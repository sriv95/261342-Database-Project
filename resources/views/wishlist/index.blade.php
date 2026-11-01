@extends('layouts.app')

@section('title', 'Wish list')

@section('content')
    <h1 class="display mb-6 text-4xl">My wish list</h1>

    @if ($products->isEmpty())
        <div class="py-12">
            <p class="display text-2xl">Nothing saved yet.</p>
            <p class="mt-2 text-gray-600">Tap the heart on any product to keep it here.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-5">Browse products</a>
        </div>
    @else
        @php $wishedIds = $products->pluck('id')->all(); @endphp
        <div data-stagger class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">
            @foreach ($products as $product)
                @include('partials.product-card', ['showWish' => true, 'wishedIds' => $wishedIds])
            @endforeach
        </div>
    @endif
@endsection
