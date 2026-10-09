<?php
namespace App\Http\Controllers;

use App\Models\{Sale, SaleItem, Product, Purchase};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);

        // Ventes par jour
        $salesByDay = Sale::selectRaw('DATE(sold_at) as day, SUM(total) as total, SUM(profit) as profit, COUNT(*) as count')
            ->whereBetween(DB::raw('DATE(sold_at)'), [$from, $to])
            ->groupBy('day')->orderBy('day')->get();

        // Ventes par mois
        $salesByMonth = Sale::selectRaw("strftime('%Y-%m', sold_at) as month, SUM(total) as total, SUM(profit) as profit, COUNT(*) as count")
            ->groupBy('month')->orderByDesc('month')->limit(12)->get();

        // Top produits
        $topProducts = SaleItem::selectRaw('product_name, SUM(quantity) as qty, SUM(subtotal) as revenue')
            ->whereHas('sale', fn($q) => $q->whereBetween(DB::raw('DATE(sold_at)'), [$from, $to]))
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit(10)->get();

        // Totaux période
        $totals = [
            'revenue' => Sale::whereBetween(DB::raw('DATE(sold_at)'), [$from, $to])->sum('total'),
            'profit'  => Sale::whereBetween(DB::raw('DATE(sold_at)'), [$from, $to])->sum('profit'),
            'count'   => Sale::whereBetween(DB::raw('DATE(sold_at)'), [$from, $to])->count(),
            'purchases' => Purchase::whereBetween('purchased_at', [$from, $to])->sum('total'),
            'stock_value' => (float) Product::sum(DB::raw('stock_quantity * purchase_price')),
            'stock_units' => (int) Product::sum('stock_quantity'),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
        ];
        $totals['average_sale'] = $totals['count'] > 0 ? $totals['revenue'] / $totals['count'] : 0;

        // État du stock
        $stockState = Product::with(['brand','category'])
            ->orderBy('stock_quantity')->get();

        return view('reports.index', compact(
            'from','to','salesByDay','salesByMonth','topProducts','totals','stockState'
        ));
    }

    public function stockExport()
    {
        $products = Product::with(['brand','category'])->orderBy('name')->get();
        $filename = 'stock_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($products) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($out, ['Référence','Nom','Marque','Catégorie','Genre','Volume','Concentration','Prix achat','Prix vente','Marge indicative','Marge (%)','Stock','Seuil','Valeur stock']);
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->reference, $p->name, $p->brand?->name, $p->category?->name,
                    $p->gender_label, $p->volume.' ml', $p->concentration_label,
                    $p->purchase_price, $p->current_price, $p->margin, $p->margin_percent,
                    $p->stock_quantity, $p->alert_threshold, $p->stock_value,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function salesExport(Request $request)
    {
        [$from, $to] = $this->period($request);

        $sales = Sale::with(['customer','user'])
            ->whereBetween(DB::raw('DATE(sold_at)'), [$from, $to])
            ->orderByDesc('sold_at')->get();

        $filename = "ventes_{$from}_{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($sales) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Référence','Date','Client','Vendeur','Sous-total','Remise','Total','Bénéfice','Paiement']);
            foreach ($sales as $s) {
                fputcsv($out, [
                    $s->reference, $s->sold_at->format('d/m/Y H:i'),
                    $s->customer?->name ?? 'Passage',
                    $s->user?->name, $s->subtotal, $s->discount, $s->total, $s->profit,
                    $s->payment_label,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function period(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();

        if ($from > $to) {
            throw ValidationException::withMessages([
                'to' => 'La date de fin doit être égale ou postérieure à la date de début.',
            ]);
        }

        return [$from, $to];
    }
}