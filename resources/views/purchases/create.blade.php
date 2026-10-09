@extends('layouts.app')
@section('title', 'Nouvel achat fournisseur')

@section('content')
<form method="POST" action="{{ route('purchases.store') }}" x-data="purchaseForm()">
    @csrf
    <template x-for="(item, index) in items" :key="index">
        <div>
            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
            <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
            <input type="hidden" :name="`items[${index}][purchase_price]`" :value="item.purchase_price">
        </div>
    </template>

    <section class="animate-enter mb-6 rounded-2xl bg-gradient-to-r from-sky-900 via-cyan-800 to-teal-700 p-6 text-white shadow-lg">
        <p class="text-sm text-sky-100">Approvisionnement · Nouvelle réception</p>
        <h2 class="mt-1 text-2xl font-bold">Enregistrer un nouvel achat</h2>
        <p class="mt-2 text-sm text-sky-50/90">Chaque ligne ajoutée mettra automatiquement à jour le stock du parfum.</p>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium">Fournisseur</label>
                    <select name="supplier_id" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">—</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Date *</label>
                    <input type="date" name="purchased_at" value="{{ now()->toDateString() }}" required
                           class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Montant payé</label>
                    <input type="number" name="paid_amount" min="0" step="0.01" :max="total"
                           :value="paidAmount === null ? total : paidAmount"
                           @input="paidAmount = $event.target.value"
                           placeholder="Total à régler"
                           class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <h3 class="font-semibold mb-3">Articles</h3>
                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-12 items-center gap-2 rounded-xl border border-slate-100 p-3">
                            <select x-model="item.product_id" @change="setPrice(index)" required
                                    class="col-span-6 px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                <option value="">— Produit —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (stock: {{ $p->stock_quantity }})</option>
                                @endforeach
                            </select>
                            <input type="number" min="1" x-model.number="item.quantity" placeholder="Qté" aria-label="Quantité"
                                   class="col-span-2 px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            <input type="number" min="0" step="0.01" x-model.number="item.purchase_price" placeholder="Prix achat" aria-label="Prix d'achat unitaire"
                                   class="col-span-3 px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            <button type="button" @click="items.splice(index, 1)" :disabled="items.length === 1" aria-label="Retirer cette ligne" class="col-span-1 text-rose-600 disabled:cursor-not-allowed disabled:opacity-30">✕</button>
                            <label class="col-span-7 text-xs font-medium text-slate-600">N° de lot
                                <input type="text" maxlength="100" x-model="item.lot_number" :name="`items[${index}][lot_number]`" placeholder="Ex. LOT-2026-001"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            </label>
                            <label class="col-span-5 text-xs font-medium text-slate-600">Date d’expiration
                                <input type="date" x-model="item.expires_at" :name="`items[${index}][expires_at]`" min="{{ now()->toDateString() }}"
                                       class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            </label>
                            <div class="col-span-12 text-right text-xs text-slate-500" x-text="'Ligne : ' + fmt((item.quantity || 0) * (item.purchase_price || 0))"></div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addLine" class="mt-3 text-sm text-indigo-600 hover:underline">+ Ajouter un article</button>
            </div>

            <div>
                <label class="text-sm font-medium">Notes</label>
                <textarea name="notes" rows="2" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></textarea>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm h-fit lg:sticky lg:top-20">
            <h3 class="font-semibold mb-3">Récapitulatif</h3>
            <div class="text-sm space-y-1">
                <div class="flex justify-between"><span class="text-slate-500">Articles</span><span x-text="items.filter(i => i.product_id).length"></span></div>
                <div class="flex justify-between text-lg font-bold pt-3 border-t">
                    <span>Total</span>
                    <span class="text-indigo-600" x-text="fmt(total)"></span>
                </div>
                <div class="flex justify-between text-xs"><span class="text-slate-500">Reste à payer</span><span class="font-semibold text-amber-700" x-text="fmt(Math.max(0, total - (paidAmount === null || paidAmount === '' ? total : Number(paidAmount))))"></span></div>
            </div>
            <button :disabled="!items.some(item => item.product_id && item.quantity > 0) || (paidAmount !== null && Number(paidAmount) > total)" class="mt-4 w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-300">Enregistrer l'achat</button>
            <a href="{{ route('purchases.index') }}" class="mt-2 block text-center px-4 py-2 bg-slate-100 rounded-lg text-sm">Annuler</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function purchaseForm() {
    return {
        items: [{ product_id: '', quantity: 1, purchase_price: 0, lot_number: '', expires_at: '' }],
        paidAmount: null,
        get total() { return this.items.reduce((s, i) => s + (i.quantity * i.purchase_price || 0), 0); },
        addLine() { this.items.push({ product_id: '', quantity: 1, purchase_price: 0, lot_number: '', expires_at: '' }); },
        setPrice() { /* optionnel : préremplir prix d'achat */ },
        fmt(v) { return new Intl.NumberFormat('fr-FR').format(v) + ' {{ config('app.currency') }}'; }
    }
}
</script>
@endpush