<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->user()->cart()->firstOrCreate();
        $items = $cart->items()->with('variant.product.images')->get();

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $items->sum(fn ($i) => $i->quantity * $i->variant->price),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $variant = ProductVariant::findOrFail($data['variant_id']);
        $cart = $request->user()->cart()->firstOrCreate();
        $item = $cart->items()->firstOrNew(['variant_id' => $variant->id]);

        $wanted = ($item->quantity ?? 0) + $data['quantity'];

        if ($variant->stock_qty < 1) {
            return back()->withErrors(['stock' => 'That option is out of stock.']);
        }

        $item->quantity = min($wanted, $variant->stock_qty);
        $item->save();

        $message = $wanted > $variant->stock_qty
            ? "Only {$variant->stock_qty} in stock; cart updated to the maximum."
            : 'Added to cart.';

        return back()->with('status', $message);
    }

    public function update(Request $request, CartItem $item)
    {
        $this->authorizeItem($request, $item);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        $item->update(['quantity' => min($data['quantity'], $item->variant->stock_qty)]);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $item)
    {
        $this->authorizeItem($request, $item);
        $item->delete();

        return back()->with('status', 'Item removed.');
    }

    private function authorizeItem(Request $request, CartItem $item): void
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403);
    }
}
