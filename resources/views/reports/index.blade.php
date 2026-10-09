@extends('layouts.app')
@section('title', 'Rapports')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-5 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-900 to-violet-800 p-6 text-white shadow-lg md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm text-indigo-200">Pilotage · Analyse d’activité</p><h2 class="mt-1 text-2xl font-bold">Les chiffres de votre parfumerie</h2><p class="mt-2 max-w-xl text-sm text-indigo-100">Comparez les ventes, repérez les produits performants et surveillez la valeur du stock.</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('reports.sales.export', ['from' => $from, 'to' => $to]) }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-900 shadow transition hover:-translate-y-0.5">↓ Exporter les ventes</a><a href="{{ route('reports.stock.export') }}" class="rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">↓ Exporter le stock</a></div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3"><h3 class="font-semibold text-slate-900">Période du rapport</h3><p class="text-sm text-slate-500">Choisissez les dates à analyser, puis appliquez les filtres.</p></div>
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <label class="text-xs font-medium text-slate-600">Du<input type="date" name="from" value="{{ $from }}" max="{{ now()->toDateString() }}" required class="mt-1 block rounded-lg border-slate-300 text-sm"></label>
            <label class="text-xs font-medium text-slate-600">Au<input type="date" name="to" value="{{ $to }}" max="{{ now()->toDateString() }}" required class="mt-1 block rounded-lg border-slate-300 text-sm"></label>
            <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Actualiser le rapport</button>
            <a href="{{ route('reports.index') }}" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Ce mois</a>
        </form>
    </section>

    <section aria-label="Résumé de la période" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Chiffre d’affaires', money($totals['revenue']), '↗', 'indigo'],
            ['Bénéfice réalisé', money($totals['profit']), '◆', 'emerald'],
            ['Ventes enregistrées', number_format($totals['count'], 0, ',', ' '), '🛍', 'fuchsia'],
            ['Panier moyen', money($totals['average_sale']), '◉', 'sky'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md" style="--enter-delay: {{ $loop->index * 60 }}ms"><div class="flex items-center justify-between gap-3"><div><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 break-words text-xl font-bold text-slate-900">{{ $value }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $tone }}-50 text-lg">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([
            ['Achats fournisseurs sur la période', money($totals['purchases']), 'sky'],
            ['Valeur d’achat du stock actuel', money($totals['stock_value']), 'amber'],
            ['Stock à surveiller', $totals['low_stock'] . ' faible(s) · ' . $totals['out_of_stock'] . ' rupture(s)', 'rose'],
        ] as [$label, $value, $tone])
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-medium text-slate-500">{{ $label }}</p><p class="mt-2 break-words font-bold text-{{ $tone }}-700">{{ $value }}</p></article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4"><h3 class="font-semibold text-slate-900">Ventes et bénéfices par jour</h3><p class="mt-1 text-sm text-slate-500">Du {{ company_date($from) }} au {{ company_date($to) }}</p></div>
            <div class="relative h-72"><canvas id="dailyChart" class="block h-full w-full" data-local-chart data-chart-type="bar" data-chart-legend="true"
                data-chart-labels="{{ json_encode($salesByDay->pluck('day')) }}"
                data-chart-datasets="{{ json_encode([
                    ['label' => 'Chiffre d’affaires', 'values' => $salesByDay->pluck('total'), 'color' => 'rgba(99,102,241,.78)'],
                    ['label' => 'Bénéfice', 'values' => $salesByDay->pluck('profit'), 'color' => 'rgba(16,185,129,.78)'],
                ]) }}"
                role="img" aria-label="Graphique des ventes quotidiennes"></canvas></div>
        </article>
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5"><h3 class="font-semibold text-slate-900">Parfums les plus vendus</h3><p class="mt-1 text-sm text-slate-500">Top 10 sur la période sélectionnée</p></div>
            <div class="overflow-x-auto px-5"><table class="w-full text-left text-sm"><thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-3 font-medium">Parfum</th><th class="py-3 text-center font-medium">Qté</th><th class="py-3 text-right font-medium">Chiffre d’affaires</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($topProducts as $product)<tr><td class="max-w-56 truncate py-3 font-medium text-slate-800">{{ $product->product_name }}</td><td class="py-3 text-center">{{ number_format($product->qty, 0, ',', ' ') }}</td><td class="whitespace-nowrap py-3 text-right font-semibold text-slate-700">{{ money($product->revenue) }}</td></tr>@empty<tr><td colspan="3" class="py-10 text-center text-slate-500">Aucune vente sur cette période.</td></tr>@endforelse</tbody></table></div>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5"><h3 class="font-semibold text-slate-900">Tendance mensuelle</h3><p class="mt-1 text-sm text-slate-500">Les 12 derniers mois disponibles</p></div>
        <div class="overflow-x-auto px-5"><table class="w-full min-w-[640px] text-left text-sm"><thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-3 font-medium">Mois</th><th class="py-3 text-center font-medium">Ventes</th><th class="py-3 text-right font-medium">Chiffre d’affaires</th><th class="py-3 text-right font-medium">Bénéfice</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($salesByMonth as $month)<tr class="transition hover:bg-slate-50"><td class="py-3 font-medium text-slate-800">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month->month)->translatedFormat('F Y') }}</td><td class="py-3 text-center">{{ number_format($month->count, 0, ',', ' ') }}</td><td class="py-3 text-right">{{ money($month->total) }}</td><td class="py-3 text-right font-semibold text-emerald-700">{{ money($month->profit) }}</td></tr>@empty<tr><td colspan="4" class="py-8 text-center text-slate-500">Aucune donnée de vente.</td></tr>@endforelse</tbody></table></div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h3 class="font-semibold text-slate-900">Inventaire actuel</h3><p class="mt-1 text-sm text-slate-500">{{ number_format($stockState->count(), 0, ',', ' ') }} référence(s) · {{ number_format($totals['stock_units'], 0, ',', ' ') }} unité(s)</p></div><a href="{{ route('reports.stock.export') }}" class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">Exporter CSV</a></div>
        <div class="max-h-[30rem] overflow-auto">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="sticky top-0 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Parfum</th><th class="px-5 py-3 font-medium">Marque</th><th class="px-5 py-3 text-center font-medium">Stock · seuil</th><th class="px-5 py-3 text-right font-medium">Valeur achat</th><th class="px-5 py-3 text-right font-medium">Valeur vente</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse($stockState as $product)<tr class="transition hover:bg-slate-50"><td class="px-5 py-3 font-medium text-slate-800">{{ $product->name }}</td><td class="px-5 py-3 text-slate-600">{{ $product->brand?->name ?? '—' }}</td><td class="px-5 py-3 text-center"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold','bg-emerald-50 text-emerald-700' => $product->stock_status === 'ok','bg-amber-50 text-amber-700' => $product->stock_status === 'faible','bg-rose-50 text-rose-700' => $product->stock_status === 'rupture'])>{{ $product->stock_quantity }} / {{ $product->alert_threshold }}</span></td><td class="whitespace-nowrap px-5 py-3 text-right">{{ money($product->stock_value) }}</td><td class="whitespace-nowrap px-5 py-3 text-right">{{ money($product->stock_quantity * $product->current_price) }}</td></tr>@empty<tr><td colspan="5" class="py-8 text-center text-slate-500">Aucun produit en catalogue.</td></tr>@endforelse</tbody>
            </table>
        </div>
    </section>
</div>
@endsection
