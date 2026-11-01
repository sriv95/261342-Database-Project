@extends('admin.layout')

@section('title', 'Admin orders')

@section('admin')
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <h1 class="display mr-4 text-3xl">Orders</h1>
        <a href="{{ route('admin.orders.index') }}" class="rounded-full px-3 py-1 text-sm {{ $status ? 'hover:bg-gray-200' : 'bg-gray-900 text-white' }}">All</a>
        @foreach ($statuses as $s)
            <a href="{{ route('admin.orders.index', ['status' => $s]) }}" class="rounded-full px-3 py-1 text-sm capitalize {{ $status === $s ? 'bg-gray-900 text-white' : 'hover:bg-gray-200' }}">{{ $s }}</a>
        @endforeach
    </div>

    <table class="w-full rounded-lg border border-gray-200 bg-white text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500"><tr><th class="p-3">#</th><th>Customer</th><th>Placed</th><th class="text-right">Lines</th><th class="text-right">Total</th><th class="p-3">Status</th></tr></thead>
        <tbody>
            @forelse ($orders as $order)
                <tr class="border-t border-gray-100">
                    <td class="p-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-emerald-700 hover:underline">#{{ $order->id }}</a></td>
                    <td>{{ $order->user->name }}</td>
                    <td>{{ $order->placed_at->format('d M Y H:i') }}</td>
                    <td class="text-right">{{ $order->items_count }}</td>
                    <td class="text-right">฿{{ number_format($order->total) }}</td>
                    <td class="p-3">@include("partials.status-badge", ["status" => $order->status])</td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-gray-500">No orders.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
