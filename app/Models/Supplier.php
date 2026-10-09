<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name','phone','whatsapp','email','address','notes','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function purchases() { return $this->hasMany(Purchase::class); }
    public function products()  { return $this->belongsToMany(Product::class, 'purchase_items', 'purchase_id', 'product_id'); }
}