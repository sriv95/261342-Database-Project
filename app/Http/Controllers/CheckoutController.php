<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        $items = $user->cart()->firstOrCreate()->items()->with('variant.product')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        return view('checkout.create', [
            'items' => $items,
            'subtotal' => $items->sum(fn ($i) => $i->quantity * $i->variant->price),
            'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'payment_method' => ['required', 'in:card,promptpay,cod'],
        ]);

        $address = $user->addresses()->find($data['address_id']);
        if (! $address) {
            return back()->withErrors(['address_id' => 'Choose one of your saved addresses.'])->withInput();
        }

        $cart = $user->cart()->firstOrCreate();

        try {
            $order = DB::transaction(function () use ($cart, $user, $address) {
                $items = $cart->items()->get();
                abort_if($items->isEmpty(), 422, 'Your cart is empty.');

                // Lock variant rows in id order so concurrent checkouts cannot oversell or deadlock.
                $variants = ProductVariant::whereIn('id', $items->pluck('variant_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $total = 0;
                foreach ($items as $item) {
                    $variant = $variants[$item->variant_id];
                    if ($variant->stock_qty < $item->quantity) {
                        throw new \RuntimeException("Not enough stock for {$variant->product->name} ({$variant->option_name}).");
                    }
                    $total += $item->quantity * $variant->price;
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'total' => $total,
                    'status' => 'paid', // payment is simulated
                    'placed_at' => now(),
                ]);

                foreach ($items as $item) {
                    $variant = $variants[$item->variant_id];
                    $order->items()->create([
                        'variant_id' => $variant->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $variant->price,
                    ]);
                    $variant->decrement('stock_qty', $item->quantity);
                }

                $cart->items()->delete();

                return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()->route('orders.show', $order)->with('status', 'Order placed. Thank you!');
    }
}
