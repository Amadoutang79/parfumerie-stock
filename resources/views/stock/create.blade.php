@extends('layouts.app')
@section('title', 'Nouveau mouvement de stock')

@section('content')
<form method="POST" action="{{ route('stock.store') }}" x-data="{ type: @js(old('type', $type)) }" class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6 space-y-5">
    @csrf
    <h3 class="font-semibold text-slate-800">Enregistrer un mouvement</h3>

    <div>
        <label class="text-sm font-medium">Type *</label>
        <div class="mt-2 grid grid-cols-3 gap-2">
            @foreach(['in'=>'Entrée', 'out'=>'Sortie', 'adjustment'=>'Ajustement'] as $val => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="{{ $val }}" x-model="type" @checked(old('type', $type)==$val) class="peer sr-only" required>
                    <div class="px-3 py-2 text-center text-sm rounded-lg border border-slate-300 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">{{ $label }}</div>
                </label>
            @endforeach
        </div>
    </div>

    <div x-show="type === 'in'" x-cloak class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4">
        <label class="text-sm font-medium">N° de lot
            <input type="text" name="lot_number" value="{{ old('lot_number') }}" maxlength="100" placeholder="Ex. LOT-2026-001"
                   class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        </label>
        <label class="text-sm font-medium">Date d’expiration
            <input type="date" name="expires_at" value="{{ old('expires_at') }}" min="{{ now()->toDateString() }}"
                   class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        </label>
        <p class="col-span-2 text-xs font-normal text-slate-500">Ces informations seront visibles dans le suivi des lots et prises en compte lors des ventes.</p>
    </div>

    <div>
        <label class="text-sm font-medium">Produit *</label>
        <select name="product_id" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            <option value="">— Sélectionner —</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" @selected(old('product_id')==$p->id)>
                    {{ $p->name }} ({{ $p->reference }}) — stock: {{ $p->stock_quantity }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium">Quantité *</label>
            <input type="number" name="quantity" value="{{ old('quantity') }}" required min="0"
                   class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            <p class="text-xs text-slate-500 mt-1">Pour un ajustement : indiquez le nouveau stock.</p>
        </div>
        <div>
            <label class="text-sm font-medium">Prix unitaire</label>
            <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price') }}"
                   class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium">Date *</label>
            <input type="date" name="movement_date" value="{{ old('movement_date', now()->toDateString()) }}" required
                   class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">Référence</label>
            <input type="text" name="reference" value="{{ old('reference') }}"
                   class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
        </div>
    </div>

    <div>
        <label class="text-sm font-medium">Motif</label>
        <input type="text" name="reason" value="{{ old('reason') }}" placeholder="Ex: Achat fournisseur, Casse..."
               class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
    </div>

    <div class="flex gap-2 pt-2">
        <button class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Enregistrer</button>
        <a href="{{ route('stock.index') }}" class="px-5 py-2 bg-slate-100 rounded-lg text-sm">Annuler</a>
    </div>
</form>
@endsection