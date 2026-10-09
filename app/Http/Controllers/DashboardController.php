<?php
namespace App\Http\Controllers;

use App\Models\{Product, Sale, StockMovement, Customer, StockBatch};
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->integer('days', 7);
        abort_unless(in_array($days, [7, 30, 90], true), 404);

        $today     = now()->startOfDay();
        $todayEnd  = now()->endOfDay();
        $monthStart= now()->startOfMonth();
        $monthEnd  = now()->endOfDay();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $stats = [
            'products_count'   => Product::count(),
            'stock_total'      => (int) Product::sum('stock_quantity'),
            'out_of_stock'     => Product::outOfStock()->count(),
            'low_stock'        => Product::lowStock()->count(),
            'stock_value'      => (float) Product::sum(DB::raw('stock_quantity * purchase_price')),
            'sales_today'      => (float) Sale::whereBetween('sold_at', [$today, $todayEnd])->sum('total'),
            'sales_month'      => (float) Sale::whereBetween('sold_at', [$monthStart, $monthEnd])->sum('total'),
            'profit_month'     => (float) Sale::whereBetween('sold_at', [$monthStart, $monthEnd])->sum('profit'),
            'customers_count'  => Customer::count(),
            'sales_previous_month' => (float) Sale::whereBetween('sold_at', [$previousMonthStart, $previousMonthEnd])->sum('total'),
        ];
        $stats['sales_month_change'] = $stats['sales_previous_month'] > 0
            ? round((($stats['sales_month'] - $stats['sales_previous_month']) / $stats['sales_previous_month']) * 100, 1)
            : null;

        $lowStockProducts = Product::with(['brand','category'])
            ->whereColumn('stock_quantity', '<=', 'alert_threshold')
            ->orderBy('stock_quantity')
            ->limit(10)->get();

        $expiringBatches = StockBatch::with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', today()->addDays(90))
            ->orderBy('expires_at')
            ->limit(6)
            ->get();

        $periodStart = now()->subDays($days - 1)->startOfDay();
        $dailySales = Sale::query()
            ->selectRaw('DATE(sold_at) as day, SUM(total) as total')
            ->whereBetween('sold_at', [$periodStart, $todayEnd])
            ->groupBy('day')
            ->pluck('total', 'day');

        $chartLabels = [];
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $chartLabels[] = $d->format('d/m');
            $chartData[] = (float) ($dailySales[$d->toDateString()] ?? 0);
        }

        $topProducts = SaleItem::query()
            ->selectRaw('product_name, SUM(quantity) as quantity, SUM(subtotal) as revenue')
            ->whereHas('sale', fn ($query) => $query->whereBetween('sold_at', [$periodStart, $todayEnd]))
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $recentSales = Sale::with('customer','user')->latest('sold_at')->limit(8)->get();
        $recentMovements = StockMovement::with('product', 'user')
            ->latest('movement_date')->latest('id')->limit(6)->get();

        return view('dashboard', compact(
            'stats',
            'lowStockProducts',
            'expiringBatches',
            'chartLabels',
            'chartData',
            'recentSales',
            'recentMovements',
            'topProducts',
            'days',
        ));
    }
}