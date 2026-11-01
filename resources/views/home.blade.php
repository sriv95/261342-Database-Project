@extends('layouts.app')

@section('content')
    <section>
        <h2 class="mb-4 text-xl font-semibold">Categories</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="rounded-lg border border-gray-200 bg-white p-4 text-center text-sm font-medium hover:border-emerald-600 hover:text-emerald-700">
                    {{ $category->name }}
                    <span class="block text-xs font-normal text-gray-400">{{ $category->products_count }} items</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-10">
        <h2 class="mb-4 text-xl font-semibold">New arrivals</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach ($featured as $product)
                @include('partials.product-card')
            @endforeach
        </div>
    </section>

    @foreach ($categories as $category)
        @if ($trending[$category->id]->isNotEmpty())
            <section class="mt-10">
                <div class="mb-4 flex items-baseline justify-between">
                    <h2 class="text-xl font-semibold">Trending in {{ $category->name }}</h2>
                    <a href="{{ route('products.index', ['category' => $category->id]) }}" class="text-sm text-emerald-700 hover:underline">See all</a>
                </div>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ($trending[$category->id] as $product)
                        @include('partials.product-card')
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
@endsection
