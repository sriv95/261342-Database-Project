@extends('admin.layout')

@section('title', 'Admin dashboard')

@section('admin')
    <h1 class="mb-4 text-2xl font-bold">Sales report</h1>
    <p class="mb-4 text-xs text-gray-500">Counts orders with status paid, shipped or done. Cancelled and pending orders are excluded.</p>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ([['Revenue', '฿'.number_format($kpi->revenue)], ['Orders', number_format($kpi->orders)], ['Avg. order', '฿'.number_format($kpi->average)], ['Customers', number_format($customers)]] as [$label, $value])
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-bold">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    @if ($pending)
        <p class="mt-3 text-sm"><a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-amber-700 hover:underline">{{ $pending }} order(s) waiting in pending</a></p>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        @foreach ([['Revenue by day (last 30 days)', $daily, 'day'], ['Revenue by month (last 12 months)', $monthly, 'month']] as [$title, $rows, $key])
            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h2 class="mb-3 font-semibold">{{ $title }}</h2>
                @php($max = max(1, (float) $rows->max('revenue')))
                @forelse ($rows as $row)
                    <div class="mb-1 flex items-center gap-2 text-xs">
                        <span class="w-20 shrink-0 text-gray-500">{{ $row->$key }}</span>
                        <div class="h-4 flex-1 rounded bg-gray-100"><div class="h-4 rounded bg-emerald-500" style="width: {{ round($row->revenue / $max * 100) }}%"></div></div>
                        <span class="w-24 shrink-0 text-right">฿{{ number_format($row->revenue) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No sales in this period.</p>
                @endforelse
            </section>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @foreach ([['Top selling products', $topProducts], ['Sales by category', $byCategory], ['Sales by brand', $byBrand]] as [$title, $rows])
            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h2 class="mb-3 font-semibold">{{ $title }}</h2>
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase text-gray-500"><tr><th>Name</th><th class="text-right">Units</th><th class="text-right">Revenue</th></tr></thead>
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

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 font-semibold">Low stock (5 or fewer)</h2>
            <table class="w-full text-sm">
                @forelse ($lowStock as $v)
                    <tr class="border-t border-gray-100">
                        <td class="py-1"><a href="{{ route('admin.products.edit', $v->product_id) }}" class="hover:text-emerald-700">{{ $v->product->name }}</a> <span class="text-gray-500">{{ $v->option_name }}</span></td>
                        <td class="text-right font-semibold {{ $v->stock_qty === 0 ? 'text-red-600' : 'text-amber-600' }}">{{ $v->stock_qty }}</td>
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
                    <tr class="border-t border-gray-100"><td class="py-1">{{ $p->name }}</td><td class="text-right text-amber-500">★ {{ number_format($p->rating, 1) }} <span class="text-gray-400">({{ $p->reviews }})</span></td></tr>
                @empty
                    <tr><td class="py-2 text-gray-500">Not enough reviews yet.</td></tr>
                @endforelse
            </table>
        </section>
    </div>
@endsection
