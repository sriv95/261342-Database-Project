@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
    <nav aria-label="Breadcrumb" class="mb-5 text-sm text-gray-500">
        <a href="{{ route('orders.index') }}" class="hover:underline">My orders</a>
        <span aria-hidden="true" class="mx-1.5 text-gray-300">/</span> Order #{{ $order->id }}
    </nav>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="display text-4xl">Order #{{ $order->id }}</h1>
        @include('partials.status-badge', ['status' => $order->status])
    </div>

    <div class="border-t border-gray-200">
        @foreach ($order->items as $item)
            <div class="flex justify-between gap-4 border-b border-gray-200 py-4">
                <div>
                    <a href="{{ route('products.show', $item->variant->product) }}" class="font-medium hover:underline">{{ $item->variant->product->name }}</a>
                    <p class="text-sm text-gray-500">{{ $item->variant->label }}, ฿{{ number_format($item->unit_price) }} x {{ $item->quantity }}</p>
                </div>
                <p class="price text-lg">฿{{ number_format($item->unit_price * $item->quantity) }}</p>
            </div>
        @endforeach
        <p class="flex items-baseline justify-between py-4"><span class="font-medium">Total</span><span class="price text-3xl">฿{{ number_format($order->total) }}</span></p>
    </div>

    <section class="panel mt-6 p-5">
        <h2 class="display text-xl">Shipped to</h2>
        <p class="mt-2">{{ $order->address->recipient }}<br>{{ $order->address->line1 }}, {{ $order->address->district }}, {{ $order->address->province }} {{ $order->address->postal_code }}</p>
        <p class="mt-3 text-sm text-gray-500">Placed {{ $order->placed_at->format('d M Y, H:i') }}</p>
    </section>
@endsection
