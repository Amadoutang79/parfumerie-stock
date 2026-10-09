<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SaleItem extends Model
{
    protected $fillable = ['sale_id','product_id','product_name','quantity','price','purchase_price','subtotal'];

    public function sale()    { return $this->belongsTo(Sale::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(StockBatch::class, 'sale_item_batches')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}