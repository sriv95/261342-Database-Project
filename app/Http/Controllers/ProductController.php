<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'integer'],
            'brand' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,rating'],
        ]);

        $products = Product::query()
            ->where('is_active', true)
            ->with('images')
            ->withMin('variants', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['brand'] ?? null, fn ($q, $id) => $q->where('brand_id', $id))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when(
                $filters['sort'] ?? 'newest',
                fn ($q, $sort) => match ($sort) {
                    'price_asc' => $q->orderBy('variants_min_price'),
                    'price_desc' => $q->orderByDesc('variants_min_price'),
                    'rating' => $q->orderByDesc('reviews_avg_rating'),
                    default => $q->latest('id'),
                }
            )
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'filters' => $filters,
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'brand', 'images', 'variants', 'reviews.user'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        $user = auth()->user();

        return view('products.show', [
            'product' => $product,
            'wished' => $user?->wishlistProducts()->whereKey($product->id)->exists() ?? false,
            'canReview' => $user ? OrderItem::query()
                ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', '!=', 'cancelled'))
                ->whereHas('variant', fn ($q) => $q->where('product_id', $product->id))
                ->exists() : false,
            'myReview' => $user ? $product->reviews->firstWhere('user_id', $user->id) : null,
        ]);
    }
}
