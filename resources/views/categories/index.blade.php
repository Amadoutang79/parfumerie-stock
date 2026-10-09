@extends('layouts.app')
@section('title', 'Catégories')

@section('content')
<div class="space-y-6">
    <section class="animate-enter rounded-2xl bg-gradient-to-r from-indigo-700 via-violet-700 to-fuchsia-700 p-6 text-white shadow-lg">
        <p class="text-sm text-white/75">Catalogue · Catégories</p>
        <h2 class="mt-1 text-2xl font-bold">Un catalogue facile à parcourir</h2>
        <p class="mt-2 max-w-2xl text-sm text-white/85">Classez vos parfums par famille olfactive, collection ou type pour les retrouver plus vite.</p>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([
            ['Catégories créées', $stats['total'], '▤', 'indigo'],
            ['Catégories utilisées', $stats['with_products'], '✦', 'fuchsia'],
            ['Parfums à classer', $stats['products'], '🧴', 'emerald'],
        ] as [$label, $value, $icon, $tone])
            <article class="animate-enter rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md" style="--enter-delay: {{ $loop->index * 70 }}ms">
                <div class="flex items-center justify-between"><div><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($value, 0, ',', ' ') }}</p></div><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-{{ $tone }}-50 text-xl">{{ $icon }}</span></div>
            </article>
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div><h3 class="font-semibold text-slate-900">Familles et collections</h3><p class="mt-1 text-sm text-slate-500">{{ $categories->total() }} résultat(s)</p></div>
                <form method="GET" class="flex gap-2">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Nom ou description…" aria-label="Rechercher une catégorie" class="min-w-0 rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Chercher</button>
                    @if(request('search'))<a href="{{ route('categories.index') }}" class="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200">Effacer</a>@endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-medium">Catégorie</th><th class="px-5 py-3 font-medium">Description</th><th class="px-5 py-3 text-center font-medium">Parfums</th><th class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $category)
                            <tr class="transition hover:bg-indigo-50/40">
                                <td class="whitespace-nowrap px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 font-bold text-indigo-700">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span><span class="font-semibold text-slate-800">{{ $category->name }}</span></div></td>
                                <td class="max-w-xs px-5 py-4 text-slate-500">{{ $category->description ?: 'Aucune description' }}</td>
                                <td class="px-5 py-4 text-center"><span class="rounded-full bg-fuchsia-50 px-2.5 py-1 text-xs font-semibold text-fuchsia-700">{{ $category->products_count }}</span></td>
                                <td class="px-5 py-4 text-right">
                                    @can('products.create')
                                        <details class="relative inline-block text-left">
                                            <summary class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">Modifier</summary>
                                            <form method="POST" action="{{ route('categories.update', $category) }}" class="absolute right-0 z-20 mt-2 w-72 space-y-3 rounded-xl border border-slate-200 bg-white p-4 text-left shadow-xl">
                                                @csrf @method('PUT')
                                                <label class="block text-xs font-medium text-slate-600">Nom<input required name="name" maxlength="255" value="{{ $category->name }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                                                <label class="block text-xs font-medium text-slate-600">Description<input name="description" maxlength="255" value="{{ $category->description }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                                                <button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Enregistrer</button>
                                            </form>
                                        </details>
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" class="ml-1 inline" onsubmit="return confirm('Supprimer cette catégorie ? Les parfums liés resteront au catalogue.')">
                                            @csrf @method('DELETE')
                                            <button aria-label="Supprimer {{ $category->name }}" class="rounded-lg px-2 py-1.5 text-rose-600 hover:bg-rose-50">Supprimer</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">Lecture seule</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-14 text-center"><div class="text-3xl">🗂️</div><p class="mt-2 font-semibold text-slate-700">Aucune catégorie trouvée</p><p class="mt-1 text-sm text-slate-500">Essayez une autre recherche ou créez votre première catégorie.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $categories->links() }}</div>
        </section>

        @can('products.create')
            <section class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-xl">＋</div>
                <h3 class="font-semibold text-slate-900">Créer une catégorie</h3>
                <p class="mt-1 text-sm text-slate-500">Ajoutez une famille ou une collection à votre catalogue.</p>
                <form method="POST" action="{{ route('categories.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <label class="block text-sm font-medium text-slate-700">Nom de la catégorie<input type="text" name="name" value="{{ old('name') }}" placeholder="Ex. Boisé, Floral, Orientale…" maxlength="255" required class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></label>
                    <label class="block text-sm font-medium text-slate-700">Description <span class="font-normal text-slate-400">(facultatif)</span><textarea name="description" rows="3" maxlength="255" placeholder="Décrivez brièvement cette catégorie…" class="mt-1.5 w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea></label>
                    <button class="w-full rounded-lg bg-gradient-to-r from-indigo-600 to-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">Créer la catégorie</button>
                </form>
            </section>
        @endcan
    </div>
</div>
@endsection
