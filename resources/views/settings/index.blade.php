@extends('layouts.app')
@section('title', 'Paramètres de l’entreprise')

@section('content')
@php
    $activeTab = request()->query('tab', 'information');
    if (! in_array($activeTab, ['information', 'preferences', 'appearance'], true)) {
        $activeTab = 'information';
    }
@endphp
<div class="mx-auto max-w-6xl space-y-6"
     x-data="companySettings({
        activeTab: @js($activeTab),
        companyName: @js(old('company_name', $settings->company_name)),
        tagline: @js(old('tagline', $settings->tagline)),
        city: @js(old('city', $settings->city)),
        country: @js(old('country', $settings->country)),
        description: @js(old('description', $settings->description ?? '')),
        logoUrl: @js($settings->logo_url),
        color: @js(old('primary_color', $settings->primary_color)),
        vat: @js(old('vat_rate', $settings->vat_rate)),
    })">
    <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 px-6 py-7 text-white shadow-lg sm:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-200">PERSONNALISATION</p>
                <h2 class="mt-2 text-2xl font-bold sm:text-3xl">Paramètres de l’entreprise</h2>
                <p class="mt-2 max-w-2xl text-sm text-slate-300">Gérez les informations, les préférences et l’identité visuelle de votre parfumerie.</p>
            </div>
            <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm backdrop-blur">
                <span class="text-lg" x-text="countryFlag"></span>
                <span class="ml-1 font-medium" x-text="companyName || 'Votre entreprise'"></span>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
        <nav aria-label="Onglets des paramètres" class="flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm lg:flex-col lg:self-start">
            <button type="button" @click="setTab('information')" :aria-current="activeTab === 'information' ? 'page' : null"
                    :class="activeTab === 'information' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'"
                    class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold transition">
                <span>🏢</span><span>Informations</span>
            </button>
            <button type="button" @click="setTab('preferences')" :aria-current="activeTab === 'preferences' ? 'page' : null"
                    :class="activeTab === 'preferences' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'"
                    class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold transition">
                <span>⚙️</span><span>Réglages</span>
            </button>
            <button type="button" @click="setTab('appearance')" :aria-current="activeTab === 'appearance' ? 'page' : null"
                    :class="activeTab === 'appearance' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'"
                    class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold transition">
                <span>🎨</span><span>Logo &amp; apparence</span>
            </button>
        </nav>

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data"
              class="min-w-0 space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="tab" :value="activeTab">

            <section x-show="activeTab === 'information'" x-cloak class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Informations de l’entreprise</h3>
                    <p class="mt-1 text-sm text-slate-500">Ces renseignements sont utilisés sur les tickets et dans l’application.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="company_name" class="mb-1.5 block text-sm font-medium text-slate-700">Nom de l’entreprise <span class="text-rose-500">*</span></label>
                        <input id="company_name" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required maxlength="120"
                               x-model="companyName" class="settings-input" autocomplete="organization">
                        @error('company_name') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tagline" class="mb-1.5 block text-sm font-medium text-slate-700">Slogan</label>
                        <input id="tagline" name="tagline" value="{{ old('tagline', $settings->tagline) }}" maxlength="160"
                               x-model="tagline" placeholder="L’élégance au quotidien" class="settings-input">
                        @error('tagline') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email <span class="text-rose-500">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email', $settings->email) }}" required maxlength="255"
                               class="settings-input" autocomplete="email">
                        @error('email') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="website" class="mb-1.5 block text-sm font-medium text-slate-700">Site web</label>
                        <input id="website" name="website" type="url" value="{{ old('website', $settings->website) }}" maxlength="255"
                               placeholder="https://exemple.com" class="settings-input">
                        @error('website') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">Téléphone <span class="text-rose-500">*</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $settings->phone) }}" required maxlength="30"
                               class="settings-input" autocomplete="tel">
                        @error('phone') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tax_id" class="mb-1.5 block text-sm font-medium text-slate-700">N° SIRET / NIF</label>
                        <input id="tax_id" name="tax_id" value="{{ old('tax_id', $settings->tax_id) }}" maxlength="80" class="settings-input">
                        @error('tax_id') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Adresse</label>
                        <input id="address" name="address" value="{{ old('address', $settings->address) }}" maxlength="255"
                               class="settings-input" autocomplete="street-address">
                        @error('address') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="city" class="mb-1.5 block text-sm font-medium text-slate-700">Ville <span class="text-rose-500">*</span></label>
                        <input id="city" name="city" value="{{ old('city', $settings->city) }}" required maxlength="120"
                               x-model="city" class="settings-input" autocomplete="address-level2">
                        @error('city') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="country" class="mb-1.5 block text-sm font-medium text-slate-700">Pays <span class="text-rose-500">*</span></label>
                        <select id="country" name="country" x-model="country" required class="settings-input">
                            @foreach(\App\Models\Setting::COUNTRIES as $code => $details)
                                <option value="{{ $code }}" @selected(old('country', $settings->country) === $code)>{{ $details['flag'] }} {{ $details['name'] }}</option>
                            @endforeach
                        </select>
                        @error('country') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="mb-1.5 flex items-center justify-between text-sm font-medium text-slate-700">
                            <span>Description</span>
                            <span class="text-xs font-normal text-slate-400"><span x-text="description.length"></span> / 1500</span>
                        </label>
                        <textarea id="description" name="description" rows="4" maxlength="1500" x-model="description"
                                  placeholder="Présentez votre entreprise…" class="settings-input"></textarea>
                        @error('description') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-indigo-700">Aperçu sur le ticket</p>
                    <div class="flex items-center gap-3">
                        <img x-show="logoUrl" :src="logoUrl" alt="" class="h-12 w-12 rounded-lg bg-white object-contain p-1">
                        <div x-show="!logoUrl" class="flex h-12 w-12 items-center justify-center rounded-lg bg-white text-xl">🏢</div>
                        <div class="min-w-0">
                            <p class="truncate font-bold text-slate-900" x-text="companyName || 'Nom de l’entreprise'"></p>
                            <p class="truncate text-xs text-slate-500" x-text="[tagline, city, countryName].filter(Boolean).join(' · ')"></p>
                        </div>
                    </div>
                </div>
            </section>

            <section x-show="activeTab === 'preferences'" x-cloak class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Préférences de l’application</h3>
                    <p class="mt-1 text-sm text-slate-500">Choisissez les options utilisées pour l’affichage et les notifications.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="currency" class="mb-1.5 block text-sm font-medium text-slate-700">Devise</label>
                        <select id="currency" name="currency" x-model="currency" @change="currencySymbol = currencySymbols[currency]" class="settings-input">
                            @foreach(\App\Models\Setting::CURRENCIES as $code => $name)
                                <option value="{{ $code }}" @selected(old('currency', $settings->currency) === $code)>{{ $code }} — {{ $name }}</option>
                            @endforeach
                        </select>
                        @error('currency') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="currency_symbol" class="mb-1.5 block text-sm font-medium text-slate-700">Symbole de la devise</label>
                        <input id="currency_symbol" name="currency_symbol" x-model="currencySymbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" maxlength="12" required class="settings-input">
                        @error('currency_symbol') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="vat_rate" class="mb-1.5 block text-sm font-medium text-slate-700">TVA (%)</label>
                        <input id="vat_rate" name="vat_rate" type="number" min="0" max="100" step="0.01" x-model.number="vat"
                               value="{{ old('vat_rate', $settings->vat_rate) }}" required class="settings-input">
                        @error('vat_rate') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="timezone" class="mb-1.5 block text-sm font-medium text-slate-700">Fuseau horaire</label>
                        <select id="timezone" name="timezone" class="settings-input">
                            @foreach(\App\Models\Setting::TIMEZONES as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $settings->timezone) === $timezone)>🌍 {{ $timezone }}</option>
                            @endforeach
                        </select>
                        @error('timezone') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="date_format" class="mb-1.5 block text-sm font-medium text-slate-700">Format de date</label>
                        <select id="date_format" name="date_format" class="settings-input">
                            @foreach(\App\Models\Setting::DATE_FORMATS as $format => $example)
                                <option value="{{ $format }}" @selected(old('date_format', $settings->date_format) === $format)>{{ $format }} — {{ $example }}</option>
                            @endforeach
                        </select>
                        @error('date_format') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="invoice_email" class="mb-1.5 block text-sm font-medium text-slate-700">Email pour les factures</label>
                        <input id="invoice_email" name="invoice_email" type="email" value="{{ old('invoice_email', $settings->invoice_email) }}" maxlength="255"
                               placeholder="factures@exemple.com" class="settings-input">
                        @error('invoice_email') <p class="settings-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-start gap-3">
                        <input type="hidden" name="email_notifications" value="0">
                        <input id="email_notifications" name="email_notifications" type="checkbox" value="1"
                               @checked((bool) old('email_notifications', $settings->email_notifications))
                               class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <label for="email_notifications" class="text-sm font-semibold text-slate-800">Notifications par email</label>
                            <p class="mt-1 text-xs leading-5 text-slate-500">Enregistre votre préférence pour les alertes de ventes, de stock, de réservations et de paiements. Les envois automatiques nécessitent les événements correspondants.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Aperçu du calcul de TVA</p>
                    <div class="mt-3 space-y-1 text-sm">
                        <p class="flex justify-between"><span class="text-slate-500">Sous-total</span><span>1 000 <span x-text="currencySymbol"></span></span></p>
                        <p class="flex justify-between"><span class="text-slate-500">TVA (<span x-text="vat || 0"></span> %)</span><span x-text="formatAmount(1000 * (Number(vat) || 0) / 100) + ' ' + currencySymbol"></span></p>
                        <p class="flex justify-between border-t border-slate-200 pt-2 font-bold text-slate-900"><span>Total indicatif</span><span x-text="formatAmount(1000 * (1 + (Number(vat) || 0) / 100)) + ' ' + currencySymbol"></span></p>
                    </div>
                </div>
            </section>

            <section x-show="activeTab === 'appearance'" x-cloak class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Logo &amp; apparence</h3>
                    <p class="mt-1 text-sm text-slate-500">Personnalisez le logo et la couleur principale de votre espace.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_240px]">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Logo de l’entreprise</label>
                        <label for="logo" @dragover.prevent @drop.prevent="selectLogo($event.dataTransfer.files[0])"
                               class="flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-center transition hover:border-indigo-400 hover:bg-indigo-50/40">
                            <input id="logo" x-ref="logoInput" name="logo" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                                   class="sr-only" @change="selectLogo($event.target.files[0])">
                            <img x-show="logoUrl" :src="logoUrl" alt="Aperçu du logo" class="mb-3 h-20 w-20 rounded-xl bg-white object-contain p-2 shadow-sm">
                            <span x-show="!logoUrl" class="mb-3 text-3xl" aria-hidden="true">🖼️</span>
                            <span class="text-sm font-semibold text-slate-800">Choisir un fichier ou le déposer ici</span>
                            <span class="mt-1 text-xs text-slate-500">JPG, PNG, GIF ou WEBP · 5 Mo maximum</span>
                        </label>
                        @error('logo') <p class="settings-error">{{ $message }}</p> @enderror
                        <button type="submit" form="delete-logo-form" x-show="logoUrl" x-cloak
                                class="mt-3 rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                            Supprimer le logo
                        </button>
                    </div>

                    <div>
                        <label for="primary_color" class="mb-2 block text-sm font-medium text-slate-700">Couleur principale</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="color" @input="$refs.hexColor.value = color"
                                   aria-label="Sélecteur de couleur principal" class="h-11 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1">
                            <input id="primary_color" name="primary_color" x-ref="hexColor" x-model="color" value="{{ old('primary_color', $settings->primary_color) }}"
                                   pattern="#[0-9A-Fa-f]{6}" maxlength="7" required class="settings-input font-mono uppercase">
                        </div>
                        @error('primary_color') <p class="settings-error">{{ $message }}</p> @enderror
                        <div class="mt-4 grid grid-cols-4 gap-2">
                            @foreach(\App\Models\Setting::PRIMARY_COLORS as $hex => $name)
                                <button type="button" @click="color = '{{ $hex }}'; $refs.hexColor.value = color"
                                        title="{{ $name }} {{ $hex }}" aria-label="Choisir {{ strtolower($name) }}"
                                        class="h-9 rounded-lg border-2 border-white shadow-sm ring-1 ring-slate-200 transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-slate-400"
                                        style="background-color: {{ $hex }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Aperçu de la couleur</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" :style="{ backgroundColor: color }" class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm">Bouton principal</button>
                        <span :style="{ color: color }" class="text-sm font-semibold">Lien actif</span>
                        <span :style="{ backgroundColor: color + '20', color: color }" class="rounded-full px-2.5 py-1 text-xs font-semibold">Badge</span>
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="submit" class="rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:brightness-95"
                        :style="{ backgroundColor: color }">
                    Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>

<form id="delete-logo-form" method="POST" action="{{ route('settings.logo.destroy') }}" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function companySettings(initial) {
        return {
            ...initial,
            currency: @js(old('currency', $settings->currency)),
            currencySymbol: @js(old('currency_symbol', $settings->currency_symbol)),
            currencySymbols: @js(\App\Models\Setting::CURRENCY_SYMBOLS),
            countries: @js(\App\Models\Setting::COUNTRIES),
            get countryFlag() { return this.countries[this.country]?.flag || '🌍'; },
            get countryName() { return this.countries[this.country]?.name || ''; },
            formatAmount(value) { return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value); },
            setTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            },
            selectLogo(file) {
                if (!file) return;
                const transfer = new DataTransfer();
                transfer.items.add(file);
                this.$refs.logoInput.files = transfer.files;
                this.logoUrl = URL.createObjectURL(file);
            },
        };
    }
</script>
<style>
    .settings-input {
        display: block;
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: .625rem;
        background: #fff;
        padding: .625rem .75rem;
        font-size: .875rem;
        color: #0f172a;
    }
    .settings-input:focus {
        border-color: var(--brand-primary);
        outline: 2px solid transparent;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--brand-primary), transparent 75%);
    }
    .settings-error { margin-top: .375rem; font-size: .75rem; color: #e11d48; }
</style>
@endpush
@endsection
