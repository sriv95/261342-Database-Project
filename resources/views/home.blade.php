@extends('layouts.app')

@section('hero')
    <section class="on-floor relative overflow-hidden bg-floor text-line">
        @include('partials.court-lines', ['class' => 'pointer-events-none absolute inset-0 h-full w-full text-line', 'arc' => true])

        <div class="relative mx-auto max-w-6xl px-4 pb-10 pt-14 sm:pb-14 sm:pt-20">
            <h1 class="display max-w-[14ch] text-[clamp(2.75rem,8vw,5.5rem)] font-bold">Gear up for your next smash.</h1>
            <p class="mt-5 max-w-[46ch] text-lg text-line/85">Rackets, shoes, shuttlecocks and more from Yonex, Li-Ning, Victor, Mizuno and Kawasaki.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="btn btn-light">Shop all gear</a>
                <a href="{{ route('products.index', ['sort' => 'trending']) }}" class="btn btn-ghost-light">See what is trending</a>
            </div>

            <nav aria-label="Categories" class="court mt-14 sm:mt-20">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->id]) }}">
                        <span class="display block text-xl font-semibold">{{ $category->name }}</span>
                        <span class="mt-0.5 block text-sm text-line/75">{{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </section>
@endsection

@section('content')
    <section>
        <div class="mb-5 flex items-baseline justify-between gap-4">
            <h2 class="display text-3xl">New arrivals</h2>
            <a href="{{ route('products.index') }}" class="link text-sm">See all products</a>
        </div>
        <div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">
            @foreach ($featured as $product)
                @include('partials.product-card')
            @endforeach
        </div>
    </section>

    @foreach ($categories as $category)
        @if ($trending[$category->id]->isNotEmpty())
            <section class="mt-16">
                <div class="mb-5 flex items-baseline justify-between gap-4 border-t border-gray-200 pt-6">
                    <h2 class="display text-3xl">Trending in {{ $category->name }}</h2>
                    <a href="{{ route('products.index', ['category' => $category->id, 'sort' => 'trending']) }}" class="link text-sm">See all {{ Str::lower($category->name) }}</a>
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">
                    @foreach ($trending[$category->id] as $product)
                        @include('partials.product-card')
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
@endsection
