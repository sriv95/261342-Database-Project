<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()->wishlistProducts()
            ->where('is_active', true)
            ->with(['images', 'brand', 'variants'])
            ->withMin('variants', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('wishlist.index', ['products' => $products]);
    }

    public function toggle(Request $request, Product $product)
    {
        $result = $request->user()->wishlistProducts()->toggle($product->id);

        $message = $result['attached'] ? 'Added to wish list.' : 'Removed from wish list.';

        if ($request->expectsJson()) {
            return response()->json(['wished' => (bool) $result['attached'], 'message' => $message]);
        }

        return back()->with('status', $message);
    }
}
