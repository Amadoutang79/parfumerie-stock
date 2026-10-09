@extends('layouts.app')
@section('title', 'Achats fournisseurs')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-5 rounded-2xl bg-gradient-to-r from-sky-900 via-cyan-800 to-teal-700 p-6 text-white shadow-lg md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm text-sky-100">Opérations · Approvisionnement</p><h2 class="mt-1 text-2xl font-bold">Vos achats et réceptions</h2><p class="mt-2 max-w-xl text-sm text-sky-50/90">Suivez les commandes fournisseurs et les montants réglés.</p></div>
        @can('purchases.create')<a href="{{ route('purchases.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-cyan-900 shadow transition hover:-translate-y-0.5">＋ Enregistrer un achat</a>@endcan
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['Achats enregistrés', number_format($stats['count'], 0, ',', ' '), '▤', 'sky'],
            ['Montant des résultats', money($stats['total']), '↙', 'indigo'],
            ['Reste à payer', money($stats['outstanding']), '◷', 'amber'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md" style="--enter-delay: {{ $loop->index * 60 }}ms"><div class="flex items-center justify-between gap-3"><div><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 break-words text-xl font-bold text-slate-900">{{ $value }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $tone }}-50 text-lg">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <div class="mb-4 flex items-center justify-between"><div><h3 class="font-semibold text-slate-900">Historique des achats</h3><p class="text-sm text-slate-500">{{ $purchases->total() }} résultat(s) · {{ money($stats['paid']) }} payé(s)</p></div>@if(collect($filters)->filter()->isNotEmpty())<a href="{{ route('purchases.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Effacer les filtres</a>@endif</div>
            <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Référence ou fournisseur…" aria-label="Rechercher un achat" class="rounded-lg border-slate-300 text-sm sm:col-span-2">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="Date de début" class="rounded-lg border-slate-300 text-sm">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="Date de fin" class="rounded-lg border-slate-300 text-sm">
                <div class="flex gap-2 sm:col-span-2 xl:col-span-4"><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrer les achats</button><a href="{{ route('purchases.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-600 hover:bg-slate-200">Réinitialiser</a></div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Achat</th><th class="px-5 py-3 font-medium">Fournisseur</th><th class="px-5 py-3 font-medium">Enregistré par</th><th class="px-5 py-3 text-right font-medium">Total · payé</th><th class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $purchase)
                        <tr class="transition hover:bg-sky-50/30">
                            <td class="whitespace-nowrap px-5 py-4"><a href="{{ route('purchases.show', $purchase) }}" class="font-semibold text-indigo-700 hover:text-indigo-900">{{ $purchase->reference }}</a><span class="block text-xs text-slate-500">{{ company_date($purchase->purchased_at) }}</span></td>
                            <td class="px-5 py-4 font-medium text-slate-700">{{ $purchase->supplier?->name ?? 'Fournisseur non précisé' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $purchase->user?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right"><span class="font-bold text-slate-900">{{ money($purchase->total) }}</span><span class="block text-xs text-slate-500">Payé {{ money($purchase->paid_amount) }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('purchases.show', $purchase) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">Détails</a>@can('purchases.delete')<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="ml-1 inline" onsubmit="return confirm('Annuler cet achat ? Les quantités seront retirées du stock.')">@csrf @method('DELETE')<button class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Annuler</button></form>@endcan</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center"><div class="text-3xl">🚚</div><p class="mt-2 font-semibold text-slate-800">Aucun achat trouvé</p><p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou enregistrez une réception fournisseur.</p>@can('purchases.create')<a href="{{ route('purchases.create') }}" class="mt-4 inline-flex rounded-lg bg-cyan-800 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-900">Enregistrer un achat</a>@endcan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">{{ $purchases->links() }}</div>
    </section>
</div>
@endsection
