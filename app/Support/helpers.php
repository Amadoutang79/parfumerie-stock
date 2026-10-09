<?php

use App\Models\Setting;
use Illuminate\Support\Carbon;

if (! function_exists('money')) {
    /**
     * Formate un montant avec la devise.
     * Ex: money(2500) → "2 500 FCFA"
     */
    function money($value, ?string $currency = null): string
    {
        $currency = $currency ?? (Setting::get('currency_symbol') ?? config('app.currency', 'FCFA'));

        return number_format((float) $value, 0, ',', ' ').' '.$currency;
    }
}

if (! function_exists('ref')) {
    /**
     * Génère une référence unique.
     * Ex: ref('VTE') → "VTE-20261005-A1B2C"
     */
    function ref(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -5));
    }
}

if (! function_exists('money_short')) {
    /**
     * Version courte pour les gros montants.
     * Ex: money_short(2500000) → "2.5M F"
     */
    function money_short($value): string
    {
        $v = (float) $value;
        $cur = Setting::get('currency_symbol') ?? config('app.currency', 'FCFA');

        if ($v >= 1_000_000) {
            return round($v / 1_000_000, 1).'M '.$cur;
        }
        if ($v >= 1_000) {
            return round($v / 1_000, 1).'K '.$cur;
        }

        return number_format($v, 0, ',', ' ').' '.$cur;
    }
}

if (! function_exists('company_date')) {
    function company_date($value, bool $withTime = false): string
    {
        $format = Setting::get('date_format', 'd/m/Y');

        return Carbon::parse($value)
            ->format($format.($withTime ? ' H:i' : ''));
    }
}
