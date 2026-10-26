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
            <div class="flex items-start justify-between gap-4">
                <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
                @auth
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}">
                        @csrf
                        <button class="whitespace-nowrap rounded border border-gray-300 px-3 py-1.5 text-sm hover:border-red-400">{{ $wished ? '♥ Saved' : '♡ Wish list' }}</button>
                    </form>
                @endauth
            </div>
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
                                @auth
                                    <form method="POST" action="{{ route('cart.store') }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                                        <input type="number" name="quantity" value="1" min="1" max="{{ min(20, $variant->stock_qty) }}" class="w-14 rounded border border-gray-300 px-2 py-1">
                                        <button class="rounded bg-emerald-600 px-3 py-1 text-white hover:bg-emerald-700">Add</button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">Log in to buy</a>
                                @endauth
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

        @auth
            @if ($canReview)
                <form method="POST" action="{{ route('reviews.store', $product) }}" class="mb-6 space-y-3 rounded-lg border border-gray-200 bg-white p-4 text-sm">
                    @csrf
                    <p class="font-medium">{{ $myReview ? 'Edit your review' : 'Write a review' }}</p>
                    <select name="rating" class="rounded border border-gray-300 px-3 py-2">
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected(old('rating', $myReview?->rating ?? 5) == $i)>{{ str_repeat('★', $i) }} ({{ $i }})</option>
                        @endfor
                    </select>
                    <textarea name="comment" rows="3" placeholder="Share your experience" class="w-full rounded border border-gray-300 px-3 py-2">{{ old('comment', $myReview?->comment) }}</textarea>
                    <button class="rounded bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">Submit</button>
                </form>
            @else
                <p class="mb-4 text-sm text-gray-500">Only customers who bought this product can review it.</p>
            @endif
        @endauth

        @forelse ($product->reviews as $review)
            <article class="mb-3 rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium">{{ $review->user->name }}</span>
                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                </div>
                <p class="mt-1 text-sm text-gray-700">{{ $review->comment }}</p>
                @if (auth()->id() === $review->user_id)
                    <form method="POST" action="{{ route('reviews.destroy', $product) }}" class="mt-2">@csrf @method('DELETE')<button class="text-xs text-red-600 hover:underline">Delete my review</button></form>
                @endif
            </article>
        @empty
            <p class="text-sm text-gray-500">No reviews yet.</p>
        @endforelse
    </section>
@endsection
