@extends('layouts.app')

@section('hero')
    <section class="on-floor relative overflow-hidden bg-floor text-line">
        @include('partials.court-lines', ['class' => 'pointer-events-none absolute inset-0 h-full w-full text-line opacity-40'])

        <div class="relative mx-auto max-w-6xl px-4 pb-14 pt-8 sm:pt-10">
            <nav aria-label="Categories" data-stagger class="court">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->id]) }}">
                        <span class="display block text-xl font-semibold">{{ $category->name }}</span>
                        <span class="mt-0.5 block text-sm text-line/75">{{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mb-5 mt-12 flex items-baseline justify-between gap-4">
                <h2 class="display text-3xl">New arrivals</h2>
                <a href="{{ route('products.index') }}" class="text-sm underline underline-offset-4 decoration-line/50 hover:decoration-line">See all products</a>
            </div>
            <div data-stagger class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">
                @foreach ($featured as $product)
                    @include('partials.product-card', ['dark' => true])
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('content')
    @foreach ($categories as $category)
        @if ($trending[$category->id]->isNotEmpty())
            <section class="mt-14 first:mt-0">
                <div class="mb-5 flex items-baseline justify-between gap-4 border-t border-gray-200 pt-6">
                    <h2 class="display text-3xl">Trending in {{ $category->name }}</h2>
                    <a href="{{ route('products.index', ['category' => $category->id, 'sort' => 'trending']) }}" class="link text-sm">See all {{ Str::lower($category->name) }}</a>
                </div>
                <div data-stagger class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">
                    @foreach ($trending[$category->id] as $product)
                        @include('partials.product-card')
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
@endsection
