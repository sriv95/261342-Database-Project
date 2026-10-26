@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav class="mb-4 text-sm text-gray-500">
        <a href="{{ route('products.index') }}" class="hover:underline">Products</a> /
        <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="hover:underline">{{ $product->category->name }}</a>
    </nav>

    <div class="grid gap-8 md:grid-cols-2">
        <img src="{{ asset($product->images->first()?->path ?? 'images/placeholder.svg') }}" alt="{{ $product->name }}" class="w-full rounded-lg border border-gray-200 bg-white">

        <div>
            <p class="text-sm text-gray-500">{{ $product->brand->name }}</p>
            <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
            @if ($product->reviews_count)
                <p class="mt-1 text-amber-500">★ {{ number_format($product->reviews_avg_rating, 1) }} <span class="text-sm text-gray-400">({{ $product->reviews_count }} reviews)</span></p>
            @endif
            <p class="mt-4 text-gray-700">{{ $product->description }}</p>

            <h2 class="mt-6 mb-2 text-sm font-semibold uppercase text-gray-500">Options</h2>
            <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
                @foreach ($product->variants as $variant)
                    <li class="flex items-center justify-between px-4 py-3 text-sm">
                        <span class="font-medium">{{ $variant->option_name }}</span>
                        <span class="flex items-center gap-4">
                            <span class="font-semibold">฿{{ number_format($variant->price) }}</span>
                            @if ($variant->stock_qty > 0)
                                <span class="text-emerald-600">{{ $variant->stock_qty }} in stock</span>
                            @else
                                <span class="text-red-500">Out of stock</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <section class="mt-12">
        <h2 class="mb-4 text-xl font-semibold">Reviews</h2>
        @forelse ($product->reviews as $review)
            <article class="mb-3 rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium">{{ $review->user->name }}</span>
                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                </div>
                <p class="mt-1 text-sm text-gray-700">{{ $review->comment }}</p>
            </article>
        @empty
            <p class="text-sm text-gray-500">No reviews yet.</p>
        @endforelse
    </section>
@endsection
