@extends('layouts.app')

@section('title', 'Wish list')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">My wish list</h1>

    @forelse ($products as $product)
        @once <div class="grid grid-cols-2 gap-4 md:grid-cols-4"> @endonce
        <div>
            @include('partials.product-card')
            <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="mt-1 text-center">
                @csrf
                <button class="text-sm text-red-600 hover:underline">Remove</button>
            </form>
        </div>
        @if ($loop->last) </div> @endif
    @empty
        <p class="py-12 text-center text-gray-500">Nothing saved yet. <a href="{{ route('products.index') }}" class="text-emerald-700 hover:underline">Browse products</a></p>
    @endforelse
@endsection
