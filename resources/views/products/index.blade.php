@extends('layouts.app')

@section('title', 'Products')

@section('content')
    @php
        $hasFilters = collect($filters ?? [])->only(['q', 'category', 'brand'])->filter()->isNotEmpty();
        $selectClass = 'field !w-auto rounded-full bg-white pr-9';
    @endphp

    <div class="mb-6 flex flex-wrap items-baseline justify-between gap-2">
        <h1 class="display text-4xl">Products</h1>
        <p class="text-gray-500">{{ number_format($products->total()) }} {{ Str::plural('result', $products->total()) }}</p>
    </div>

    <form method="GET" class="mb-8 flex flex-wrap items-center gap-3 border-y border-gray-200 py-4">
        <label class="sr-only" for="q">Search products</label>
        <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search products" class="field !w-auto min-w-52 flex-1 rounded-full sm:max-w-xs">
        <label class="sr-only" for="category">Category</label>
        <select id="category" name="category" onchange="this.form.submit()" class="{{ $selectClass }}">
            <option value="">All categories</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(($filters['category'] ?? null) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="brand">Brand</label>
        <select id="brand" name="brand" onchange="this.form.submit()" class="{{ $selectClass }}">
            <option value="">All brands</option>
            @foreach ($brands as $b)
                <option value="{{ $b->id }}" @selected(($filters['brand'] ?? null) == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="sort">Sort by</label>
        <select id="sort" name="sort" onchange="this.form.submit()" class="{{ $selectClass }}">
            @foreach (['newest' => 'Newest', 'trending' => 'Trending', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'rating' => 'Top rated'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Search</button>
        @if ($hasFilters)
            <a href="{{ route('products.index') }}" class="link text-sm">Clear filters</a>
        @endif
    </form>

    @if ($products->isEmpty())
        <div class="py-16">
            <p class="display text-2xl">Nothing matches those filters.</p>
            <p class="mt-2 text-gray-600">Try a different brand or category, or clear the search.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-5">Show all products</a>
        </div>
    @else
        <div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">
            @foreach ($products as $product)
                @include('partials.product-card', ['showWish' => true])
            @endforeach
        </div>
        <div class="mt-10">{{ $products->links() }}</div>
    @endif
@endsection
