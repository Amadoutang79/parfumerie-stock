@extends('layouts.app')
@section('title', $customer->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-3 text-sm">
        <h2 class="font-bold text-lg">{{ $customer->name }}</h2>
        <div><span class="text-slate-500">Téléphone :</span> {{ $customer->phone ?? '—' }}</div>
        <div><span class="text-slate-500">Email :</span> {{ $customer->email ?? '—' }}</div>
        <div><span class="text-slate-500">Adresse :</span> {{ $customer->address ?? '—' }}</div>
        <div class="pt-3 border-t">
            <div class="text-xs text-slate-500">Total dépensé</div>
            <div class="text-2xl font-bold text-indigo-600">{{ money($customer->total_spent) }}</div>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b font-semibold">Historique des achats</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3 text-left">Réf.</th><th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Paiement</th><th class="px-4 py-3 text-right">Total</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($customer->sales as $s)
                    <tr><td class="px-4 py-3"><a href="{{ route('sales.show', $s) }}" class="text-indigo-600">{{ $s->reference }}</a></td>
                        <td class="px-4 py-3">{{ company_date($s->sold_at, true) }}</td>
                        <td class="px-4 py-3">{{ $s->payment_label }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ money($s->total) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune vente</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection