<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const CONCENTRATIONS = [
        'EDT' => 'Eau de toilette (EDT)',
        'EDP' => 'Eau de parfum (EDP)',
        'EXTRAIT' => 'Extrait de parfum',
        'EDC' => 'Eau de Cologne (EDC)',
        'BRUME' => 'Brume parfumée',
    ];

    protected $fillable = [
        'name','reference','barcode','brand_id','category_id','gender','volume',
        'concentration','purchase_price','selling_price','promo_price','stock_quantity',
        'alert_threshold','image','description','is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'promo_price'    => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    /* ---------- Relations ---------- */
    public function brand()        { return $this->belongsTo(Brand::class); }
    public function category()     { return $this->belongsTo(Category::class); }
    public function movements()    { return $this->hasMany(StockMovement::class); }
    public function saleItems()    { return $this->hasMany(SaleItem::class); }
    public function purchaseItems(){ return $this->hasMany(PurchaseItem::class); }
    public function batches()       { return $this->hasMany(StockBatch::class); }

    /* ---------- Scopes ---------- */
    public function scopeLowStock($q)  { return $q->whereColumn('stock_quantity', '<=', 'alert_threshold')->where('stock_quantity', '>', 0); }
    public function scopeOutOfStock($q){ return $q->where('stock_quantity', '<=', 0); }
    public function scopeActive($q)    { return $q->where('is_active', true); }

    /* ---------- Accessors ---------- */
    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->promo_price && $this->promo_price > 0
            ? $this->promo_price
            : $this->selling_price);
    }

    public function getStockValueAttribute(): float
    {
        return $this->stock_quantity * (float) $this->purchase_price;
    }

    public function getMarginAttribute(): float
    {
        return $this->current_price - (float) $this->purchase_price;
    }

    public function getMarginPercentAttribute(): float
    {
        return $this->current_price > 0
            ? round(($this->margin / $this->current_price) * 100, 1)
            : 0;
    }

    public function getConcentrationLabelAttribute(): string
    {
        return self::CONCENTRATIONS[$this->concentration] ?? 'Non précisée';
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        $initials = htmlspecialchars(
            mb_strtoupper(mb_substr($this->name, 0, 2)),
            ENT_XML1 | ENT_QUOTES,
            'UTF-8',
        );
        $avatar = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">'
            .'<rect width="128" height="128" rx="18" fill="#6366f1"/>'
            .'<text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" '
            .'font-family="sans-serif" font-size="48" font-weight="700" fill="#fff">'
            .$initials.'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($avatar);
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'homme'   => 'Homme',
            'femme'   => 'Femme',
            default   => 'Unisexe',
        };
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) return 'rupture';
        if ($this->stock_quantity <= $this->alert_threshold) return 'faible';
        return 'ok';
    }

    /* ---------- Génération référence ---------- */
    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->first();
        $num  = $last ? ((int) preg_replace('/\D/', '', $last->reference)) + 1 : 1;
        return 'PRF-'.str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}