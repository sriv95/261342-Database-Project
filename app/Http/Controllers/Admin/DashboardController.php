<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Orders in these statuses count as sales. */
    public const SALE_STATUSES = ['paid', 'shipped', 'done'];

    public function __invoke()
    {
        $sales = fn () => DB::table('orders')->whereIn('orders.status', self::SALE_STATUSES);

        $kpi = $sales()->selectRaw('COUNT(*) AS orders, COALESCE(SUM(total), 0) AS revenue, COALESCE(AVG(total), 0) AS average')->first();

        $daily = $sales()
            ->where('placed_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(placed_at) AS day, COUNT(*) AS orders, SUM(total) AS revenue')
            ->groupBy('day')->orderBy('day')->get();

        $monthly = $sales()
            ->where('placed_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(placed_at, '%Y-%m') AS month, COUNT(*) AS orders, SUM(total) AS revenue")
            ->groupBy('month')->orderBy('month')->get();

        $dailyChart = collect(range(29, 0))->map(fn ($n) => now()->subDays($n)->startOfDay())
            ->map(fn ($d) => ['label' => $d->format('j M'), 'value' => (float) ($daily->firstWhere('day', $d->toDateString())->revenue ?? 0)]);
        $monthlyChart = collect(range(11, 0))->map(fn ($n) => now()->startOfMonth()->subMonths($n))
            ->map(fn ($d) => ['label' => $d->format('M Y'), 'value' => (float) ($monthly->firstWhere('month', $d->format('Y-m'))->revenue ?? 0)]);

        $lines = fn () => DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereIn('orders.status', self::SALE_STATUSES);

        $units = 'SUM(order_items.quantity) AS units, SUM(order_items.quantity * order_items.unit_price) AS revenue';

        return view('admin.dashboard', [
            'kpi' => $kpi,
            'customers' => User::where('role', 'customer')->count(),
            'pending' => DB::table('orders')->where('status', 'pending')->count(),
            'daily' => $daily,
            'monthly' => $monthly,
            'dailyChart' => $dailyChart,
            'monthlyChart' => $monthlyChart,
            'topProducts' => $lines()->selectRaw("products.id, products.name, $units")
                ->groupBy('products.id', 'products.name')->orderByDesc('units')->limit(5)->get(),
            'byCategory' => $lines()->join('categories', 'categories.id', '=', 'products.category_id')
                ->selectRaw("categories.name, $units")->groupBy('categories.id', 'categories.name')->orderByDesc('revenue')->get(),
            'byBrand' => $lines()->join('brands', 'brands.id', '=', 'products.brand_id')
                ->selectRaw("brands.name, $units")->groupBy('brands.id', 'brands.name')->orderByDesc('revenue')->get(),
            'lowStock' => ProductVariant::with('product')->where('stock_qty', '<=', 5)->orderBy('stock_qty')->limit(10)->get(),
            'topRated' => DB::table('reviews')->join('products', 'products.id', '=', 'reviews.product_id')
                ->selectRaw('products.id, products.name, AVG(reviews.rating) AS rating, COUNT(*) AS reviews')
                ->groupBy('products.id', 'products.name')->having('reviews', '>=', 2)->orderByDesc('rating')->limit(5)->get(),
        ]);
    }
}
