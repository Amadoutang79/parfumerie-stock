@extends('layouts.app')
@section('title', 'Suivi des lots')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-4 rounded-2xl bg-gradient-to-r from-amber-900 via-orange-800 to-rose-800 p-6 text-white shadow-lg sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-amber-100">Opérations · Traçabilité</p>
            <h2 class="mt-1 text-2xl font-bold">Suivi des lots</h2>
            <p class="mt-2 text-sm text-amber-50/90">Les ventes consomment d’abord les lots dont la date d’expiration est la plus proche.</p>
        </div>
        <a href="{{ route('stock.index') }}" class="rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">Journal de stock</a>
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['Lots avec du stock', $stats['active'], 'indigo'],
            ['À surveiller (90 jours)', $stats['expiring'], 'amber'],
            ['Déjà expirés', $stats['expired'], 'rose'],
        ] as [$label, $value, $tone])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-{{ $tone }}-700">{{ number_format($value, 0, ',', ' ') }}</p>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row">
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="N° de lot, parfum ou référence…" aria-label="Rechercher un lot" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm">
            <select name="status" aria-label="État du lot" class="rounded-lg border-slate-300 text-sm">
                <option value="all" @selected($status === 'all')>Tous les lots</option>
                <option value="expiring" @selected($status === 'expiring')>Expiration sous 90 jours</option>
                <option value="expired" @selected($status === 'expired')>Lots expirés</option>
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Filtrer</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Parfum</th><th class="px-5 py-3">N° de lot</th><th class="px-5 py-3 text-center">Disponible / initial</th><th class="px-5 py-3">Expiration</th><th class="px-5 py-3 text-right">Coût unitaire</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        @php
                            $expired = $batch->expires_at && $batch->expires_at->lt(today());
                            $expiring = $batch->expires_at && !$expired && $batch->expires_at->lte(today()->addDays(90));
                        @endphp
                        <tr class="transition hover:bg-amber-50/30">
                            <td class="px-5 py-3">
                                @if($batch->product)
                                    <a href="{{ route('products.show', $batch->product) }}" class="font-semibold text-slate-800 hover:text-indigo-700">{{ $batch->product->name }}</a>
                                    <span class="block text-xs text-slate-500">{{ $batch->product->reference }} · {{ $batch->product->volume }} ml</span>
                                @else
                                    <span class="text-slate-400">Produit supprimé</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-medium">{{ $batch->lot_number }}</td>
                            <td class="px-5 py-3 text-center">{{ $batch->quantity }} / {{ $batch->initial_quantity }}</td>
                            <td class="px-5 py-3">
                                @if($batch->expires_at)
                                    <span @class(['font-medium text-rose-700' => $expired, 'font-medium text-amber-700' => $expiring, 'text-slate-600' => !$expired && !$expiring])>
                                        {{ $batch->expires_at->format('d/m/Y') }} · {{ $expired ? 'Expiré' : ($expiring ? 'À surveiller' : 'OK') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Non renseignée</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">{{ money($batch->unit_cost) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Aucun lot ne correspond à ces filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">{{ $batches->links() }}</div>
    </section>
</div>
@endsection
