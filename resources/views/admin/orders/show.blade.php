@extends('admin.layout')

@section('title', 'Order #'.$order->id)

@section('admin')
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:underline">← Orders</a>
    <div class="mt-2 mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Order #{{ $order->id }}</h1>
        @if ($order->status === 'cancelled')
            <span class="rounded bg-red-100 px-3 py-1 text-sm font-medium uppercase text-red-700">Cancelled</span>
        @else
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex items-center gap-2 text-sm">
                @csrf @method('PATCH')
                <select name="status" class="rounded border border-gray-300 px-3 py-1.5">
                    @foreach ($statuses as $s)<option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>@endforeach
                </select>
                <button class="rounded bg-gray-900 px-3 py-1.5 text-white hover:bg-gray-700" onclick="return this.form.status.value !== 'cancelled' || confirm('Cancel this order and return stock?')">Update</button>
            </form>
        @endif
    </div>

    <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
        @foreach ($order->items as $item)
            <div class="flex justify-between p-3 text-sm">
                <span>{{ $item->variant->product->name }} <span class="text-gray-500">({{ $item->variant->label }}) · ฿{{ number_format($item->unit_price) }} × {{ $item->quantity }}</span></span>
                <span class="font-semibold">฿{{ number_format($item->unit_price * $item->quantity) }}</span>
            </div>
        @endforeach
        <p class="flex justify-between p-3 font-bold"><span>Total</span><span>฿{{ number_format($order->total) }}</span></p>
    </div>

    <div class="mt-4 grid gap-4 text-sm md:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-4"><h2 class="mb-1 font-semibold">Customer</h2>{{ $order->user->name }}<br>{{ $order->user->email }}<br>{{ $order->user->phone }}</section>
        <section class="rounded-lg border border-gray-200 bg-white p-4"><h2 class="mb-1 font-semibold">Ship to</h2>{{ $order->address->recipient }}<br>{{ $order->address->line1 }}, {{ $order->address->district }}, {{ $order->address->province }} {{ $order->address->postal_code }}</section>
    </div>
@endsection
