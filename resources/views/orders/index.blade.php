@extends('layouts.app')

@section('title', 'My orders')

@section('content')
    <h1 class="display mb-6 text-4xl">My orders</h1>

    <div data-stagger>
    @forelse ($orders as $order)
        <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 py-4 first:border-t hover:bg-white">
            <div>
                <p class="font-medium">Order #{{ $order->id }}</p>
                <p class="text-sm text-gray-500">Placed {{ $order->placed_at->format('d M Y, H:i') }}, {{ $order->items_count }} {{ Str::plural('line', $order->items_count) }}</p>
            </div>
            <div class="flex items-center gap-4">
                @include('partials.status-badge', ['status' => $order->status])
                <p class="price w-28 text-right text-lg">฿{{ number_format($order->total) }}</p>
            </div>
        </a>
    @empty
        <div class="py-12">
            <p class="display text-2xl">No orders yet.</p>
            <p class="mt-2 text-gray-600">Orders you place show up here with their status.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-5">Browse products</a>
        </div>
    @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
