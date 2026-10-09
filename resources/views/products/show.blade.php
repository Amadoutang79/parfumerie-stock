@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <img src="{{ $product->image_url }}" class="w-full aspect-square rounded-xl object-cover mb-4">
        <h2 class="text-xl font-bold text-slate-800">{{ $product->name }}</h2>
        <p class="text-sm text-slate-500">{{ $product->brand?->name }} · {{ $product->category?->name }}</p>
        <div class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Référence</span><span class="font-medium">{{ $product->reference }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Genre</span><span>{{ $product->gender_label }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Contenance</span><span>{{ $product->volume }} ml</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Concentration</span><span>{{ $product->concentration_label }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Prix achat</span><span>{{ money($product->purchase_price) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Prix vente</span><span class="font-semibold">{{ money($product->selling_price) }}</span></div>
            @can('reports.view')
                <div class="flex justify-between"><span class="text-slate-500">Marge actuelle</span><span class="font-semibold {{ $product->margin >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ money($product->margin) }} ({{ number_format($product->margin_percent, 1, ',', ' ') }} %)</span></div>
            @endcan
            @if($product->promo_price)
                <div class="flex justify-between"><span class="text-slate-500">Promo</span><span class="text-rose-600 font-semibold">{{ money($product->promo_price) }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-slate-500">Stock</span>
                <span class="font-semibold @if($product->stock_status=='ok') text-emerald-600 @elseif($product->stock_status=='faible') text-amber-600 @else text-rose-600 @endif">
                    {{ $product->stock_quantity }}
                </span>
            </div>
        </div>
        @can('products.edit')
            <a href="{{ route('products.edit', $product) }}" class="mt-6 block text-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">Modifier</a>
        @endcan
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        @can('stock.view')
        <div class="border-b border-slate-200 p-5">
            <h3 class="font-semibold">Lots disponibles</h3>
            <p class="mt-1 text-sm text-slate-500">Les lots les plus proches de leur date limite sont proposés en premier à la vente.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Lot</th><th class="py-2 text-center">Disponible</th><th class="py-2">Expiration</th><th class="py-2 text-right">Coût unitaire</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($product->batches as $batch)
                            <tr>
                                <td class="py-2 font-medium">{{ $batch->lot_number }}</td>
                                <td class="py-2 text-center">{{ $batch->quantity }}</td>
                                <td class="py-2 @if($batch->expires_at?->lt(today())) text-rose-700 @else text-slate-600 @endif">{{ $batch->expires_at?->format('d/m/Y') ?? 'Non renseignée' }}</td>
                                <td class="py-2 text-right">{{ money($batch->unit_cost) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-5 text-center text-slate-500">Aucun lot en stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endcan
        <div class="px-5 py-4 border-b border-slate-200 font-semibold">Historique des mouvements</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-center">Qté</th>
                    <th class="px-4 py-3 text-center">Stock</th>
                    <th class="px-4 py-3 text-left">Motif</th>
                    <th class="px-4 py-3 text-left">Utilisateur</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($movements as $m)
                    <tr>
                        <td class="px-4 py-3">{{ company_date($m->movement_date) }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs {{ $m->type_color }}">{{ $m->type_label }}</span></td>
                        <td class="px-4 py-3 text-center font-medium">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $m->stock_before }} → {{ $m->stock_after }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->reason ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $m->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucun mouvement</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $movements->links() }}</div>
    </div>
</div>
@endsection