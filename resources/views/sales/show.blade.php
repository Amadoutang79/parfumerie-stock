@extends('layouts.app')
@section('title', 'Vente '.$sale->reference)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold">Vente {{ $sale->reference }}</h2>
            <p class="text-sm text-slate-500">{{ company_date($sale->sold_at, true) }}</p>
        </div>
        <a href="{{ route('sales.ticket', $sale) }}" target="_blank" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm">🖨️ Ticket</a>
    </div>

    <div class="px-6 py-4 grid grid-cols-2 gap-4 text-sm border-b border-slate-200">
        <div>
            <div class="text-xs text-slate-500">Client</div>
            <div class="font-medium">{{ $sale->customer?->name ?? 'Passage' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Vendeur</div>
            <div class="font-medium">{{ $sale->user?->name }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Paiement</div>
            <div class="font-medium">{{ $sale->payment_label }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Statut</div>
            <div class="font-medium text-emerald-600">Payée</div>
        </div>
    </div>

    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-6 py-3 text-left">Produit</th>
                <th class="px-6 py-3 text-left">Lot(s)</th>
                <th class="px-6 py-3 text-center">Qté</th>
                <th class="px-6 py-3 text-right">PU</th>
                <th class="px-6 py-3 text-right">Sous-total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($sale->items as $i)
                <tr>
                    <td class="px-6 py-3">{{ $i->product_name }}</td>
                    <td class="px-6 py-3">
                        @forelse($i->batches as $batch)
                            <span class="block">{{ $batch->lot_number }} · {{ $batch->pivot->quantity }}</span>
                        @empty
                            <span class="text-slate-400">—</span>
                        @endforelse
                    </td>
                    <td class="px-6 py-3 text-center">{{ $i->quantity }}</td>
                    <td class="px-6 py-3 text-right">{{ money($i->price) }}</td>
                    <td class="px-6 py-3 text-right font-medium">{{ money($i->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="px-6 py-4 border-t border-slate-200 space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">Sous-total</span><span>{{ money($sale->subtotal) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Remise</span><span class="text-rose-600">- {{ money($sale->discount) }}</span></div>
        @if($sale->vat_rate > 0)
            <div class="flex justify-between"><span class="text-slate-500">TVA ({{ rtrim(rtrim(number_format((float) $sale->vat_rate, 2, '.', ''), '0'), '.') }} %)</span><span>{{ money($sale->vat_amount) }}</span></div>
        @endif
        <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-100"><span>Total</span><span class="text-indigo-600">{{ money($sale->total) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Payé</span><span>{{ money($sale->paid_amount) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Rendu</span><span>{{ money($sale->change_amount) }}</span></div>
    </div>
</div>
@endsection