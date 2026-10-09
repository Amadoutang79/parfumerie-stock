@extends('layouts.guest')

@section('title', 'Inscription')

@section('content')
<div x-data="registerForm()" class="glass rounded-3xl p-8 shadow-2xl animate-scale-in">

    {{-- En-tête --}}
    <div class="text-center mb-8 animate-fade-up">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-indigo-600 flex items-center justify-center text-white text-2xl shadow-xl shadow-fuchsia-500/30 mx-auto mb-4 animate-float">
            ✨
        </div>
        <h1 class="text-2xl font-bold text-white">
            Créer un <span class="gradient-text">compte</span>
        </h1>
        <p class="text-sm text-white/60 mt-1.5">Rejoignez la gestion de parfumerie</p>
    </div>

    {{-- Erreurs --}}
    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
             x-transition.opacity
             class="mb-5 px-4 py-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-100 text-sm flex items-start gap-2 animate-fade-up">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- Nom --}}
        <div class="input-wrapper animate-slide-left" style="animation-delay:.05s">
            <label for="name" class="block text-sm font-medium text-white/85 mb-2">
                Nom complet
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Awa Traoré"
                    class="glass-input w-full pl-12 pr-4 py-3.5 rounded-xl text-white placeholder-white/30 text-sm"
                >
                <span class="input-line"></span>
            </div>
        </div>

        {{-- Email --}}
        <div class="input-wrapper animate-slide-left" style="animation-delay:.1s">
            <label for="email" class="block text-sm font-medium text-white/85 mb-2">
                Adresse email
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </span>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    placeholder="vous@exemple.com"
                    class="glass-input w-full pl-12 pr-4 py-3.5 rounded-xl text-white placeholder-white/30 text-sm"
                >
                <span class="input-line"></span>
            </div>
        </div>

        {{-- Mot de passe --}}
        <div class="input-wrapper animate-slide-left" style="animation-delay:.15s">
            <label for="password" class="block text-sm font-medium text-white/85 mb-2">
                Mot de passe
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <input
                    id="password"
                    name="password"
                    :type="showPassword ? 'text' : 'password'"
                    x-model="password"
                    @input="checkStrength"
                    required
                    autocomplete="new-password"
                    placeholder="Au moins 6 caractères"
                    class="glass-input w-full pl-12 pr-12 py-3.5 rounded-xl text-white placeholder-white/30 text-sm"
                >
                <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
                <span class="input-line"></span>
            </div>

            {{-- Indicateur de force --}}
            <div x-show="password.length > 0" x-cloak x-transition class="mt-3">
                <div class="flex gap-1.5">
                    <div class="strength-bar flex-1 bg-white/10"
                         :class="strength >= 1 ? 'bg-rose-500' : 'bg-white/10'"></div>
                    <div class="strength-bar flex-1 bg-white/10"
                         :class="strength >= 2 ? 'bg-amber-500' : 'bg-white/10'"></div>
                    <div class="strength-bar flex-1 bg-white/10"
                         :class="strength >= 3 ? 'bg-emerald-500' : 'bg-white/10'"></div>
                    <div class="strength-bar flex-1 bg-white/10"
                         :class="strength >= 4 ? 'bg-emerald-400' : 'bg-white/10'"></div>
                </div>
                <p class="text-[11px] mt-1.5 transition"
                   :class="{
                       'text-rose-400':    strength === 1,
                       'text-amber-400':   strength === 2,
                       'text-emerald-400': strength >= 3
                   }"
                   x-text="strengthLabel"></p>
            </div>
        </div>

        {{-- Confirmation --}}
        <div class="input-wrapper animate-slide-left" style="animation-delay:.2s">
            <label for="password_confirmation" class="block text-sm font-medium text-white/85 mb-2">
                Confirmer le mot de passe
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="glass-input w-full pl-12 pr-4 py-3.5 rounded-xl text-white placeholder-white/30 text-sm"
                >
                <span class="input-line"></span>
            </div>
        </div>

        {{-- Conditions --}}
        <label class="flex items-start gap-3 text-sm text-white/70 cursor-pointer animate-slide-left" style="animation-delay:.25s">
            <input type="checkbox" required
                   class="mt-0.5 rounded bg-white/10 border-white/30 text-fuchsia-500 focus:ring-fuchsia-500/50 focus:ring-offset-0">
            <span>
                J'accepte les
                <a href="#" class="text-fuchsia-400 hover:text-fuchsia-300 underline">conditions d'utilisation</a>
                et la
                <a href="#" class="text-fuchsia-400 hover:text-fuchsia-300 underline">politique de confidentialité</a>
            </span>
        </label>

        {{-- Bouton --}}
        <button
            type="submit"
            :disabled="loading"
            @click="loading = true"
            class="btn-gradient w-full px-6 py-3.5 rounded-xl text-white font-semibold text-sm
                   shadow-lg shadow-fuchsia-500/20 relative overflow-hidden
                   animate-slide-left disabled:opacity-70 disabled:cursor-not-allowed"
            style="animation-delay:.3s"
        >
            <span x-show="!loading" class="flex items-center justify-center gap-2">
                Créer mon compte
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </span>
            <span x-show="loading" x-cloak class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                Création...
            </span>
        </button>

        {{-- Lien connexion --}}
        <p class="text-center text-sm text-white/60 pt-1 animate-fade-up" style="animation-delay:.35s">
            Déjà inscrit ?
            <a href="{{ route('login') }}"
               class="gradient-text font-semibold hover:opacity-80 transition">
                Se connecter
            </a>
        </p>
    </form>
</div>
@endsection

@push('scripts')
<script>
function registerForm() {
    return {
        showPassword: false,
        loading: false,
        password: '',
        strength: 0,
        strengthLabel: '',

        checkStrength() {
            const p = this.password;
            let score = 0;
            if (p.length >= 6)  score++;
            if (p.length >= 10) score++;
            if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++;
            if (/\d/.test(p))   score++;
            if (/[^A-Za-z0-9]/.test(p)) score++;

            this.strength = Math.min(4, score);
            this.strengthLabel = ['', 'Faible', 'Moyen', 'Fort', 'Très fort'][this.strength] || '';
        }
    }
}
</script>
@endpush