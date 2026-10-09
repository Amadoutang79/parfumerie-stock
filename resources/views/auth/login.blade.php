@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')
<div x-data="loginForm()" class="glass rounded-3xl p-8 shadow-2xl animate-scale-in">

    {{-- En-tête animé --}}
    <div class="text-center mb-8 animate-fade-up">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-indigo-600 flex items-center justify-center text-white text-2xl shadow-xl shadow-fuchsia-500/30 mx-auto mb-4 animate-float">
            🔐
        </div>
        <h1 class="text-2xl font-bold text-white">
            Bon retour <span class="gradient-text">parmi nous</span>
        </h1>
        <p class="text-sm text-white/60 mt-1.5">Connectez-vous à votre espace</p>
    </div>

    {{-- Erreurs --}}
    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
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

    {{-- Statut (ex: mot de passe réinitialisé) --}}
    @if (session('status'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-100 text-sm flex items-start gap-2 animate-fade-up">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

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
                    autofocus
                    autocomplete="username"
                    placeholder="vous@exemple.com"
                    class="glass-input w-full pl-12 pr-4 py-3.5 rounded-xl text-white placeholder-white/30 text-sm"
                >
                <span class="input-line"></span>
            </div>
        </div>

        {{-- Mot de passe --}}
        <div class="input-wrapper animate-slide-left" style="animation-delay:.2s">
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
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
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
        </div>

        {{-- Remember + Forgot --}}
        <div class="flex items-center justify-between text-sm animate-slide-left" style="animation-delay:.3s">
            <label class="flex items-center gap-2 text-white/70 cursor-pointer hover:text-white/90 transition">
                <input type="checkbox" name="remember"
                       class="rounded bg-white/10 border-white/30 text-fuchsia-500 focus:ring-fuchsia-500/50 focus:ring-offset-0">
                <span>Se souvenir de moi</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-fuchsia-400 hover:text-fuchsia-300 transition text-sm font-medium">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        {{-- Bouton --}}
        <button
            type="submit"
            :disabled="loading"
            @click="loading = true"
            class="btn-gradient w-full px-6 py-3.5 rounded-xl text-white font-semibold text-sm
                   shadow-lg shadow-fuchsia-500/20 relative overflow-hidden
                   animate-slide-left disabled:opacity-70 disabled:cursor-not-allowed"
            style="animation-delay:.4s"
        >
            <span x-show="!loading" class="flex items-center justify-center gap-2">
                Se connecter
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
                Connexion...
            </span>
        </button>

        {{-- Lien inscription --}}
        <p class="text-center text-sm text-white/60 pt-1 animate-fade-up" style="animation-delay:.5s">
            Pas encore de compte ?
            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="gradient-text font-semibold hover:opacity-80 transition">
                    Créer un compte
                </a>
            @endif
        </p>
    </form>
</div>

{{-- Comptes de démo --}}
<div class="mt-5 glass rounded-2xl p-4 text-xs text-white/70 animate-fade-up" style="animation-delay:.6s">
    <div class="flex items-center gap-2 font-semibold text-white/90 mb-2.5">
        <span>🔑</span> Comptes de démonstration
    </div>
    <div class="space-y-1.5">
        <button type="button" @click="fillDemo('admin@parfumerie.test')"
                class="w-full text-left flex justify-between items-center p-2 rounded-lg hover:bg-white/5 transition group">
            <span class="text-white/70 group-hover:text-white/90">👑 Admin</span>
            <span class="font-mono text-white/50 text-[11px] group-hover:text-fuchsia-400">admin@parfumerie.test</span>
        </button>
        <button type="button" @click="fillDemo('gerant@parfumerie.test')"
                class="w-full text-left flex justify-between items-center p-2 rounded-lg hover:bg-white/5 transition group">
            <span class="text-white/70 group-hover:text-white/90">📋 Gérant</span>
            <span class="font-mono text-white/50 text-[11px] group-hover:text-fuchsia-400">gerant@parfumerie.test</span>
        </button>
        <button type="button" @click="fillDemo('vendeur@parfumerie.test')"
                class="w-full text-left flex justify-between items-center p-2 rounded-lg hover:bg-white/5 transition group">
            <span class="text-white/70 group-hover:text-white/90">💼 Vendeur</span>
            <span class="font-mono text-white/50 text-[11px] group-hover:text-fuchsia-400">vendeur@parfumerie.test</span>
        </button>
    </div>
    <p class="mt-2.5 text-center text-[11px] text-white/40">Cliquez pour remplir automatiquement</p>
</div>
@endsection

@push('scripts')
<script>
function loginForm() {
    return {
        showPassword: false,
        loading: false,
        fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
            document.getElementById('password').focus();
        }
    }
}
</script>
@endpush