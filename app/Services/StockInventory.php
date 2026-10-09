<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Validation\ValidationException;

class StockInventory
{
    public static function receive(
        Product $product,
        int $quantity,
        float $unitCost,
        string $lotNumber,
        ?string $expiresAt = null,
        ?int $purchaseId = null,
        ?int $purchaseItemId = null,
    ): StockBatch {
        $batch = StockBatch::create([
            'product_id' => $product->id,
            'purchase_id' => $purchaseId,
            'purchase_item_id' => $purchaseItemId,
            'lot_number' => $lotNumber,
            'expires_at' => $expiresAt,
            'initial_quantity' => $quantity,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
        ]);

        $product->update(['stock_quantity' => $product->stock_quantity + $quantity]);

        return $batch;
    }

    /**
     * Decrease stock using FEFO; batches without an expiry are consumed last.
     *
     * @return array<int, int> Batch IDs mapped to quantities consumed.
     */
    public static function issue(
        Product $product,
        int $quantity,
        bool $excludeExpired = false,
        string $errorField = 'items',
    ): array
    {
        $batches = StockBatch::query()
            ->where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->when($excludeExpired, fn ($query) => $query
                ->where(fn ($eligible) => $eligible
                    ->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', today())))
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expires_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($batches->sum('quantity') < $quantity) {
            throw ValidationException::withMessages([
                $errorField => "Les quantités disponibles par lot pour {$product->name} sont insuffisantes.",
            ]);
        }

        $remaining = $quantity;
        $allocations = [];

        foreach ($batches as $batch) {
            if ($remaining === 0) {
                break;
            }

            $issued = min($remaining, $batch->quantity);
            $batch->decrement('quantity', $issued);
            $allocations[$batch->id] = $issued;
            $remaining -= $issued;
        }

        $product->decrement('stock_quantity', $quantity);

        return $allocations;
    }

    public static function setQuantity(Product $product, int $quantity, string $errorField = 'items'): void
    {
        $difference = $quantity - $product->stock_quantity;

        if ($difference > 0) {
            self::receive(
                $product,
                $difference,
                (float) $product->purchase_price,
                'AJUST-'.$product->reference.'-'.now()->format('YmdHis'),
            );
        } elseif ($difference < 0) {
            self::issue($product, abs($difference), errorField: $errorField);
        }
    }

    /**
     * Restore the exact batches consumed by a sale.
     *
     * @param  array<int, int>  $allocations
     */
    public static function restore(Product $product, array $allocations): void
    {
        $restored = 0;

        foreach ($allocations as $batchId => $quantity) {
            $batch = StockBatch::query()->lockForUpdate()->find($batchId);

            if ($batch) {
                $batch->increment('quantity', $quantity);
                $restored += $quantity;
            }
        }

        if ($restored !== array_sum($allocations)) {
            throw ValidationException::withMessages([
                'sale' => 'Impossible de restaurer tous les lots consommés par cette vente.',
            ]);
        }

        $product->increment('stock_quantity', $restored);
    }
}
