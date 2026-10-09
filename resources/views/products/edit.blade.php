@extends('layouts.app')
@section('title', 'Modifier '.$product->name)

@section('content')
<form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data"
      class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf @method('PUT')

    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Nom *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Référence *</label>
                <input type="text" name="reference" value="{{ old('reference', $product->reference) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Code-barres</label>
                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Marque</label>
                <select name="brand_id" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">—</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id)==$b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Catégorie</label>
                <select name="category_id" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">—</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id)==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Genre *</label>
                <select name="gender" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    @foreach(['unisexe','homme','femme'] as $g)
                        <option value="{{ $g }}" @selected(old('gender', $product->gender)==$g)>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Contenance (ml)</label>
                <input type="number" name="volume" value="{{ old('volume', $product->volume) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="text-sm font-medium">Concentration</label>
                <select name="concentration" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">Non précisée</option>
                    @foreach(\App\Models\Product::CONCENTRATIONS as $code => $label)
                        <option value="{{ $code }}" @selected(old('concentration', $product->concentration) === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Prix d'achat</label>
                <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Prix de vente</label>
                <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Prix promo</label>
                <input type="number" step="0.01" name="promo_price" value="{{ old('promo_price', $product->promo_price) }}"
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Stock</label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Seuil d'alerte</label>
                <input type="number" name="alert_threshold" value="{{ old('alert_threshold', $product->alert_threshold) }}" required
                       class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="3" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('description', $product->description) }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 h-fit">
        <img src="{{ $product->image_url }}" class="w-24 h-24 rounded-lg object-cover mx-auto">
        <input type="file" name="image" accept="image/*"
               class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked($product->is_active) class="rounded border-slate-300">
            Produit actif
        </label>
        <div class="pt-4 flex gap-2">
            <button class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Mettre à jour</button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-slate-100 rounded-lg text-sm">Annuler</a>
        </div>
    </div>
</form>
@endsection