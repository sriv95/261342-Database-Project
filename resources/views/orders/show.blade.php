@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
    <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:underline">← My orders</a>
    <div class="mt-2 mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Order #{{ $order->id }}</h1>
        <span class="rounded bg-emerald-100 px-3 py-1 text-sm font-medium uppercase text-emerald-800">{{ $order->status }}</span>
    </div>

    <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
        @foreach ($order->items as $item)
            <div class="flex justify-between p-4 text-sm">
                <div>
                    <a href="{{ route('products.show', $item->variant->product) }}" class="font-medium hover:text-emerald-700">{{ $item->variant->product->name }}</a>
                    <p class="text-gray-500">{{ $item->variant->label }} · ฿{{ number_format($item->unit_price) }} × {{ $item->quantity }}</p>
                </div>
                <p class="font-semibold">฿{{ number_format($item->unit_price * $item->quantity) }}</p>
            </div>
        @endforeach
        <p class="flex justify-between p-4 font-bold"><span>Total</span><span>฿{{ number_format($order->total) }}</span></p>
    </div>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-4 text-sm">
        <h2 class="mb-1 font-semibold">Shipped to</h2>
        <p>{{ $order->address->recipient }}<br>{{ $order->address->line1 }}, {{ $order->address->district }}, {{ $order->address->province }} {{ $order->address->postal_code }}</p>
        <p class="mt-2 text-gray-500">Placed {{ $order->placed_at->format('d M Y H:i') }}</p>
    </section>
@endsection
