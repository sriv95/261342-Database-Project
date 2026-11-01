<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private const TRENDING_PER_CATEGORY = 4;

    private const TRENDING_WINDOW_DAYS = 30;

    public function __invoke()
    {
        $featured = Product::query()
            ->where('is_active', true)
            ->with('images')
            ->withMin('variants', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(12)
            ->get();

        $categories = Category::withCount('products')->get();

        return view('home', [
            'featured' => $featured,
            'categories' => $categories,
            'trending' => $this->trendingByCategory($categories),
        ]);
    }

    /**
     * Best sellers per category over the last 30 days (cancelled orders ignored).
     * Each product gets a `sold_monthly` attribute with its unit count.
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, Product>> keyed by category id
     */
    private function trendingByCategory($categories)
    {
        $sold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('products.is_active', true)
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.placed_at', '>=', now()->subDays(self::TRENDING_WINDOW_DAYS))
            ->groupBy('products.id', 'products.category_id')
            ->selectRaw('products.id as product_id, products.category_id, SUM(order_items.quantity) as sold')
            ->orderByDesc('sold')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rows) => $rows->take(self::TRENDING_PER_CATEGORY));

        $products = Product::query()
            ->whereIn('id', $sold->flatten()->pluck('product_id'))
            ->with(['images', 'brand'])
            ->withMin('variants', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get()
            ->keyBy('id');

        return $categories->mapWithKeys(fn ($category) => [
            $category->id => ($sold[$category->id] ?? collect())
                ->map(function ($row) use ($products) {
                    $product = $products[$row->product_id];
                    $product->sold_monthly = (int) $row->sold;

                    return $product;
                })
                ->values(),
        ]);
    }
}
