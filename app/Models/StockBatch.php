<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StockBatch extends Model
{
    protected $fillable = [
        'product_id',
        'purchase_id',
        'purchase_item_id',
        'lot_number',
        'expires_at',
        'initial_quantity',
        'quantity',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function saleItems(): BelongsToMany
    {
        return $this->belongsToMany(SaleItem::class, 'sale_item_batches')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
