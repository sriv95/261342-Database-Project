@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Your cart</h1>

    @if ($items->isEmpty())
        <p class="py-12 text-center text-gray-500">Your cart is empty. <a href="{{ route('products.index') }}" class="text-emerald-700 hover:underline">Browse products</a></p>
    @else
        <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
            @foreach ($items as $item)
                <div class="flex items-center gap-4 p-4">
                    <img src="{{ asset($item->variant->product->images->first()?->path ?? 'images/placeholder.svg') }}" class="h-16 w-16 rounded object-cover" alt="">
                    <div class="flex-1">
                        <a href="{{ route('products.show', $item->variant->product) }}" class="font-medium hover:text-emerald-700">{{ $item->variant->product->name }}</a>
                        <p class="text-sm text-gray-500">{{ $item->variant->label }} · ฿{{ number_format($item->variant->price) }}</p>
                        @if ($item->quantity > $item->variant->stock_qty)
                            <p class="text-sm text-red-600">Only {{ $item->variant->stock_qty }} left in stock</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="20" class="w-16 rounded border border-gray-300 px-2 py-1 text-sm">
                        <button class="text-sm text-emerald-700 hover:underline">Update</button>
                    </form>
                    <p class="w-24 text-right font-semibold">฿{{ number_format($item->quantity * $item->variant->price) }}</p>
                    <form method="POST" action="{{ route('cart.destroy', $item) }}">
                        @csrf @method('DELETE')
                        <button class="text-sm text-red-600 hover:underline">Remove</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-lg">Subtotal: <span class="font-bold">฿{{ number_format($subtotal) }}</span></p>
            <a href="{{ route('checkout.create') }}" class="rounded bg-emerald-600 px-5 py-2 font-medium text-white hover:bg-emerald-700">Checkout</a>
        </div>
    @endif
@endsection
