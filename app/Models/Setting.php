<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    public const CACHE_KEY = 'company_settings_v2';

    public const COUNTRIES = [
        'ML' => ['name' => 'Mali', 'flag' => '🇲🇱'],
        'SN' => ['name' => 'Sénégal', 'flag' => '🇸🇳'],
        'CI' => ['name' => 'Côte d’Ivoire', 'flag' => '🇨🇮'],
        'BF' => ['name' => 'Burkina Faso', 'flag' => '🇧🇫'],
        'NE' => ['name' => 'Niger', 'flag' => '🇳🇪'],
        'GN' => ['name' => 'Guinée', 'flag' => '🇬🇳'],
        'MA' => ['name' => 'Maroc', 'flag' => '🇲🇦'],
        'DZ' => ['name' => 'Algérie', 'flag' => '🇩🇿'],
        'FR' => ['name' => 'France', 'flag' => '🇫🇷'],
        'BE' => ['name' => 'Belgique', 'flag' => '🇧🇪'],
        'US' => ['name' => 'États-Unis', 'flag' => '🇺🇸'],
    ];

    public const CURRENCIES = [
        'FCFA' => 'Franc CFA',
        'EUR' => 'Euro',
        'USD' => 'Dollar américain',
        'MAD' => 'Dirham marocain',
        'DZD' => 'Dinar algérien',
    ];

    public const CURRENCY_SYMBOLS = [
        'FCFA' => 'F',
        'EUR' => '€',
        'USD' => '$',
        'MAD' => 'د.م.',
        'DZD' => 'دج',
    ];

    public const TIMEZONES = [
        'Africa/Bamako',
        'Africa/Abidjan',
        'Africa/Dakar',
        'Africa/Ouagadougou',
        'Africa/Casablanca',
        'Europe/Paris',
    ];

    public const DATE_FORMATS = [
        'd/m/Y' => '06/10/2026',
        'm/d/Y' => '10/06/2026',
        'Y-m-d' => '2026-10-06',
        'd-m-Y' => '06-10-2026',
    ];

    public const PRIMARY_COLORS = [
        '#F97316' => 'Orange',
        '#EF4444' => 'Rouge',
        '#EC4899' => 'Rose',
        '#8B5CF6' => 'Violet',
        '#3B82F6' => 'Bleu',
        '#06B6D4' => 'Cyan',
        '#10B981' => 'Vert',
        '#F59E0B' => 'Ambre',
    ];

    protected $fillable = [
        'company_name',
        'tagline',
        'email',
        'website',
        'phone',
        'tax_id',
        'address',
        'city',
        'country',
        'description',
        'currency',
        'currency_symbol',
        'vat_rate',
        'timezone',
        'date_format',
        'primary_color',
        'invoice_email',
        'email_notifications',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'email_notifications' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        $attributes = Cache::remember(self::CACHE_KEY, now()->addHour(), function (): array {
            $applicationName = config('app.name');

            return self::query()->firstOrCreate(['id' => 1], [
                'company_name' => $applicationName && $applicationName !== 'Laravel'
                    ? $applicationName
                    : 'Parfumerie Stock',
                'email' => 'contact@example.com',
                'phone' => '+223 00 00 00 00',
                'city' => 'Bamako',
                'country' => 'ML',
                'currency' => 'FCFA',
                'currency_symbol' => 'F',
                'vat_rate' => 18,
                'timezone' => 'Africa/Bamako',
                'date_format' => 'd/m/Y',
                'primary_color' => '#3B82F6',
                'email_notifications' => true,
            ])->getAttributes();
        });

        $setting = new self;
        $setting->setRawAttributes($attributes, true);
        $setting->exists = true;

        return $setting;
    }

    public static function getInstance(): self
    {
        return self::current();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::current()->getAttribute($key) ?? $default;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function getCountryDetailsAttribute(): array
    {
        return self::COUNTRIES[$this->country] ?? ['name' => $this->country, 'flag' => '🌍'];
    }
}
