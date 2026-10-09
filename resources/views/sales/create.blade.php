@extends('layouts.app')
@section('title', 'Nouvelle vente')

@section('content')
<form method="POST" action="{{ route('sales.store') }}" x-data="pos()" @submit="prepareSubmit">
    @csrf
    <input type="hidden" name="customer_id" :value="customerId">
    <input type="hidden" name="discount" :value="discount || 0">
    <input type="hidden" name="payment_method" :value="paymentMethod">
    <input type="hidden" name="paid_amount" :value="amountPaid">

    {{-- Lignes du panier --}}
    <template x-for="(item, index) in cart" :key="item.id">
        <div>
            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.id">
            <input type="hidden" :name="`items[${index}][quantity]`" :value="item.qty">
            <input type="hidden" :name="`items[${index}][price]`" :value="item.price">
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Produits --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <label class="mb-2 block text-sm font-semibold text-slate-800" for="product-search">Choisir un parfum</label>
                <input id="product-search" type="search" x-model="search" placeholder="Nom, référence ou code-barres…"
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                <p class="mt-2 text-xs text-slate-500"><span x-text="filtered.length"></span> parfum(s) disponible(s) · Cliquez sur une fiche pour l’ajouter au panier.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[70vh] overflow-y-auto pr-1">
                <template x-for="p in filtered" :key="p.id">
                    <button type="button" @click="add(p)"
                            :disabled="p.stock_quantity <= 0"
                            class="text-left bg-white rounded-xl border border-slate-200 p-3 hover:border-indigo-400 hover:shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <img :src="p.image_url" :alt="p.name" loading="lazy" class="w-full aspect-square object-cover rounded-lg mb-2">
                        <div class="text-sm font-medium truncate" x-text="p.name"></div>
                        <div class="text-xs text-slate-500" x-text="(p.brand||'—') + ' · ' + p.volume + 'ml'"></div>
                        <div class="mt-1 flex items-center justify-between">
                            <span class="text-sm font-bold text-indigo-600" x-text="fmt(p.price)"></span>
                            <span class="text-xs px-1.5 py-0.5 rounded"
                                  :class="p.stock_quantity <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600'"
                                  x-text="p.stock_quantity"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        {{-- Panier --}}
        <div class="bg-white rounded-xl border border-slate-200 flex flex-col h-fit lg:sticky lg:top-20">
            <div class="px-5 py-4 border-b border-slate-200 font-semibold text-slate-800">🛒 Panier</div>

            <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate" x-text="item.name"></div>
                            <div class="text-xs text-slate-500" x-text="fmt(item.price)"></div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="dec(index)" class="w-7 h-7 rounded bg-slate-100 hover:bg-slate-200 text-sm">−</button>
                            <span class="w-8 text-center text-sm font-medium" x-text="item.qty"></span>
                            <button type="button" @click="inc(index)" class="w-7 h-7 rounded bg-slate-100 hover:bg-slate-200 text-sm">+</button>
                        </div>
                        <button type="button" @click="remove(index)" class="text-rose-600 text-sm">✕</button>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="px-4 py-10 text-center text-slate-500 text-sm">
                    Panier vide
                </div>
            </div>

            <div class="p-4 border-t border-slate-200 space-y-3">
                <div>
                    <label class="text-xs text-slate-500">Client</label>
                    <select x-model="customerId" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        <option value="">Passage</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->phone }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-500">Remise</label>
                        <input type="number" min="0" step="0.01" x-model.number="discount"
                               :max="subtotal"
                               class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Paiement</label>
                        <select x-model="paymentMethod" class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            <option value="cash">Espèces</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Carte</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-3 space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Sous-total</span><span x-text="fmt(subtotal)"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Remise</span><span x-text="'- ' + fmt(discount||0)"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">TVA (<span x-text="vatRate"></span> %)</span><span x-text="fmt(vatAmount)"></span></div>
                    <div class="flex justify-between text-lg font-bold text-slate-800">
                        <span>Total</span><span class="text-indigo-600" x-text="fmt(total)"></span>
                    </div>
                </div>

                <div>
                    <label for="paid-amount" class="text-xs font-medium text-slate-600">Montant remis <span class="font-normal text-slate-400">(laisser vide si paiement complet)</span></label>
                    <input id="paid-amount" type="number" min="0" step="0.01" x-model.number="paidAmount" :max="total" placeholder="Paiement complet"
                           class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <div x-show="amountPaid < total" class="mt-2 flex justify-between text-xs">
                        <span class="text-amber-700">Reste à payer</span>
                        <span class="font-semibold text-amber-700" x-text="fmt(remainingAmount)"></span>
                    </div>
                    <div x-show="amountPaid > total" class="mt-2 flex justify-between text-xs">
                        <span class="text-emerald-700">Monnaie à rendre</span>
                        <span class="font-semibold text-emerald-700" x-text="fmt(changeAmount)"></span>
                    </div>
                </div>

                <button type="submit" :disabled="cart.length === 0 || discount > subtotal"
                        class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 text-white rounded-lg font-medium">
                    ✅ Valider la vente
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
@php
    $posProducts = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'volume' => $product->volume,
        'reference' => $product->reference,
        'barcode' => $product->barcode,
        'brand' => $product->brand?->name,
        'price' => (float) $product->current_price,
        'stock_quantity' => $product->stock_quantity,
        'image_url' => $product->image_url,
    ])->values();
@endphp
<script>
function pos() {
    return {
        products: @json($posProducts),
        search: '',
        cart: [],
        discount: 0,
        vatRate: @js((float) $settings->vat_rate),
        currencySymbol: @js($settings->currency_symbol),
        customerId: '',
        paymentMethod: 'cash',
        paidAmount: null,

        get filtered() {
            const s = this.search.toLowerCase();
            if (!s) return this.products;
            return this.products.filter(p =>
                p.name.toLowerCase().includes(s) ||
                (p.reference || '').toLowerCase().includes(s) ||
                (p.barcode || '').toLowerCase().includes(s) ||
                (p.brand || '').toLowerCase().includes(s)
            );
        },
        get subtotal() {
            return this.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
        },
        get total() {
            return Math.max(0, this.subtotal - (this.discount || 0) + this.vatAmount);
        },
        get vatAmount() {
            return Math.round(Math.max(0, this.subtotal - (this.discount || 0)) * this.vatRate) / 100;
        },
        get amountPaid() {
            return this.paidAmount === null || this.paidAmount === '' ? this.total : Number(this.paidAmount);
        },
        get remainingAmount() {
            return Math.max(0, this.total - this.amountPaid);
        },
        get changeAmount() {
            return Math.max(0, this.amountPaid - this.total);
        },

        add(p) {
            if (p.stock_quantity <= 0) return;
            const existing = this.cart.find(i => i.id === p.id);
            if (existing) {
                if (existing.qty < p.stock_quantity) existing.qty++;
            } else {
                this.cart.push({ id: p.id, name: p.name, price: p.price, qty: 1, max: p.stock_quantity });
            }
        },
        inc(i) {
            const item = this.cart[i];
            if (item.qty < item.max) item.qty++;
        },
        dec(i) {
            this.cart[i].qty--;
            if (this.cart[i].qty <= 0) this.cart.splice(i, 1);
        },
        remove(i) { this.cart.splice(i, 1); },

        fmt(v) { return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(v) + ' ' + this.currencySymbol; },
        prepareSubmit(e) {
            if (this.cart.length === 0 || this.discount > this.subtotal) { e.preventDefault(); }
        }
    }
}
</script>
@endpush