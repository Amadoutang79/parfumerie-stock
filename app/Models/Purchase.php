<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['reference','supplier_id','user_id','total','paid_amount','status','notes','purchased_at'];
    protected $casts = ['purchased_at' => 'date'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function items()    { return $this->hasMany(PurchaseItem::class); }
}