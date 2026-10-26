<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public const STATUSES = ['pending', 'paid', 'shipped', 'done', 'cancelled'];

    public function index(Request $request)
    {
        $status = $request->query('status');

        $orders = Order::with('user')
            ->withCount('items')
            ->when(in_array($status, self::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest('placed_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders, 'statuses' => self::STATUSES, 'status' => $status]);
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', [
            'order' => $order->load(['user', 'address', 'items.variant.product']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => ['required', 'in:'.implode(',', self::STATUSES)]]);

        DB::transaction(function () use ($order, $data) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if ($order->status === 'cancelled') {
                abort(422, 'A cancelled order cannot be changed.');
            }

            if ($data['status'] === 'cancelled') {
                // Return the stock reserved at checkout.
                foreach ($order->items as $item) {
                    ProductVariant::whereKey($item->variant_id)->increment('stock_qty', $item->quantity);
                }
            }

            $order->update(['status' => $data['status']]);
        });

        return back()->with('status', 'Order status updated.');
    }
}
