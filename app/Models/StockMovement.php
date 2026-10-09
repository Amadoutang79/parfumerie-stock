<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id','type','quantity','stock_before','stock_after',
        'unit_price','reason','reference','user_id','movement_date',
    ];

    protected $casts = ['movement_date' => 'date'];

    public function product() { return $this->belongsTo(Product::class); }
    public function user()    { return $this->belongsTo(User::class); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in'         => 'Entrée',
            'out'        => 'Sortie',
            'adjustment' => 'Ajustement',
            default      => $this->type,
        };
    }
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'in'         => 'bg-emerald-100 text-emerald-700',
            'out'        => 'bg-rose-100 text-rose-700',
            'adjustment' => 'bg-amber-100 text-amber-700',
            default      => 'bg-slate-100 text-slate-700',
        };
    }
}