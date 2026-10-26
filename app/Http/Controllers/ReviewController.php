<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $user = $request->user();

        $hasBought = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', '!=', 'cancelled'))
            ->whereHas('variant', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        if (! $hasBought) {
            return back()->withErrors(['review' => 'Only customers who bought this product can review it.']);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            $data,
        );

        return back()->with('status', 'Thanks for your review!');
    }

    public function destroy(Request $request, Product $product)
    {
        Review::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();

        return back()->with('status', 'Review deleted.');
    }
}
