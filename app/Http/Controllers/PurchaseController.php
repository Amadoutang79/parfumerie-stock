<?php
namespace App\Http\Controllers;

use App\Models\{Product, Purchase, Supplier, StockMovement};
use App\Models\StockBatch;
use App\Services\StockInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = Purchase::with(['supplier','user']);
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(fn ($builder) => $builder
                ->where('reference', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%")));
        }
        if ($from = $filters['from'] ?? null) {
            $query->whereDate('purchased_at', '>=', $from);
        }
        if ($to = $filters['to'] ?? null) {
            $query->whereDate('purchased_at', '<=', $to);
        }

        $purchases = $query->latest('purchased_at')->paginate(20)->withQueryString();
        $stats = [
            'count' => Purchase::count(),
            'total' => (float) (clone $query)->sum('total'),
            'paid' => (float) (clone $query)->sum('paid_amount'),
            'outstanding' => max(0, (float) (clone $query)->sum(DB::raw('total - paid_amount'))),
        ];

        return view('purchases.index', compact('purchases', 'stats', 'filters'));
    }

    public function create()
    {
        return view('purchases.create', [
            'products'  => Product::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'             => 'nullable|exists:suppliers,id',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.purchase_price'  => 'required|numeric|min:0',
            'items.*.lot_number'      => 'nullable|string|max:100',
            'items.*.expires_at'      => 'nullable|date|after_or_equal:purchased_at',
            'paid_amount'             => 'nullable|numeric|min:0',
            'purchased_at'            => 'required|date',
            'notes'                   => 'nullable|string',
        ]);

        $purchase = DB::transaction(function () use ($data) {
            $total = 0;
            foreach ($data['items'] as $i) $total += $i['quantity'] * $i['purchase_price'];

            $purchase = Purchase::create([
                'reference'    => ref('ACH'),
                'supplier_id'  => $data['supplier_id'] ?? null,
                'user_id'      => auth()->id(),
                'total'        => $total,
                'paid_amount'  => $data['paid_amount'] ?? $total,
                'status'       => 'received',
                'notes'        => $data['notes'] ?? null,
                'purchased_at' => $data['purchased_at'],
            ]);

            foreach ($data['items'] as $index => $i) {
                $product = Product::lockForUpdate()->findOrFail($i['product_id']);
                $before  = $product->stock_quantity;
                $lotNumber = trim((string) ($i['lot_number'] ?? ''))
                    ?: $purchase->reference.'-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

                $purchaseItem = $purchase->items()->create([
                    'product_id'     => $product->id,
                    'quantity'       => $i['quantity'],
                    'purchase_price' => $i['purchase_price'],
                    'subtotal'       => $i['quantity'] * $i['purchase_price'],
                ]);

                $product->update(['purchase_price' => $i['purchase_price']]);
                StockInventory::receive(
                    $product,
                    $i['quantity'],
                    (float) $i['purchase_price'],
                    $lotNumber,
                    $i['expires_at'] ?? null,
                    $purchase->id,
                    $purchaseItem->id,
                );
                $after = $product->fresh()->stock_quantity;

                StockMovement::create([
                    'product_id'    => $product->id,
                    'type'          => 'in',
                    'quantity'      => $i['quantity'],
                    'stock_before'  => $before,
                    'stock_after'   => $after,
                    'unit_price'    => $i['purchase_price'],
                    'reason'        => 'Achat '.$purchase->reference,
                    'reference'     => $purchase->reference,
                    'user_id'       => auth()->id(),
                    'movement_date' => $data['purchased_at'],
                ]);
            }
            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Achat enregistré.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items.product','items.batch','supplier','user']);
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if (!$product) continue;
                $before = $product->stock_quantity;

                $batch = StockBatch::query()
                    ->where('purchase_item_id', $item->id)
                    ->lockForUpdate()
                    ->first();

                if ($batch && $batch->quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'purchase' => "Impossible d’annuler cet achat : le lot {$batch->lot_number} a déjà été utilisé.",
                    ]);
                }

                if (!$batch && $before < $item->quantity) {
                    throw ValidationException::withMessages([
                        'purchase' => "Impossible d’annuler cet achat : le stock de {$product->name} est inférieur à la quantité reçue.",
                    ]);
                }

                if ($batch) {
                    $batch->delete();
                }

                if (!$batch) {
                    StockInventory::issue($product, $item->quantity, errorField: 'purchase');
                } else {
                    $product->decrement('stock_quantity', $item->quantity);
                }
                $after = $product->fresh()->stock_quantity;

                StockMovement::create([
                    'product_id'    => $product->id,
                    'type'          => 'out',
                    'quantity'      => $item->quantity,
                    'stock_before'  => $before,
                    'stock_after'   => $after,
                    'reason'        => 'Annulation achat '.$purchase->reference,
                    'reference'     => $purchase->reference,
                    'user_id'       => auth()->id(),
                    'movement_date' => now(),
                ]);
            }
            $purchase->delete();
        });

        return back()->with('success', 'Achat supprimé.');
    }
}