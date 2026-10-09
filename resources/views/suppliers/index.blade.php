@extends('layouts.app')
@section('title', 'Fournisseurs')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-5 rounded-2xl bg-gradient-to-r from-slate-800 via-sky-900 to-cyan-800 p-6 text-white shadow-lg md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm text-sky-100">Partenaires · Approvisionnement</p><h2 class="mt-1 text-2xl font-bold">Le réseau de vos fournisseurs</h2><p class="mt-2 max-w-xl text-sm text-sky-50/90">Retrouvez les contacts, ouvrez WhatsApp et consultez l’historique des commandes.</p></div>
        @can('suppliers.manage')<a href="{{ route('suppliers.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-sky-900 shadow transition hover:-translate-y-0.5">＋ Ajouter un fournisseur</a>@endcan
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['Fournisseurs référencés', number_format($stats['total'], 0, ',', ' '), '🏭', 'sky'],
            ['Comptes actifs', number_format($stats['active'], 0, ',', ' '), '✓', 'emerald'],
            ['Achats cumulés', money($stats['purchases']), '↙', 'indigo'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md" style="--enter-delay: {{ $loop->index * 60 }}ms"><div class="flex items-center justify-between gap-3"><div><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 break-words text-xl font-bold text-slate-900">{{ $value }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $tone }}-50 text-lg">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div><h3 class="font-semibold text-slate-900">Répertoire fournisseurs</h3><p class="text-sm text-slate-500">{{ $suppliers->total() }} résultat(s)</p></div>
            <form method="GET" class="flex gap-2">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom, téléphone, e-mail…" aria-label="Rechercher un fournisseur" class="min-w-0 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Chercher</button>
                @if(request('search'))<a href="{{ route('suppliers.index') }}" class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200">Effacer</a>@endif
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Fournisseur</th><th class="px-5 py-3 font-medium">Contact</th><th class="px-5 py-3 text-center font-medium">Achats</th><th class="px-5 py-3 text-right font-medium">Montant commandé</th><th class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                        <tr class="transition hover:bg-sky-50/30">
                            <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 font-bold text-sky-700">{{ mb_strtoupper(mb_substr($supplier->name, 0, 1)) }}</span><div><a href="{{ route('suppliers.show', $supplier) }}" class="font-semibold text-slate-800 hover:text-indigo-700">{{ $supplier->name }}</a><p class="max-w-48 truncate text-xs text-slate-500">{{ $supplier->address ?: $supplier->email ?: 'Coordonnées à compléter' }}</p></div></div></td>
                            <td class="px-5 py-4"><div class="flex flex-wrap gap-2">@if($supplier->phone)<a href="tel:{{ $supplier->phone }}" class="text-slate-600 hover:text-indigo-700">{{ $supplier->phone }}</a>@endif @if($supplier->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $supplier->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">WhatsApp</a>@endif @if(!$supplier->phone && !$supplier->whatsapp)<span class="text-slate-400">—</span>@endif</div></td>
                            <td class="px-5 py-4 text-center"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $supplier->purchases_count }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-slate-800">{{ money($supplier->purchases_sum_total ?? 0) }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">@can('suppliers.manage')<a href="{{ route('suppliers.edit', $supplier) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">Modifier</a><form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="ml-1 inline" onsubmit="return confirm('Supprimer ce fournisseur ?')">@csrf @method('DELETE')<button class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Supprimer</button></form>@else<a href="{{ route('suppliers.show', $supplier) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">Détails</a>@endcan</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center"><div class="text-3xl">🏭</div><p class="mt-2 font-semibold text-slate-800">Aucun fournisseur trouvé</p><p class="mt-1 text-sm text-slate-500">Modifiez votre recherche ou ajoutez un partenaire.</p>@can('suppliers.manage')<a href="{{ route('suppliers.create') }}" class="mt-4 inline-flex rounded-lg bg-sky-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-900">Ajouter un fournisseur</a>@endcan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">{{ $suppliers->links() }}</div>
    </section>
</div>
@endsection
