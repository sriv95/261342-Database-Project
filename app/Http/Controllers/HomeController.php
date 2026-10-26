<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function __invoke()
    {
        $featured = Product::query()
            ->where('is_active', true)
            ->with('images')
            ->withMin('variants', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(8)
            ->get();

        return view('home', [
            'featured' => $featured,
            'categories' => Category::withCount('products')->get(),
        ]);
    }
}
