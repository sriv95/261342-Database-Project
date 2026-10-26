@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Products</h1>

    <form method="GET" class="mb-6 grid gap-3 rounded-lg border border-gray-200 bg-white p-4 sm:grid-cols-5">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search products" class="rounded border-gray-300 px-3 py-2 text-sm sm:col-span-2 border">
        <select name="category" class="rounded border border-gray-300 px-3 py-2 text-sm">
            <option value="">All categories</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(($filters['category'] ?? null) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="brand" class="rounded border border-gray-300 px-3 py-2 text-sm">
            <option value="">All brands</option>
            @foreach ($brands as $b)
                <option value="{{ $b->id }}" @selected(($filters['brand'] ?? null) == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <select name="sort" class="rounded border border-gray-300 px-3 py-2 text-sm">
            @foreach (['newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'rating' => 'Top rated'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 sm:col-span-5 sm:w-fit">Apply</button>
    </form>

    @if ($products->isEmpty())
        <p class="py-12 text-center text-gray-500">No products match your filters.</p>
    @else
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach ($products as $product)
                @include('partials.product-card')
            @endforeach
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
@endsection
