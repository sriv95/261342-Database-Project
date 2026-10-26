@extends('admin.layout')

@section('title', 'Admin products')

@section('admin')
    <div class="mb-4 flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">Products</h1>
        <form method="GET" class="ml-auto"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search" class="rounded border border-gray-300 px-3 py-1.5 text-sm"></form>
        <a href="{{ route('admin.products.create') }}" class="rounded bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">New product</a>
    </div>

    <table class="w-full rounded-lg border border-gray-200 bg-white text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="p-3">Name</th><th>Category</th><th>Brand</th><th class="text-right">Variants</th><th class="text-right">Stock</th><th class="p-3">Status</th><th></th></tr></thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="border-t border-gray-100">
                    <td class="p-3 font-medium">{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->brand->name }}</td>
                    <td class="text-right">{{ $product->variants_count }}</td>
                    <td class="text-right">{{ $product->variants_sum_stock_qty ?? 0 }}</td>
                    <td class="p-3">{!! $product->is_active ? '<span class="text-emerald-600">Active</span>' : '<span class="text-gray-400">Hidden</span>' !!}</td>
                    <td class="pr-3 text-right"><a href="{{ route('admin.products.edit', $product) }}" class="text-emerald-700 hover:underline">Edit</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
