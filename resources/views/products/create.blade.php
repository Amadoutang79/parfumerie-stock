@extends('layouts.app')
@section('title', 'Nouveau parfum')

@section('content')
<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data"
      class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        <h3 class="font-semibold text-slate-800">Informations produit</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Nom du parfum *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="text-sm font-medium">Référence *</label>
                <input type="text" name="reference" value="{{ old('reference', $reference) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Code-barres</label>
                <input type="text" name="barcode" value="{{ old('barcode') }}"
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Marque</label>
                <select name="brand_id" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">—</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" @selected(old('brand_id')==$b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Catégorie</label>
                <select name="category_id" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">—</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Genre *</label>
                <select name="gender" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="unisexe" @selected(old('gender')=='unisexe')>Unisexe</option>
                    <option value="homme"   @selected(old('gender')=='homme')>Homme</option>
                    <option value="femme"   @selected(old('gender')=='femme')>Femme</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Contenance (ml) *</label>
                <input type="number" name="volume" value="{{ old('volume', 50) }}" required min="1"
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Concentration</label>
                <select name="concentration" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">Non précisée</option>
                    @foreach(\App\Models\Product::CONCENTRATIONS as $code => $label)
                        <option value="{{ $code }}" @selected(old('concentration') === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <h3 class="font-semibold text-slate-800 pt-2">Prix</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Prix d'achat *</label>
                <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', 0) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Prix de vente *</label>
                <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', 0) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Prix promo</label>
                <input type="number" step="0.01" name="promo_price" value="{{ old('promo_price') }}"
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
        </div>

        <h3 class="font-semibold text-slate-800 pt-2">Stock</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Quantité en stock *</label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required min="0"
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Seuil d'alerte *</label>
                <input type="number" name="alert_threshold" value="{{ old('alert_threshold', 5) }}" required min="0"
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="3" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('description') }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 h-fit">
        <h3 class="font-semibold text-slate-800">Image</h3>
        <input type="file" name="image" accept="image/*"
               class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
            Produit actif
        </label>

        <div class="pt-4 flex gap-2">
            <button class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                Enregistrer
            </button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-slate-100 rounded-lg text-sm">Annuler</a>
        </div>
    </div>
</form>
@endsection