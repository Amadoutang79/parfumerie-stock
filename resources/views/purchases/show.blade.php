@extends('layouts.app')
@section('title', 'Achat '.$purchase->reference)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200">
        <h2 class="text-lg font-bold">Achat {{ $purchase->reference }}</h2>
        <p class="text-sm text-slate-500">{{ company_date($purchase->purchased_at) }} · {{ $purchase->supplier?->name }}</p>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-6 py-3 text-left">Produit</th>
                <th class="px-6 py-3 text-left">Lot · expiration</th>
                <th class="px-6 py-3 text-center">Qté</th>
                <th class="px-6 py-3 text-right">Prix achat</th>
                <th class="px-6 py-3 text-right">Sous-total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($purchase->items as $i)
                <tr>
                    <td class="px-6 py-3">{{ $i->product?->name ?? '—' }}</td>
                    <td class="px-6 py-3">{{ $i->batch?->lot_number ?? '—' }}<span class="block text-xs text-slate-500">{{ $i->batch?->expires_at?->format('d/m/Y') ?? 'Sans date' }}</span></td>
                    <td class="px-6 py-3 text-center">{{ $i->quantity }}</td>
                    <td class="px-6 py-3 text-right">{{ money($i->purchase_price) }}</td>
                    <td class="px-6 py-3 text-right font-medium">{{ money($i->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-slate-200 space-y-2 text-sm">
        <div class="flex justify-between text-lg font-bold"><span>Total</span><span class="text-indigo-600">{{ money($purchase->total) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Payé</span><span>{{ money($purchase->paid_amount) }}</span></div>
    </div>
</div>
@endsection