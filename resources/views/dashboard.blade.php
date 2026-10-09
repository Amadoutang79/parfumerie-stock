@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
<div class="space-y-6">
    <section class="animate-enter relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-fuchsia-950 px-6 py-7 text-white shadow-lg sm:px-8">
        <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-fuchsia-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-48 w-48 rounded-full bg-indigo-400/20 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-200">{{ now()->translatedFormat('l d F Y') }}</p>
                <h2 class="mt-2 text-2xl font-bold sm:text-3xl">Bonjour {{ auth()->user()->name }} 👋</h2>
                <p class="mt-2 max-w-xl text-sm text-slate-300">Voici les chiffres clés et les actions utiles pour piloter votre parfumerie.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @can('sales.create')
                    <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-950 shadow transition hover:-translate-y-0.5 hover:bg-indigo-50">
                        <span aria-hidden="true">＋</span> Nouvelle vente
                    </a>
                @endcan
                @can('products.create')
                    <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20">
                        <span aria-hidden="true">🧴</span> Ajouter un parfum
                    </a>
                @endcan
                @can('stock.adjust')
                    <a href="{{ route('stock.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20">
                        <span aria-hidden="true">📦</span> Mouvement de stock
                    </a>
                @endcan
            </div>
        </div>
    </section>

    @php
        $cards = [
            ['label' => 'Chiffre d’affaires du mois', 'value' => money($stats['sales_month']), 'icon' => '↗', 'tone' => 'indigo', 'hint' => $stats['sales_month_change'] === null ? 'Pas de ventes le mois précédent' : (($stats['sales_month_change'] >= 0 ? '+' : '') . $stats['sales_month_change'] . '% vs mois précédent')],
            ['label' => 'Ventes aujourd’hui', 'value' => money($stats['sales_today']), 'icon' => '◷', 'tone' => 'fuchsia', 'hint' => 'Total encaissé aujourd’hui'],
            ['label' => 'Bénéfice du mois', 'value' => money($stats['profit_month']), 'icon' => '◆', 'tone' => 'emerald', 'hint' => 'Marge cumulée sur les ventes'],
            ['label' => 'Valeur du stock', 'value' => money($stats['stock_value']), 'icon' => '▦', 'tone' => 'amber', 'hint' => $stats['stock_total'] . ' unités · ' . $stats['products_count'] . ' références'],
        ];
    @endphp

    <section aria-label="Indicateurs clés" class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
        @foreach($cards as $card)
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                     style="--enter-delay: {{ $loop->index * 70 }}ms">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 break-words text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-{{ $card['tone'] }}-50 text-xl font-bold text-{{ $card['tone'] }}-600">{{ $card['icon'] }}</span>
                </div>
                <p class="mt-3 text-xs {{ $card['tone'] === 'indigo' && $stats['sales_month_change'] !== null ? ($stats['sales_month_change'] >= 0 ? 'text-emerald-600' : 'text-rose-600') : 'text-slate-500' }}">{{ $card['hint'] }}</p>
            </article>
        @endforeach
    </section>

    <section aria-label="Raccourcis de suivi" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @can('customers.view')
            <a href="{{ route('customers.index') }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-indigo-200 hover:shadow">
                <span>
                    <span class="block text-xs font-medium text-slate-500">Clients enregistrés</span>
                    <span class="mt-1 block text-lg font-bold text-slate-800">{{ number_format($stats['customers_count'], 0, ',', ' ') }}</span>
                </span>
                <span class="text-lg text-indigo-500 transition group-hover:translate-x-1" aria-hidden="true">→</span>
            </a>
        @endcan
        @can('products.view')
            <a href="{{ route('products.index', ['stock' => 'out']) }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-rose-200 hover:shadow">
                <span>
                    <span class="block text-xs font-medium text-slate-500">Parfums en rupture</span>
                    <span class="mt-1 block text-lg font-bold text-rose-600">{{ number_format($stats['out_of_stock'], 0, ',', ' ') }}</span>
                </span>
                <span class="text-lg text-rose-500 transition group-hover:translate-x-1" aria-hidden="true">→</span>
            </a>
            <a href="{{ route('products.index', ['stock' => 'low']) }}" class="group flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-amber-200 hover:shadow">
                <span>
                    <span class="block text-xs font-medium text-slate-500">Stocks faibles</span>
                    <span class="mt-1 block text-lg font-bold text-amber-600">{{ number_format($stats['low_stock'], 0, ',', ' ') }}</span>
                </span>
                <span class="text-lg text-amber-500 transition group-hover:translate-x-1" aria-hidden="true">→</span>
            </a>
        @endcan
    </section>

    @if($expiringBatches->isNotEmpty())
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-amber-950">Lots à surveiller</h3>
                    <p class="text-sm text-amber-800">Lots expirés ou à expiration dans les 90 prochains jours.</p>
                </div>
                @can('stock.view')
                    <a href="{{ route('stock.lots') }}" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-amber-900 shadow-sm transition hover:bg-amber-100">Voir tous les lots</a>
                @endcan
            </div>
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($expiringBatches as $batch)
                    <a href="{{ $batch->product ? route('products.show', $batch->product) : route('stock.lots') }}" class="rounded-xl border border-amber-200 bg-white p-3 transition hover:-translate-y-0.5 hover:shadow-sm">
                        <span class="block truncate text-sm font-semibold text-slate-800">{{ $batch->product?->name ?? 'Produit supprimé' }}</span>
                        <span class="mt-1 block text-xs text-slate-500">Lot {{ $batch->lot_number }} · {{ $batch->quantity }} restant(s)</span>
                        <span class="mt-1 block text-xs font-medium {{ $batch->expires_at->lt(today()) ? 'text-rose-700' : 'text-amber-700' }}">{{ $batch->expires_at->lt(today()) ? 'Expiré' : 'Expire le' }} {{ $batch->expires_at->format('d/m/Y') }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 sm:p-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">Évolution des ventes</h3>
                    <p class="mt-1 text-sm text-slate-500">Chiffre d’affaires journalier</p>
                </div>
                <nav aria-label="Période du graphique" class="flex w-fit rounded-lg bg-slate-100 p-1">
                    @foreach([7 => '7 jours', 30 => '30 jours', 90 => '90 jours'] as $period => $label)
                        <a href="{{ route('dashboard', ['days' => $period]) }}"
                           @class([
                               'rounded-md px-3 py-1.5 text-xs font-medium transition',
                               'bg-white text-indigo-700 shadow-sm' => $days === $period,
                               'text-slate-500 hover:text-slate-800' => $days !== $period,
                           ])
                           @if($days === $period) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="relative h-72">
                <canvas id="salesChart" class="block h-full w-full" data-local-chart data-chart-type="line"
                        data-chart-labels="{{ json_encode($chartLabels) }}"
                        data-chart-datasets="{{ json_encode([['label' => 'Chiffre d’affaires', 'values' => $chartData, 'color' => '#6366f1']]) }}"
                        role="img" aria-label="Graphique des ventes pour les {{ $days }} derniers jours"></canvas>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">À réapprovisionner</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $stats['out_of_stock'] }} rupture(s) · {{ $stats['low_stock'] }} stock(s) faible(s)</p>
                </div>
                @can('products.view')
                    <a href="{{ route('products.index', ['stock' => 'low']) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Tout voir</a>
                @endcan
            </div>
            <div class="mt-4 space-y-2">
                @forelse($lowStockProducts as $product)
                    <a @can('products.view') href="{{ route('products.show', $product) }}" @endcan
                       class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-slate-50">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-700">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-slate-800">{{ $product->name }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ $product->brand?->name ?? 'Sans marque' }} · seuil {{ $product->alert_threshold }}</span>
                        </span>
                        <span @class([
                            'rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-rose-100 text-rose-700' => $product->stock_quantity <= 0,
                            'bg-amber-100 text-amber-700' => $product->stock_quantity > 0,
                        ])>{{ $product->stock_quantity }} restant(s)</span>
                    </a>
                @empty
                    <div class="rounded-xl bg-emerald-50 px-4 py-8 text-center">
                        <p class="text-2xl" aria-hidden="true">✓</p>
                        <p class="mt-2 text-sm font-medium text-emerald-800">Tous les stocks sont au-dessus du seuil.</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">Meilleures ventes</h3>
                    <p class="mt-1 text-sm text-slate-500">Sur les {{ $days }} derniers jours</p>
                </div>
                <span class="text-xl" aria-hidden="true">🏆</span>
            </div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($topProducts as $product)
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $product->product_name }}</p>
                            <p class="text-xs text-slate-500">{{ $product->quantity }} vendu(s)</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-slate-700">{{ money($product->revenue) }}</p>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">Les meilleures ventes apparaîtront ici.</p>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h3 class="font-semibold text-slate-900">Dernières ventes</h3>
                    <p class="mt-1 text-sm text-slate-500">Les transactions récentes de la boutique</p>
                </div>
                @can('sales.view')
                    <a href="{{ route('sales.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Historique →</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium sm:px-6">Référence</th>
                            <th class="px-5 py-3 font-medium">Client</th>
                            <th class="px-5 py-3 text-right font-medium">Total</th>
                            <th class="hidden px-5 py-3 text-right font-medium md:table-cell">Heure</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSales as $sale)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 sm:px-6">
                                    @can('sales.view')
                                        <a href="{{ route('sales.show', $sale) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $sale->reference }}</a>
                                    @else
                                        <span class="font-semibold text-slate-700">{{ $sale->reference }}</span>
                                    @endcan
                                </td>
                                <td class="max-w-40 truncate px-5 py-3 text-slate-600">{{ $sale->customer?->name ?? 'Passage' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-slate-800">{{ money($sale->total) }}</td>
                                <td class="hidden whitespace-nowrap px-5 py-3 text-right text-slate-500 md:table-cell">{{ company_date($sale->sold_at, true) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Aucune vente enregistrée pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <details class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <summary class="cursor-pointer list-none px-5 py-4 font-semibold text-slate-800 marker:hidden">
            <span class="flex items-center justify-between">Activité récente du stock <span class="text-xs font-normal text-slate-500">Afficher / masquer</span></span>
        </summary>
        <div class="divide-y divide-slate-100 border-t border-slate-100 px-5">
            @forelse($recentMovements as $movement)
                <div class="flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                    <div>
                        <p class="font-medium text-slate-800">{{ $movement->product?->name ?? 'Produit supprimé' }}</p>
                        <p class="text-xs text-slate-500">{{ $movement->reason ?: $movement->type_label }} · {{ company_date($movement->movement_date) }}</p>
                    </div>
                    <span @class([
                        'rounded-full px-2.5 py-1 text-xs font-semibold',
                        'bg-emerald-100 text-emerald-700' => $movement->type === 'in',
                        'bg-rose-100 text-rose-700' => $movement->type === 'out',
                        'bg-amber-100 text-amber-700' => $movement->type === 'adjustment',
                    ])>{{ $movement->type_label }} · {{ $movement->quantity }}</span>
                </div>
            @empty
                <p class="py-6 text-sm text-slate-500">Aucun mouvement de stock enregistré.</p>
            @endforelse
        </div>
    </details>
</div>
@endsection
