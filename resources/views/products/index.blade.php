@extends('layouts.app')
@section('title', 'Parfums')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-5 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-950 via-indigo-800 to-violet-700 p-6 text-white shadow-lg md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm text-indigo-200">Catalogue · Parfums</p>
            <h2 class="mt-1 text-2xl font-bold">Votre collection, en un regard</h2>
            <p class="mt-2 max-w-2xl text-sm text-indigo-100">Retrouvez rapidement chaque référence et surveillez son prix et son niveau de stock.</p>
        </div>
        @can('products.create')
            <a href="{{ route('products.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-800 shadow transition hover:-translate-y-0.5 hover:bg-indigo-50">＋ Ajouter un parfum</a>
        @endcan
    </section>

    <section aria-label="Résumé du catalogue" class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach([
            ['Références', $stats['total'], '🧴', 'indigo'],
            ['Produits actifs', $stats['active'], '✦', 'emerald'],
            ['Stock faible', $stats['low_stock'], '⚠', 'amber'],
            ['Valeur d’achat en stock', money($stats['stock_value']), '▦', 'violet'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md sm:p-5" style="--enter-delay: {{ $loop->index * 60 }}ms">
                <div class="flex items-center justify-between gap-2"><p class="text-xs font-medium text-slate-500 sm:text-sm">{{ $label }}</p><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-{{ $tone }}-50 text-lg">{{ $icon }}</span></div>
                <p class="mt-3 break-words text-xl font-bold text-slate-900 sm:text-2xl">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div><h3 class="font-semibold text-slate-900">Catalogue des parfums</h3><p class="text-sm text-slate-500">{{ $products->total() }} résultat(s)</p></div>
                @if(collect($filters)->filter()->isNotEmpty())
                    <a href="{{ route('products.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Effacer les filtres</a>
                @endif
            </div>
            <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
                <label class="sm:col-span-2 xl:col-span-2"><span class="sr-only">Rechercher dans le catalogue</span><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom, référence ou code-barres…" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></label>
                <label><span class="sr-only">Marque</span><select name="brand_id" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Toutes les marques</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(($filters['brand_id'] ?? '') == $brand->id)>{{ $brand->name }}</option>@endforeach</select></label>
                <label><span class="sr-only">Catégorie</span><select name="category_id" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Toutes les catégories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                <label><span class="sr-only">Genre</span><select name="gender" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Tous les genres</option>@foreach(['femme' => 'Femme', 'homme' => 'Homme', 'unisexe' => 'Unisexe'] as $key => $label)<option value="{{ $key }}" @selected(($filters['gender'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></label>
                <label><span class="sr-only">Concentration</span><select name="concentration" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Toutes concentrations</option>@foreach(\App\Models\Product::CONCENTRATIONS as $key => $label)<option value="{{ $key }}" @selected(($filters['concentration'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></label>
                <label><span class="sr-only">État du stock</span><select name="stock" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Tout le stock</option><option value="low" @selected(($filters['stock'] ?? '') === 'low')>Stock faible</option><option value="out" @selected(($filters['stock'] ?? '') === 'out')>Rupture</option></select></label>
                <div class="flex gap-2 sm:col-span-2 xl:col-span-6"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Appliquer les filtres</button><a href="{{ route('products.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200">Réinitialiser</a></div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Parfum</th><th class="px-5 py-3 font-medium">Famille</th><th class="px-5 py-3 font-medium">Genre · format</th><th class="px-5 py-3 text-right font-medium">Prix · marge</th><th class="px-5 py-3 text-center font-medium">Stock</th><th class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="transition hover:bg-indigo-50/30">
                            <td class="whitespace-nowrap px-5 py-3.5"><div class="flex items-center gap-3"><img src="{{ $product->image_url }}" alt="" loading="lazy" class="h-11 w-11 rounded-xl bg-slate-100 object-cover"><div class="min-w-0"><a href="{{ route('products.show', $product) }}" class="block max-w-56 truncate font-semibold text-slate-800 hover:text-indigo-600">{{ $product->name }}</a><span class="text-xs text-slate-500">{{ $product->reference }}@if($product->barcode) · {{ $product->barcode }}@endif</span></div></div></td>
                            <td class="px-5 py-3.5"><span class="font-medium text-slate-700">{{ $product->brand?->name ?? 'Sans marque' }}</span><span class="block text-xs text-slate-500">{{ $product->category?->name ?? 'Sans catégorie' }}</span></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-slate-600">{{ $product->gender_label }} · {{ $product->volume }} ml<span class="block text-xs text-slate-500">{{ $product->concentration_label }}</span></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">@if($product->promo_price && $product->promo_price > 0)<span class="block text-xs text-slate-400 line-through">{{ money($product->selling_price) }}</span><span class="font-bold text-rose-600">{{ money($product->promo_price) }}</span>@else<span class="font-semibold text-slate-800">{{ money($product->selling_price) }}</span>@endif @can('reports.view')<span class="block text-xs font-medium {{ $product->margin >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Marge {{ money($product->margin) }} · {{ number_format($product->margin_percent, 1, ',', ' ') }} %</span>@endcan</td>
                            <td class="px-5 py-3.5 text-center"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold','bg-emerald-50 text-emerald-700' => $product->stock_status === 'ok','bg-amber-50 text-amber-700' => $product->stock_status === 'faible','bg-rose-50 text-rose-700' => $product->stock_status === 'rupture'])>{{ $product->stock_quantity }} · {{ $product->stock_status }}</span></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right"><div class="inline-flex items-center gap-1"><a href="{{ route('products.show', $product) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">Voir</a>@can('products.edit')<a href="{{ route('products.edit', $product) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">Modifier</a>@endcan @can('products.delete')<form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Supprimer ce parfum ?')">@csrf @method('DELETE')<button class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Supprimer</button></form>@endcan</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center"><div class="text-3xl">🧴</div><p class="mt-2 font-semibold text-slate-800">Aucun parfum ne correspond</p><p class="mt-1 text-sm text-slate-500">Essayez de modifier vos filtres ou ajoutez une nouvelle référence.</p>@can('products.create')<a href="{{ route('products.create') }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Ajouter un parfum</a>@endcan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">{{ $products->links() }}</div>
    </section>
</div>
@endsection
