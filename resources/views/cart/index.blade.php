@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <h1 class="display mb-6 text-4xl">Your cart</h1>

    @if ($items->isEmpty())
        <div class="py-12">
            <p class="display text-2xl">Your cart is empty.</p>
            <p class="mt-2 text-gray-600">Add a racket, shoes or a tube of shuttles to get started.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-5">Browse products</a>
        </div>
    @else
        <div class="grid items-start gap-8 lg:grid-cols-3">
            <div data-stagger class="border-t border-gray-200 lg:col-span-2">
                @foreach ($items as $item)
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-3 border-b border-gray-200 py-5">
                        <a href="{{ route('products.show', $item->variant->product) }}" class="tile h-20 w-20 shrink-0">
                            <img src="{{ asset($item->variant->product->images->first()?->path ?? 'images/placeholder.svg') }}" class="h-full w-full object-contain p-1.5" alt="">
                        </a>
                        <div class="min-w-48 flex-1">
                            <a href="{{ route('products.show', $item->variant->product) }}" class="font-medium hover:underline">{{ $item->variant->product->name }}</a>
                            <p class="text-sm text-gray-500">{{ $item->variant->label }}, ฿{{ number_format($item->variant->price) }} each</p>
                            @if ($item->quantity > $item->variant->stock_qty)
                                <p class="text-sm font-medium text-red-700">Only {{ $item->variant->stock_qty }} left in stock. Lower the quantity to continue.</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <label class="sr-only" for="qty-{{ $item->id }}">Quantity</label>
                            <input id="qty-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="20" class="field !w-16 !py-1.5 text-center">
                            <button class="link text-sm">Update</button>
                        </form>
                        <p class="price w-24 text-right text-lg">฿{{ number_format($item->quantity * $item->variant->price) }}</p>
                        <form method="POST" action="{{ route('cart.destroy', $item) }}">
                            @csrf @method('DELETE')
                            <button class="text-sm text-red-700 hover:underline">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <aside class="panel p-5 lg:sticky lg:top-6">
                <h2 class="display text-xl">Order summary</h2>
                <p class="mt-4 flex items-baseline justify-between"><span>Subtotal</span><span class="price text-2xl">฿{{ number_format($subtotal) }}</span></p>
                <p class="mt-1 text-sm text-gray-500">Shipping address and payment come next.</p>
                <a href="{{ route('checkout.create') }}" class="btn btn-primary mt-5 w-full !py-2.5">Check out</a>
                <a href="{{ route('products.index') }}" class="link mt-4 block text-center text-sm">Keep shopping</a>
            </aside>
        </div>
    @endif
@endsection
