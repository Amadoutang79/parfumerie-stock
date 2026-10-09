@extends('layouts.app')
@section('title', 'Ventes')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-5 rounded-2xl bg-gradient-to-r from-violet-900 via-fuchsia-800 to-rose-700 p-6 text-white shadow-lg md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm text-fuchsia-100">Opérations · Ventes</p><h2 class="mt-1 text-2xl font-bold">Le suivi de vos encaissements</h2><p class="mt-2 text-sm text-fuchsia-50/90">Retrouvez une transaction, vérifiez son règlement et réimprimez son ticket.</p></div>
        @can('sales.create')<a href="{{ route('sales.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-fuchsia-900 shadow transition hover:-translate-y-0.5">＋ Nouvelle vente</a>@endcan
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['Ventes correspondantes', number_format($sales->total(), 0, ',', ' '), '▤', 'indigo'],
            ['Chiffre d’affaires filtré', money($total), '↗', 'fuchsia'],
            ['Bénéfice filtré', money($profit), '◆', 'emerald'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md" style="--enter-delay: {{ $loop->index * 60 }}ms"><div class="flex items-center justify-between gap-3"><div><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 break-words text-xl font-bold text-slate-900">{{ $value }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $tone }}-50 text-lg">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <div class="mb-4 flex items-center justify-between"><div><h3 class="font-semibold text-slate-900">Historique des ventes</h3><p class="text-sm text-slate-500">{{ $sales->total() }} résultat(s) · {{ money($paid) }} encaissé(s)</p></div>@if(collect($filters)->filter()->isNotEmpty())<a href="{{ route('sales.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Effacer les filtres</a>@endif</div>
            <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Référence ou client…" aria-label="Rechercher une vente" class="rounded-lg border-slate-300 text-sm">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="Date de début" class="rounded-lg border-slate-300 text-sm">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="Date de fin" class="rounded-lg border-slate-300 text-sm">
                <select name="payment_method" aria-label="Mode de paiement" class="rounded-lg border-slate-300 text-sm"><option value="">Tous les paiements</option>@foreach(['cash'=>'Espèces','mobile_money'=>'Mobile Money','card'=>'Carte','other'=>'Autre'] as $key => $label)<option value="{{ $key }}" @selected(($filters['payment_method'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
                <div class="flex gap-2"><button class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrer</button><a href="{{ route('sales.index') }}" class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200">Reset</a></div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Transaction</th><th class="px-5 py-3 font-medium">Client · vendeur</th><th class="px-5 py-3 font-medium">Paiement</th><th class="px-5 py-3 text-right font-medium">Total</th><th class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                        <tr class="transition hover:bg-fuchsia-50/30">
                            <td class="whitespace-nowrap px-5 py-4"><a href="{{ route('sales.show', $sale) }}" class="font-semibold text-indigo-700 hover:text-indigo-900">{{ $sale->reference }}</a><span class="block text-xs text-slate-500">{{ company_date($sale->sold_at, true) }}</span></td>
                            <td class="px-5 py-4"><p class="font-medium text-slate-800">{{ $sale->customer?->name ?? 'Client de passage' }}</p><p class="text-xs text-slate-500">{{ $sale->user?->name ?? '—' }}</p></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $sale->payment_label }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-slate-900">{{ money($sale->total) }}<span class="block text-xs font-normal text-slate-500">Payé {{ money($sale->paid_amount) }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('sales.ticket', $sale) }}" target="_blank" rel="noopener" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">Ticket</a>@can('sales.delete')<form method="POST" action="{{ route('sales.destroy', $sale) }}" class="ml-1 inline" onsubmit="return confirm('Annuler cette vente et restituer les quantités au stock ?')">@csrf @method('DELETE')<button class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Annuler</button></form>@endcan</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center"><div class="text-3xl">🛒</div><p class="mt-2 font-semibold text-slate-800">Aucune vente trouvée</p><p class="mt-1 text-sm text-slate-500">Essayez de modifier les dates ou les critères de recherche.</p>@can('sales.create')<a href="{{ route('sales.create') }}" class="mt-4 inline-flex rounded-lg bg-fuchsia-700 px-4 py-2 text-sm font-semibold text-white hover:bg-fuchsia-800">Enregistrer une vente</a>@endcan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">{{ $sales->links() }}</div>
    </section>
</div>
@endsection