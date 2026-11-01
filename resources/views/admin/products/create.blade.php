@extends('admin.layout')

@section('title', 'New product')

@section('admin')
    <h1 class="display mb-4 text-3xl">New product</h1>
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6 rounded-lg border border-gray-200 bg-white p-4">
        @csrf
        @include('admin.products._fields')

        <div>
            <h2 class="mb-2 text-sm font-semibold">First variant</h2>
            <div class="grid gap-3 text-sm sm:grid-cols-5">
                @foreach ([['sku', 'SKU', 'text'], ['color', 'Color (optional)', 'text'], ['option_name', 'Option (e.g. 4U-G5)', 'text'], ['price', 'Price', 'number'], ['stock_qty', 'Stock', 'number']] as [$f, $label, $type])
                    <div>
                        <label class="block font-medium" for="{{ $f }}">{{ $label }}</label>
                        <input id="{{ $f }}" name="{{ $f }}" type="{{ $type }}" @if ($type === 'number') min="0" step="{{ $f === 'price' ? '0.01' : '1' }}" @endif value="{{ old($f) }}" @required(! in_array($f, ['color', 'option_name'])) class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                        @error($f) <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </div>
        <button class="rounded-full bg-emerald-600 px-5 py-2 font-medium text-white hover:bg-emerald-700">Create product</button>
    </form>
@endsection
