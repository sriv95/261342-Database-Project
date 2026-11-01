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

            <div data-carousel class="mt-12">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 class="display text-3xl">New arrivals</h2>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('products.index') }}" class="hidden text-sm underline underline-offset-4 decoration-line/50 hover:decoration-line sm:inline">See all products</a>
                        <div class="flex gap-2">
                            <button type="button" data-prev aria-label="Previous products" class="btn btn-ghost-light !p-0 h-10 w-10 disabled:opacity-35">
                                <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12.5 4-6 6 6 6"/></svg>
                            </button>
                            <button type="button" data-next aria-label="Next products" class="btn btn-ghost-light !p-0 h-10 w-10 disabled:opacity-35">
                                <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m7.5 4 6 6-6 6"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div data-track role="region" aria-roledescription="carousel" aria-label="New arrivals" tabindex="0" class="-mx-2 flex snap-x snap-mandatory overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($featured as $product)
                        <div class="w-1/2 shrink-0 snap-start px-2 md:w-1/4">
                            @include('partials.product-card', ['dark' => true])
                        </div>
                    @endforeach
                </div>

                <div data-dots class="mt-5 flex justify-center gap-2" role="group" aria-label="Choose page"></div>
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
