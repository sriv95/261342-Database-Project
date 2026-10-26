@extends('layouts.app')

@section('content')
    <section class="rounded-xl bg-emerald-700 px-8 py-14 text-white">
        <h1 class="text-4xl font-bold">Gear up for your next smash.</h1>
        <p class="mt-3 max-w-xl text-emerald-100">Rackets, shoes, shuttlecocks and more from Yonex, Li-Ning, Victor, Mizuno and Kawasaki.</p>
        <a href="{{ route('products.index') }}" class="mt-6 inline-block rounded bg-white px-5 py-2 font-semibold text-emerald-800 hover:bg-emerald-50">Shop now</a>
    </section>

    <section class="mt-10">
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
@endsection
