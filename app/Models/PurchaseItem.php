<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id','product_id','quantity','purchase_price','subtotal'];

    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function product()  { return $this->belongsTo(Product::class); }
    public function batch()    { return $this->hasOne(StockBatch::class); }
}