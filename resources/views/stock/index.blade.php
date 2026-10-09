@extends('layouts.app')
@section('title', 'Mouvements de stock')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-5 rounded-2xl bg-gradient-to-r from-emerald-800 via-teal-700 to-cyan-700 p-6 text-white shadow-lg md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm text-emerald-100">Opérations · Inventaire</p><h2 class="mt-1 text-2xl font-bold">Un stock sous contrôle</h2><p class="mt-2 max-w-xl text-sm text-emerald-50/90">Suivez chaque entrée, sortie et correction avec son motif et son responsable.</p></div>
        @can('stock.adjust')
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('stock.create', ['type' => 'in']) }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 shadow transition hover:-translate-y-0.5">＋ Entrée</a>
                <a href="{{ route('stock.create', ['type' => 'out']) }}" class="rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">− Sortie</a>
                <a href="{{ route('stock.create', ['type' => 'adjustment']) }}" class="rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">⚖ Ajuster</a>
            </div>
        @endcan
    </section>

    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach([
            ['Mouvements enregistrés', $stats['total'], '▤', 'slate'],
            ['Entrées', $stats['entries'], '↙', 'emerald'],
            ['Sorties', $stats['exits'], '↗', 'rose'],
            ['Ajustements', $stats['adjustments'], '⚖', 'amber'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md sm:p-5" style="--enter-delay: {{ $loop->index * 60 }}ms">
                <div class="flex items-center justify-between"><p class="text-xs font-medium text-slate-500 sm:text-sm">{{ $label }}</p><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-{{ $tone }}-50 text-lg">{{ $icon }}</span></div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($value, 0, ',', ' ') }}</p>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><div><h3 class="font-semibold text-slate-900">Journal des mouvements</h3><p class="text-sm text-slate-500">{{ $movements->total() }} résultat(s)</p></div><div class="flex items-center gap-3"><a href="{{ route('stock.lots') }}" class="text-sm font-medium text-amber-700 hover:text-amber-900">Suivi des lots</a>@if(collect($filters)->filter()->isNotEmpty())<a href="{{ route('stock.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Effacer les filtres</a>@endif</div></div>
            <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Produit, référence, motif…" aria-label="Rechercher un mouvement" class="rounded-lg border-slate-300 text-sm sm:col-span-2">
                <select name="product_id" aria-label="Produit" class="rounded-lg border-slate-300 text-sm"><option value="">Tous les parfums</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>{{ $product->name }}</option>@endforeach</select>
                <select name="type" aria-label="Type de mouvement" class="rounded-lg border-slate-300 text-sm"><option value="">Tous les types</option><option value="in" @selected(($filters['type'] ?? '') === 'in')>Entrées</option><option value="out" @selected(($filters['type'] ?? '') === 'out')>Sorties</option><option value="adjustment" @selected(($filters['type'] ?? '') === 'adjustment')>Ajustements</option></select>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="Date de début" class="rounded-lg border-slate-300 text-sm">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="Date de fin" class="rounded-lg border-slate-300 text-sm">
                <div class="flex gap-2 sm:col-span-2 xl:col-span-6"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrer les mouvements</button><a href="{{ route('stock.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-600 hover:bg-slate-200">Réinitialiser</a></div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Date</th><th class="px-5 py-3 font-medium">Parfum</th><th class="px-5 py-3 font-medium">Mouvement</th><th class="px-5 py-3 text-center font-medium">Quantité</th><th class="px-5 py-3 text-center font-medium">Avant → après</th><th class="px-5 py-3 font-medium">Motif · Responsable</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $movement)
                        <tr class="transition hover:bg-emerald-50/30">
                            <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ company_date($movement->movement_date) }}</td>
                            <td class="px-5 py-4">@if($movement->product)<a href="{{ route('products.show', $movement->product) }}" class="font-semibold text-slate-800 hover:text-indigo-600">{{ $movement->product->name }}</a><span class="block text-xs text-slate-500">{{ $movement->product->reference }}</span>@else<span class="text-slate-400">Produit supprimé</span>@endif</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $movement->type_color }}">{{ $movement->type_label }}</span></td>
                            <td class="px-5 py-4 text-center font-bold {{ $movement->type === 'in' ? 'text-emerald-700' : ($movement->type === 'out' ? 'text-rose-700' : 'text-amber-700') }}">{{ $movement->quantity > 0 && $movement->type !== 'adjustment' ? '+' : '' }}{{ $movement->quantity }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-center text-slate-600">{{ $movement->stock_before }} <span class="text-slate-400">→</span> <span class="font-semibold">{{ $movement->stock_after }}</span></td>
                            <td class="px-5 py-4"><p class="max-w-56 truncate font-medium text-slate-700">{{ $movement->reason ?: 'Sans motif' }}</p><p class="text-xs text-slate-500">{{ $movement->user?->name ?? '—' }}@if($movement->reference) · {{ $movement->reference }}@endif</p></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center"><div class="text-3xl">📦</div><p class="mt-2 font-semibold text-slate-800">Aucun mouvement trouvé</p><p class="mt-1 text-sm text-slate-500">Essayez d’effacer les filtres ou enregistrez une entrée.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">{{ $movements->links() }}</div>
    </section>
</div>
@endsection
