@extends('admin.layout')

@section('title', 'Edit '.$product->name)

@section('admin')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product? Products with orders are hidden instead.')">
            @csrf @method('DELETE')
            <button class="text-sm text-red-600 hover:underline">Delete product</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-gray-200 bg-white p-4">
        @csrf @method('PUT')
        @include('admin.products._fields')
        @if ($product->images->isNotEmpty())
            <img src="{{ asset($product->images->firstWhere('is_primary', true)?->path ?? $product->images->first()->path) }}" class="h-24 w-24 rounded border border-gray-200 object-cover" alt="">
        @endif
        <button class="rounded bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">Save product</button>
    </form>

    <h2 class="mt-8 mb-2 text-lg font-semibold">Variants</h2>
    <div class="space-y-2">
        @foreach ($product->variants as $variant)
            <div class="flex flex-wrap items-end gap-2 rounded-lg border border-gray-200 bg-white p-3 text-sm">
                <form method="POST" action="{{ route('admin.variants.update', $variant) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PATCH')
                    <label>SKU<input name="sku" value="{{ $variant->sku }}" required class="mt-1 block w-32 rounded border border-gray-300 px-2 py-1"></label>
                    <label>Color<input name="color" value="{{ $variant->color }}" class="mt-1 block w-28 rounded border border-gray-300 px-2 py-1"></label>
                    <label>Option<input name="option_name" value="{{ $variant->option_name }}" class="mt-1 block w-32 rounded border border-gray-300 px-2 py-1"></label>
                    <label>Price<input name="price" type="number" step="0.01" min="0" value="{{ $variant->price }}" required class="mt-1 block w-28 rounded border border-gray-300 px-2 py-1"></label>
                    <label>Stock<input name="stock_qty" type="number" min="0" value="{{ $variant->stock_qty }}" required class="mt-1 block w-20 rounded border border-gray-300 px-2 py-1"></label>
                    <button class="rounded bg-gray-900 px-3 py-1.5 text-white hover:bg-gray-700">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.variants.destroy', $variant) }}">
                    @csrf @method('DELETE')
                    <button class="px-2 py-1.5 text-red-600 hover:underline">Delete</button>
                </form>
                @if ($variant->order_items_count)<span class="text-xs text-gray-400">{{ $variant->order_items_count }} order line(s)</span>@endif
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.variants.store', $product) }}" class="mt-3 flex flex-wrap items-end gap-2 rounded-lg border border-dashed border-gray-300 p-3 text-sm">
        @csrf
        <label>SKU<input name="sku" required class="mt-1 block w-32 rounded border border-gray-300 px-2 py-1"></label>
        <label>Color<input name="color" class="mt-1 block w-28 rounded border border-gray-300 px-2 py-1"></label>
        <label>Option<input name="option_name" class="mt-1 block w-32 rounded border border-gray-300 px-2 py-1"></label>
        <label>Price<input name="price" type="number" step="0.01" min="0" required class="mt-1 block w-28 rounded border border-gray-300 px-2 py-1"></label>
        <label>Stock<input name="stock_qty" type="number" min="0" value="0" required class="mt-1 block w-20 rounded border border-gray-300 px-2 py-1"></label>
        <button class="rounded bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">Add variant</button>
    </form>
    @foreach (['sku', 'color', 'option_name', 'price', 'stock_qty'] as $f)
        @error($f) <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    @endforeach
@endsection
