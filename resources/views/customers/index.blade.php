@extends('layouts.app')
@section('title', 'Clients')

@section('content')
<div class="space-y-6">
    <section class="animate-enter flex flex-col gap-5 rounded-2xl bg-gradient-to-r from-rose-900 via-pink-800 to-fuchsia-700 p-6 text-white shadow-lg md:flex-row md:items-center md:justify-between">
        <div><p class="text-sm text-pink-100">Relation client · Fidélité</p><h2 class="mt-1 text-2xl font-bold">Mieux connaître vos clients</h2><p class="mt-2 max-w-xl text-sm text-pink-50/90">Retrouvez leur historique, leur fidélité et contactez-les en un geste.</p></div>
        @can('customers.manage')<a href="#ajouter-client" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rose-900 shadow transition hover:-translate-y-0.5">＋ Ajouter un client</a>@endcan
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['Clients enregistrés', number_format($stats['total'], 0, ',', ' '), '👥', 'rose'],
            ['Ventes réalisées', number_format($stats['sales'], 0, ',', ' '), '🛍', 'indigo'],
            ['Chiffre d’affaires', money($stats['revenue']), '↗', 'emerald'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md" style="--enter-delay: {{ $loop->index * 60 }}ms"><div class="flex items-center justify-between gap-3"><div><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 break-words text-xl font-bold text-slate-900">{{ $value }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $tone }}-50 text-lg">{{ $icon }}</span></div></article>
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div><h3 class="font-semibold text-slate-900">Fichier clients</h3><p class="text-sm text-slate-500">{{ $customers->total() }} résultat(s)</p></div>
                <form method="GET" class="flex gap-2">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom, téléphone, e-mail…" aria-label="Rechercher un client" class="min-w-0 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Chercher</button>
                    @if(request('search'))<a href="{{ route('customers.index') }}" class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200">Effacer</a>@endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Client</th><th class="px-5 py-3 font-medium">Contact</th><th class="px-5 py-3 text-center font-medium">Visites</th><th class="px-5 py-3 text-right font-medium">Dépensé</th><th class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customers as $customer)
                            <tr class="transition hover:bg-rose-50/30">
                                <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 font-bold text-rose-700">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span><div><a href="{{ route('customers.show', $customer) }}" class="font-semibold text-slate-800 hover:text-indigo-700">{{ $customer->name }}</a><p class="max-w-48 truncate text-xs text-slate-500">{{ $customer->email ?: 'Client depuis la boutique' }}</p></div></div></td>
                                <td class="whitespace-nowrap px-5 py-4">@if($customer->phone)<div class="flex items-center gap-2"><a href="tel:{{ $customer->phone }}" class="text-slate-700 hover:text-indigo-700">{{ $customer->phone }}</a><a href="https://wa.me/{{ preg_replace('/\D/', '', $customer->phone) }}" target="_blank" rel="noopener noreferrer" aria-label="Contacter {{ $customer->name }} sur WhatsApp" class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">WhatsApp</a></div>@else<span class="text-slate-400">—</span>@endif</td>
                                <td class="px-5 py-4 text-center"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $customer->sales_count }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-slate-800">{{ money($customer->sales_sum_total ?? 0) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('customers.show', $customer) }}" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">Historique</a>@can('customers.manage')<form method="POST" action="{{ route('customers.destroy', $customer) }}" class="ml-1 inline" onsubmit="return confirm('Supprimer cette fiche client ?')">@csrf @method('DELETE')<button class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Supprimer</button></form>@endcan</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-14 text-center"><div class="text-3xl">👥</div><p class="mt-2 font-semibold text-slate-800">Aucun client trouvé</p><p class="mt-1 text-sm text-slate-500">Essayez une autre recherche ou ajoutez un client.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $customers->links() }}</div>
        </section>

        @can('customers.manage')
            <section id="ajouter-client" class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-xl">＋</div>
                <h3 class="font-semibold text-slate-900">Créer une fiche client</h3>
                <p class="mt-1 text-sm text-slate-500">Conservez les coordonnées utiles et retrouvez les ventes plus facilement.</p>
                <form method="POST" action="{{ route('customers.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <label class="block text-sm font-medium text-slate-700">Nom complet<input type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></label>
                    <label class="block text-sm font-medium text-slate-700">Téléphone<input type="tel" name="phone" value="{{ old('phone') }}" maxlength="50" autocomplete="tel" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></label>
                    <label class="block text-sm font-medium text-slate-700">E-mail<input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></label>
                    <label class="block text-sm font-medium text-slate-700">Adresse <span class="font-normal text-slate-400">(facultatif)</span><input type="text" name="address" value="{{ old('address') }}" maxlength="255" autocomplete="street-address" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></label>
                    <button class="w-full rounded-lg bg-gradient-to-r from-rose-600 to-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">Enregistrer le client</button>
                </form>
            </section>
        @endcan
    </div>
</div>
@endsection
