@extends('admin.layout')

@section('title', 'Admin dashboard')

@section('admin')
    <h1 class="display mb-4 text-3xl">Sales report</h1>
    <p class="mb-4 text-xs text-gray-500">Counts orders with status paid, shipped or done. Cancelled and pending orders are excluded.</p>

    <div data-stagger class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ([['Revenue', '฿'.number_format($kpi->revenue)], ['Orders', number_format($kpi->orders)], ['Avg. order', '฿'.number_format($kpi->average)], ['Customers', number_format($customers)]] as [$label, $value])
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="display text-3xl" data-count="{{ preg_replace('/[^0-9.]/', '', $value) }}" data-prefix="{{ str_starts_with($value, '฿') ? '฿' : '' }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    @if ($pending)
        <p class="mt-3 text-sm"><a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-amber-700 hover:underline">{{ $pending }} order(s) waiting in pending</a></p>
    @endif

    @php
        $chart = fn ($type, $rows, $label = 'label', $value = 'value') => json_encode([
            'type' => $type,
            'labels' => collect($rows)->pluck($label)->values(),
            'values' => collect($rows)->pluck($value)->map(fn ($v) => (float) $v)->values(),
        ]);
    @endphp

    <div data-stagger class="mt-8 grid gap-6 lg:grid-cols-2">
        @foreach ([
            ['Revenue by day (last 30 days)', 'line', $dailyChart, 'day', $daily, 'Revenue by day chart'],
            ['Revenue by month (last 12 months)', 'bar', $monthlyChart, 'month', $monthly, 'Revenue by month chart'],
        ] as [$title, $type, $series, $key, $rows, $aria])
            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h2 class="mb-3 font-semibold">{{ $title }}</h2>
                <div class="relative h-60"><canvas role="img" aria-label="{{ $aria }}" data-chart='{{ $chart($type, $series) }}'></canvas></div>
                <div class="mt-4 max-h-56 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-white text-left text-xs text-gray-500"><tr><th class="py-1 font-medium">{{ ucfirst($key) }}</th><th class="text-right font-medium">Orders</th><th class="text-right font-medium">Revenue</th></tr></thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-t border-gray-100"><td class="py-1">{{ $row->$key }}</td><td class="text-right">{{ $row->orders }}</td><td class="text-right">฿{{ number_format($row->revenue) }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="py-2 text-gray-500">No sales in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>

    <div data-stagger class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach ([['Sales by category', $byCategory], ['Sales by brand', $byBrand]] as [$title, $rows])
            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h2 class="mb-3 font-semibold">{{ $title }}</h2>
                @if ($rows->isNotEmpty())
                    <div class="relative" style="height: {{ 56 + $rows->count() * 34 }}px"><canvas role="img" aria-label="{{ $title }} chart" data-chart='{{ $chart('hbar', $rows, 'name', 'revenue') }}'></canvas></div>
                @endif
                <table class="mt-4 w-full text-sm">
                    <thead class="text-left text-xs text-gray-500"><tr><th class="py-1 font-medium">Name</th><th class="text-right font-medium">Units</th><th class="text-right font-medium">Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t border-gray-100"><td class="py-1">{{ $row->name }}</td><td class="text-right">{{ $row->units }}</td><td class="text-right">฿{{ number_format($row->revenue) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="py-2 text-gray-500">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endforeach
    </div>

    <div data-stagger class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 font-semibold">Top selling products</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500"><tr><th class="py-1 font-medium">Name</th><th class="text-right font-medium">Units</th><th class="text-right font-medium">Revenue</th></tr></thead>
                <tbody>
                    @forelse ($topProducts as $row)
                        <tr class="border-t border-gray-100"><td class="py-1">{{ $row->name }}</td><td class="text-right">{{ $row->units }}</td><td class="text-right">฿{{ number_format($row->revenue) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-2 text-gray-500">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 font-semibold">Low stock (5 or fewer)</h2>
            <table class="w-full text-sm">
                @forelse ($lowStock as $v)
                    <tr class="border-t border-gray-100">
                        <td class="py-1"><a href="{{ route('admin.products.edit', $v->product_id) }}" class="hover:text-emerald-700">{{ $v->product->name }}</a> <span class="text-gray-500">{{ $v->label }}</span></td>
                        <td class="text-right font-semibold {{ $v->stock_qty === 0 ? 'text-red-700' : 'text-amber-700' }}">{{ $v->stock_qty }}</td>
                    </tr>
                @empty
                    <tr><td class="py-2 text-gray-500">Everything is well stocked.</td></tr>
                @endforelse
            </table>
        </section>
        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 font-semibold">Top rated (2+ reviews)</h2>
            <table class="w-full text-sm">
                @forelse ($topRated as $p)
                    <tr class="border-t border-gray-100"><td class="py-1">{{ $p->name }}</td><td class="text-right text-amber-700">★ {{ number_format($p->rating, 1) }} <span class="text-gray-500">({{ $p->reviews }})</span></td></tr>
                @empty
                    <tr><td class="py-2 text-gray-500">Not enough reviews yet.</td></tr>
                @endforelse
            </table>
        </section>
    </div>
@endsection
