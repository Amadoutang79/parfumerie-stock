<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Services\StockInventory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user']);

        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'payment_method' => ['nullable', 'in:cash,mobile_money,card,other'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        if ($from = $filters['from'] ?? null) {
            $query->whereDate('sold_at', '>=', $from);
        }
        if ($to = $filters['to'] ?? null) {
            $query->whereDate('sold_at', '<=', $to);
        }
        if ($pm = $filters['payment_method'] ?? null) {
            $query->where('payment_method', $pm);
        }
        if ($s = $filters['search'] ?? null) {
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%"));
            });
        }

        $sales = $query->latest('sold_at')->paginate(20)->withQueryString();
        $total = (clone $query)->sum('total');
        $paid = (clone $query)->sum('paid_amount');
        $profit = (clone $query)->sum('profit');

        return view('sales.index', compact('sales', 'total', 'paid', 'profit') + [
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('sales.create', [
            'products' => Product::active()->with('brand')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'settings' => Setting::current(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,mobile_money,card,other',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $sale = DB::transaction(function () use ($data) {
            $vatRate = (float) Setting::current()->vat_rate;
            $subtotal = 0;
            $profit = 0;
            $lines = [];
            $itemsByProduct = collect($data['items'])->groupBy('product_id');
            $products = Product::query()
                ->whereIn('id', $itemsByProduct->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($itemsByProduct as $productId => $items) {
                $product = $products->get($productId);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => 'Un des parfums sélectionnés n’est plus disponible.',
                    ]);
                }

                $requestedQuantity = $items->sum('quantity');
                if ($product->stock_quantity < $requestedQuantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuffisant pour {$product->name} (disponible : {$product->stock_quantity}).",
                    ]);
                }
            }

            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);
                $price = $product->current_price;

                $lineSubtotal = $item['quantity'] * $price;
                $lineProfit = $item['quantity'] * ($price - $product->purchase_price);

                $subtotal += $lineSubtotal;
                $profit += $lineProfit;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = (float) ($data['discount'] ?? 0);
            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'La remise ne peut pas dépasser le sous-total.',
                ]);
            }

            $vatAmount = round(($subtotal - $discount) * $vatRate / 100, 2);
            $total = $subtotal - $discount + $vatAmount;
            $paid = (float) ($data['paid_amount'] ?? $total);

            $sale = Sale::create([
                'reference' => ref('VTE'),
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total' => $total,
                'profit' => $profit - $discount,
                'payment_method' => $data['payment_method'],
                'paid_amount' => $paid,
                'change_amount' => max(0, $paid - $total),
                'notes' => $data['notes'] ?? null,
                'sold_at' => now(),
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];
                $before = $product->stock_quantity;

                $saleItem = $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'purchase_price' => $product->purchase_price,
                    'subtotal' => $line['subtotal'],
                ]);

                $allocations = StockInventory::issue($product, $line['quantity'], excludeExpired: true);
                foreach ($allocations as $batchId => $quantity) {
                    $saleItem->batches()->attach($batchId, ['quantity' => $quantity]);
                }
                $after = $product->fresh()->stock_quantity;

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $line['quantity'],
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'unit_price' => $line['price'],
                    'reason' => 'Vente '.$sale->reference,
                    'reference' => $sale->reference,
                    'user_id' => auth()->id(),
                    'movement_date' => now(),
                ]);
            }

            return $sale;
        });

        return redirect()->route('sales.ticket', $sale)->with('success', 'Vente enregistrée.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'items.batches', 'customer', 'user']);

        return view('sales.show', compact('sale'));
    }

    public function ticket(Sale $sale)
    {
        $sale->load(['items', 'customer', 'user']);

        return view('sales.ticket', [
            'sale' => $sale,
            'settings' => Setting::current(),
        ]);
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $sale->load('items');
            foreach ($sale->items as $item) {
                if ($item->product) {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);
                    if (!$product) {
                        continue;
                    }
                    $before = $product->stock_quantity;
                    $allocations = DB::table('sale_item_batches')
                        ->where('sale_item_id', $item->id)
                        ->pluck('quantity', 'stock_batch_id')
                        ->map(fn ($quantity) => (int) $quantity)
                        ->all();

                    if ($allocations) {
                        StockInventory::restore($product, $allocations);
                    } else {
                        StockInventory::receive(
                            $product,
                            $item->quantity,
                            (float) $item->purchase_price,
                            'RETOUR-'.$sale->reference.'-'.$item->id,
                        );
                    }
                    $after = $product->fresh()->stock_quantity;

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'reason' => 'Annulation vente '.$sale->reference,
                        'reference' => $sale->reference,
                        'user_id' => auth()->id(),
                        'movement_date' => now(),
                    ]);
                }
            }
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Vente annulée.');
    }
}
