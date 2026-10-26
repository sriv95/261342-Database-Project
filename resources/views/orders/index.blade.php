@extends('layouts.app')

@section('title', 'My orders')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">My orders</h1>

    @forelse ($orders as $order)
        <a href="{{ route('orders.show', $order) }}" class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 hover:border-emerald-600">
            <div>
                <p class="font-medium">Order #{{ $order->id }}</p>
                <p class="text-sm text-gray-500">{{ $order->placed_at->format('d M Y H:i') }} · {{ $order->items_count }} line(s)</p>
            </div>
            <div class="text-right">
                <p class="font-semibold">฿{{ number_format($order->total) }}</p>
                <p class="text-xs uppercase text-emerald-700">{{ $order->status }}</p>
            </div>
        </a>
    @empty
        <p class="py-12 text-center text-gray-500">No orders yet.</p>
    @endforelse

    {{ $orders->links() }}
@endsection
