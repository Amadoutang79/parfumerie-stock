<?php
namespace App\Http\Controllers;

use App\Models\{Product, StockMovement};
use App\Services\StockInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:in,out,adjustment'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = StockMovement::with(['product','user']);

        if ($type = $filters['type'] ?? null)       $query->where('type', $type);
        if ($pid  = $filters['product_id'] ?? null) $query->where('product_id', $pid);
        if ($from = $filters['from'] ?? null)       $query->whereDate('movement_date', '>=', $from);
        if ($to   = $filters['to'] ?? null)         $query->whereDate('movement_date', '<=', $to);
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(fn ($builder) => $builder
                ->where('reference', 'like', "%{$search}%")
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($product) => $product
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")));
        }

        $movements = $query->latest('movement_date')->latest('id')->paginate(25)->withQueryString();
        $stats = [
            'total' => StockMovement::count(),
            'entries' => StockMovement::where('type', 'in')->count(),
            'exits' => StockMovement::where('type', 'out')->count(),
            'adjustments' => StockMovement::where('type', 'adjustment')->count(),
        ];

        return view('stock.index', [
            'movements' => $movements,
            'stats' => $stats,
            'products'  => Product::orderBy('name')->get(),
            'filters'   => $filters,
        ]);
    }

    public function lots(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,expiring,expired'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $filters['status'] ?? 'all';
        $query = \App\Models\StockBatch::with('product')->where('quantity', '>', 0);

        if ($status === 'expired') {
            $query->whereDate('expires_at', '<', today());
        } elseif ($status === 'expiring') {
            $query->whereDate('expires_at', '>=', today())
                ->whereDate('expires_at', '<=', today()->addDays(90));
        }

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(fn ($builder) => $builder
                ->where('lot_number', 'like', "%{$search}%")
                ->orWhereHas('product', fn ($product) => $product
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")));
        }

        $batches = $query
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expires_at')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'active' => \App\Models\StockBatch::where('quantity', '>', 0)->count(),
            'expiring' => \App\Models\StockBatch::where('quantity', '>', 0)
                ->whereDate('expires_at', '>=', today())
                ->whereDate('expires_at', '<=', today()->addDays(90))
                ->count(),
            'expired' => \App\Models\StockBatch::where('quantity', '>', 0)
                ->whereDate('expires_at', '<', today())
                ->count(),
        ];

        return view('stock.lots', compact('batches', 'filters', 'stats', 'status'));
    }

    public function create(Request $request)
    {
        return view('stock.create', [
            'products' => Product::orderBy('name')->get(),
            'type'     => $request->input('type', 'in'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'type'          => 'required|in:in,out,adjustment',
            'quantity'      => ['required', 'integer', $request->input('type') === 'adjustment' ? 'min:0' : 'min:1'],
            'unit_price'    => 'nullable|numeric|min:0',
            'lot_number'    => 'nullable|string|max:100',
            'expires_at'    => ['nullable', 'date', 'after_or_equal:movement_date'],
            'reason'        => 'nullable|string|max:255',
            'reference'     => 'nullable|string|max:255',
            'movement_date' => 'required|date',
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $before  = $product->stock_quantity;

            if ($data['type'] === 'out' && $data['quantity'] > $before) {
                throw ValidationException::withMessages([
                    'quantity' => "La sortie dépasse le stock disponible ({$before}).",
                ]);
            }

            if ($data['type'] === 'in') {
                StockInventory::receive(
                    $product,
                    $data['quantity'],
                    (float) ($data['unit_price'] ?? $product->purchase_price),
                    trim((string) ($data['lot_number'] ?? ''))
                        ?: 'ENTREE-'.now()->format('YmdHis').'-'.$product->id,
                    $data['expires_at'] ?? null,
                );
            } elseif ($data['type'] === 'out') {
                StockInventory::issue($product, $data['quantity'], errorField: 'quantity');
            } else {
                StockInventory::setQuantity($product, $data['quantity'], 'quantity');
            }
            $after = $product->fresh()->stock_quantity;

            StockMovement::create([
                'product_id'    => $product->id,
                'type'          => $data['type'],
                'quantity'      => $data['type'] === 'adjustment' ? $after - $before : $data['quantity'],
                'stock_before'  => $before,
                'stock_after'   => $after,
                'unit_price'    => $data['unit_price'] ?? null,
                'reason'        => $data['reason'] ?? null,
                'reference'     => $data['reference'] ?? null,
                'user_id'       => auth()->id(),
                'movement_date' => $data['movement_date'],
            ]);
        });

        return redirect()->route('stock.index')->with('success', 'Mouvement enregistré.');
    }
}