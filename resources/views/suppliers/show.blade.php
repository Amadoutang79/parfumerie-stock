@extends('layouts.app')
@section('title', $supplier->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-3 text-sm">
        <h2 class="font-bold text-lg">{{ $supplier->name }}</h2>
        <div><span class="text-slate-500">Téléphone :</span> {{ $supplier->phone ?? '—' }}</div>
        <div><span class="text-slate-500">WhatsApp :</span>
            @if($supplier->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/','',$supplier->whatsapp) }}" target="_blank" class="text-emerald-600">💬 {{ $supplier->whatsapp }}</a>
            @else — @endif
        </div>
        <div><span class="text-slate-500">Email :</span> {{ $supplier->email ?? '—' }}</div>
        <div><span class="text-slate-500">Adresse :</span> {{ $supplier->address ?? '—' }}</div>
        @if($supplier->notes) <div class="pt-2 border-t"><span class="text-slate-500">Notes :</span><p>{{ $supplier->notes }}</p></div> @endif
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b font-semibold">Derniers achats</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3 text-left">Réf.</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-right">Total</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($supplier->purchases as $p)
                    <tr><td class="px-4 py-3"><a href="{{ route('purchases.show', $p) }}" class="text-indigo-600">{{ $p->reference }}</a></td>
                        <td class="px-4 py-3">{{ company_date($p->purchased_at) }}</td>
                        <td class="px-4 py-3 text-right">{{ money($p->total) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucun achat</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection