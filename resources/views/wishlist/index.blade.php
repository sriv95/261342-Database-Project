@extends('layouts.app')

@section('title', 'Wish list')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">My wish list</h1>

    @if ($products->isEmpty())
        <p class="py-12 text-center text-gray-500">Nothing saved yet. <a href="{{ route('products.index') }}" class="text-emerald-700 hover:underline">Browse products</a></p>
    @else
        @php $wishedIds = $products->pluck('id')->all(); @endphp
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach ($products as $product)
                @include('partials.product-card', ['showWish' => true, 'wishedIds' => $wishedIds])
            @endforeach
        </div>
    @endif
@endsection
