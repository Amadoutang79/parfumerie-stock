<!DOCTYPE html>
<html lang="fr" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings->company_name }} — Gestion de stock pour parfumerie</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        html, body { font-family: 'Inter', sans-serif; }

        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%     { transform: translateY(-14px); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes gradientAnim {
            0%,100% { background-position: 0% 50%; }
            50%     { background-position: 100% 50%; }
        }
        @keyframes pulseSoft {
            0%,100% { opacity: 1; }
            50%     { opacity: .7; }
        }
        @keyframes revealUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes barGrow {
            from { transform: scaleY(.1); }
            to   { transform: scaleY(1); }
        }

        .gradient-text {
            background: linear-gradient(90deg, #fcd34d, #fb7185, #fdba74, #fcd34d);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradientAnim 8s ease infinite;
        }
        .bg-grid {
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .glass {
            background: rgba(255,255,255,.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.12);
        }
        .bar {
            transform-origin: bottom;
            animation: barGrow 1.4s cubic-bezier(.2,.8,.2,1) both;
        }
        .reveal { opacity: 0; will-change: opacity, transform; }
        .reveal.visible {
            animation: revealUp .8s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--reveal-delay, 0ms);
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
            .reveal { opacity: 1 !important; }
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-800"
      x-data="{ mobileOpen: false, scrolled: false }"
      @scroll.window.passive="scrolled = window.scrollY > 20">

    {{-- ================= HEADER ================= --}}
    <header :class="scrolled ? 'bg-slate-900/90 backdrop-blur shadow-lg' : 'bg-transparent'"
            class="fixed top-0 left-0 right-0 z-50 transition-colors duration-300">
        <nav class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                @if($settings->logo_url)
                    <img src="{{ $settings->logo_url }}" alt="{{ $settings->company_name }}" class="h-10 w-10 rounded-xl bg-white object-contain p-1 shadow-lg group-hover:scale-110 transition-transform duration-300">
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl text-lg font-bold text-white shadow-lg shadow-amber-950/30 group-hover:scale-110 transition-transform duration-300"
                         style="background: linear-gradient(135deg, {{ $settings->primary_color }}, #0f172a)">
                        {{ mb_substr($settings->company_name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="font-bold text-white leading-tight">{{ $settings->company_name }}</div>
                    <div class="text-[11px] text-white/70 leading-tight">{{ $settings->tagline ?: 'Gestion de stock' }}</div>
                </div>
            </a>

            {{-- Liens desktop --}}
            <div class="hidden md:flex items-center gap-3">
                <a href="#modules" class="px-3 py-2 text-sm text-white/80 hover:text-white transition">Modules</a>
                <a href="#roles"   class="px-3 py-2 text-sm text-white/80 hover:text-white transition">Rôles</a>
                <a href="#about"   class="px-3 py-2 text-sm text-white/80 hover:text-white transition">À propos</a>

                @auth
                    {{-- Utilisateur connecté --}}
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
                       class="px-4 py-2 rounded-lg bg-white text-slate-900 text-sm font-semibold hover:bg-slate-100 transition">
                        Tableau de bord →
                    </a>
                @else
                    {{-- Utilisateur NON connecté : boutons Connexion + Inscription toujours visibles --}}
                    <a href="{{ Route::has('login') ? route('login') : url('/login') }}"
                       class="px-4 py-2 rounded-lg text-white text-sm font-medium hover:bg-white/10 transition border border-white/20">
                        Connexion
                    </a>
                    <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                       class="px-4 py-2 rounded-lg bg-amber-300 text-slate-950 text-sm font-semibold hover:bg-amber-200 transition shadow-lg">
                        Inscription
                    </a>
                @endauth
            </div>

            {{-- Burger mobile --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="md:hidden text-white p-2 rounded-lg hover:bg-white/10 transition"
                    aria-label="Menu">
                <svg x-show="!mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </nav>

        {{-- Menu mobile --}}
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-slate-900/95 backdrop-blur border-t border-white/10">
            <div class="px-6 py-4 space-y-3">
                <a href="#modules" @click="mobileOpen=false" class="block text-white/80 hover:text-white">Modules</a>
                <a href="#roles"   @click="mobileOpen=false" class="block text-white/80 hover:text-white">Rôles</a>
                <a href="#about"   @click="mobileOpen=false" class="block text-white/80 hover:text-white">À propos</a>

                @auth
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
                       class="block px-4 py-2 rounded-lg bg-white text-slate-900 text-center font-semibold">
                        Tableau de bord →
                    </a>
                @else
                    <a href="{{ Route::has('login') ? route('login') : url('/login') }}"
                       class="block px-4 py-2 rounded-lg bg-white/10 text-white text-center border border-white/20">
                        Connexion
                    </a>
                    <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                       class="block px-4 py-2 rounded-lg bg-amber-300 text-slate-950 text-center font-semibold">
                        Inscription
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ================= HERO ================= --}}
    <section class="relative min-h-screen flex items-center overflow-hidden bg-gradient-to-br from-[#171411] via-[#2a211a] to-[#542b20]">
        <div class="absolute inset-0 bg-grid opacity-40"></div>
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-amber-400/20 blur-3xl animate-float-slow"></div>
        <div class="absolute -bottom-32 -left-32 w-[500px] h-[500px] rounded-full bg-rose-500/20 blur-3xl animate-float"></div>

        <div class="relative max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center py-24">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass text-white/90 text-xs font-medium animate-fade-in">
                    <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse-soft"></span>
                    L’essentiel pour votre parfumerie, réuni au même endroit
                </span>

                <h1 class="mt-6 text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight animate-fade-up">
                    Votre parfumerie,
                    <span class="block gradient-text">maîtrisée parfum par parfum.</span>
                </h1>

                <p class="mt-6 text-lg text-white/70 max-w-xl animate-fade-up" style="animation-delay:.15s">
                    Suivez les stocks, encaissez vos ventes, gérez vos achats et repérez les produits à réapprovisionner,
                    depuis une interface conçue pour votre quotidien.
                </p>

                {{-- Boutons CTA hero --}}
                <div class="mt-8 flex flex-wrap gap-3 animate-fade-up" style="animation-delay:.3s">
                    @auth
                        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
                           class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-rose-500 text-slate-950 font-semibold shadow-lg shadow-amber-950/40 hover:scale-105 transition-transform duration-300">
                            Ouvrir mon tableau de bord
                        </a>
                    @else
                        <a href="{{ Route::has('login') ? route('login') : url('/login') }}"
                           class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-rose-500 text-slate-950 font-semibold shadow-lg shadow-amber-950/40 hover:scale-105 transition-transform duration-300">
                            Se connecter
                        </a>
                        <a href="#modules"
                           class="px-6 py-3 rounded-xl glass text-white font-semibold hover:bg-white/20 transition">
                            Découvrir les fonctionnalités
                        </a>
                    @endauth
                </div>

                <div class="mt-12 grid grid-cols-3 gap-6 max-w-md animate-fade-up" style="animation-delay:.45s">
                    <div>
                        <div class="text-3xl font-extrabold text-white">1</div>
                        <div class="text-xs text-white/60">Application unifiée</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-white">8</div>
                        <div class="text-xs text-white/60">Modules</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-white">3</div>
                        <div class="text-xs text-white/60">Rôles</div>
                    </div>
                </div>
            </div>

            {{-- Aperçu dashboard --}}
            <div class="relative hidden lg:block">
                <div class="absolute inset-0 bg-gradient-to-tr from-amber-400/30 to-rose-500/30 rounded-3xl blur-2xl animate-pulse-soft"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-6 animate-float" style="animation-duration:7s">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-indigo-700">Aperçu de démonstration</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white hover:scale-105 transition">
                            <div class="text-xs opacity-80">Valeur du stock</div>
                            <div class="text-lg font-bold mt-1">2 450 000 F</div>
                        </div>
                        <div class="rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 p-4 text-white hover:scale-105 transition">
                            <div class="text-xs opacity-80">Ventes du jour</div>
                            <div class="text-lg font-bold mt-1">145 000 F</div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex justify-between items-end h-24 gap-1.5">
                            @foreach([40, 65, 45, 80, 55, 90, 70] as $i => $h)
                                <div class="bar flex-1 rounded-t bg-gradient-to-t from-amber-500 to-rose-500 hover:opacity-80 transition"
                                     style="height: {{ $h }}%; animation-delay: {{ $i * 0.1 }}s"></div>
                            @endforeach
                        </div>
                        <div class="mt-3 text-xs text-slate-500">Ventes — 7 derniers jours</div>
                    </div>

                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-700 font-medium">Parfum boisé · 100 ml</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">OK</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-700 font-medium">Parfum floral · 50 ml</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 animate-pulse-soft">Faible</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-700 font-medium">Parfum ambré · 75 ml</span>
                            <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">Rupture</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a href="#modules" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 hover:text-white animate-bounce-slow">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </a>
    </section>

    {{-- ================= MODULES ================= --}}
    <section id="modules" class="max-w-7xl mx-auto px-6 py-24">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Tout ce dont votre boutique a besoin</h2>
            <p class="mt-4 text-slate-500">
                Des modules simples, pensés pour être utilisés au quotidien par les gérants et vendeurs.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @php
                $modules = [
                    ['icon'=>'🏠','title'=>'Tableau de bord','desc'=>'Vue d’ensemble : ventes, stock, alertes, bénéfices.', 'color'=>'from-indigo-500 to-indigo-600'],
                    ['icon'=>'🧴','title'=>'Parfums','desc'=>'Fiches produits, marques, catégories, prix promo.', 'color'=>'from-fuchsia-500 to-fuchsia-600'],
                    ['icon'=>'📦','title'=>'Stock','desc'=>'Entrées, sorties, ajustements et historique complet.', 'color'=>'from-emerald-500 to-emerald-600'],
                    ['icon'=>'🛒','title'=>'Ventes','desc'=>'Point de vente rapide, panier, remise, ticket imprimable.', 'color'=>'from-amber-500 to-amber-600'],
                    ['icon'=>'🚚','title'=>'Achats','desc'=>'Commandes fournisseurs avec mise à jour auto du stock.', 'color'=>'from-sky-500 to-sky-600'],
                    ['icon'=>'🏭','title'=>'Fournisseurs','desc'=>'Contacts, WhatsApp, historique des commandes.', 'color'=>'from-rose-500 to-rose-600'],
                    ['icon'=>'👥','title'=>'Clients','desc'=>'Fiches clients, historique d’achat, total dépensé.', 'color'=>'from-violet-500 to-violet-600'],
                    ['icon'=>'📊','title'=>'Rapports','desc'=>'Ventes, bénéfices, meilleurs produits et export CSV.', 'color'=>'from-teal-500 to-teal-600'],
                ];
            @endphp

            @foreach($modules as $i => $m)
                <div class="reveal group bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                     style="--reveal-delay: {{ $i * 60 }}ms">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $m['color'] }} flex items-center justify-center text-2xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        {{ $m['icon'] }}
                    </div>
                    <h3 class="mt-4 font-semibold text-slate-900">{{ $m['title'] }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $m['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= RÔLES ================= --}}
    <section id="roles" class="bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-24">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal">
                <h2 class="text-3xl font-bold text-slate-900">Trois rôles, une seule application</h2>
                <p class="mt-3 text-slate-500">Chaque utilisateur voit uniquement ce dont il a besoin.</p>
            </div>

            @php
                $roles = [
                    ['name'=>'Administrateur','emoji'=>'👑','bg'=>'bg-indigo-50','hover'=>'hover:border-indigo-300','check'=>'text-indigo-500',
                     'points'=>['Accès complet aux modules de gestion','Gestion des utilisateurs et de leurs rôles','Suivi des ventes, achats et rapports']],
                    ['name'=>'Gérant','emoji'=>'📋','bg'=>'bg-emerald-50','hover'=>'hover:border-emerald-300','check'=>'text-emerald-500',
                     'points'=>['Gestion des parfums, du stock et des ventes','Gestion des fournisseurs et des clients','Consultation des rapports et statistiques']],
                    ['name'=>'Vendeur','emoji'=>'💼','bg'=>'bg-fuchsia-50','hover'=>'hover:border-fuchsia-300','check'=>'text-fuchsia-500',
                     'points'=>['Point de vente et encaissement','Consultation du catalogue produits','Gestion des fiches clients']],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($roles as $i => $r)
                    <div class="reveal rounded-2xl border border-slate-200 p-8 {{ $r['hover'] }} hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                         style="--reveal-delay: {{ $i * 100 }}ms">
                        <div class="w-14 h-14 rounded-2xl {{ $r['bg'] }} flex items-center justify-center text-2xl">
                            {{ $r['emoji'] }}
                        </div>
                        <h3 class="mt-5 text-xl font-bold text-slate-900">{{ $r['name'] }}</h3>
                        <ul class="mt-4 space-y-2 text-sm text-slate-600">
                            @foreach($r['points'] as $p)
                                <li class="flex items-start gap-2">
                                    <span class="{{ $r['check'] }} mt-0.5">✓</span>
                                    <span>{{ $p }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= À PROPOS ================= --}}
    <section id="about" class="max-w-7xl mx-auto px-6 py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <span class="text-sm font-semibold text-indigo-600">À PROPOS</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-bold text-slate-900">
                    Une application pensée pour les parfumeries modernes
                </h2>
                <p class="mt-5 text-slate-500">
                    Fini les cahiers et les calculs compliqués. Suivez votre stock en temps réel,
                    enregistrez vos ventes en quelques clics et gardez une vision claire de votre activité.
                </p>

                <ul class="mt-6 space-y-3">
                    @foreach([
                        'Gestion multi-rôles (Admin, Gérant, Vendeur)',
                        'Alertes automatiques de stock faible',
                        'Ticket de caisse imprimable',
                        'Rapports et exports CSV',
                        'Compatible téléphone, tablette et ordinateur',
                    ] as $f)
                        <li class="flex items-start gap-3 text-slate-700">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">✓</span>
                            {{ $f }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="reveal grid grid-cols-2 gap-4" style="--reveal-delay:150ms">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl p-6 text-white h-40 flex flex-col justify-between hover:scale-105 transition">
                    <div class="text-3xl">📦</div>
                    <div>
                        <div class="text-2xl font-bold">Stock</div>
                        <div class="text-xs opacity-80">Suivi en temps réel</div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-fuchsia-500 to-fuchsia-700 rounded-2xl p-6 text-white h-40 flex flex-col justify-between hover:scale-105 transition mt-6">
                    <div class="text-3xl">🛒</div>
                    <div>
                        <div class="text-2xl font-bold">Ventes</div>
                        <div class="text-xs opacity-80">Encaissement rapide</div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white h-40 flex flex-col justify-between hover:scale-105 transition">
                    <div class="text-3xl">📊</div>
                    <div>
                        <div class="text-2xl font-bold">Rapports</div>
                        <div class="text-xs opacity-80">Analyses instantanées</div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-2xl p-6 text-white h-40 flex flex-col justify-between hover:scale-105 transition mt-6">
                    <div class="text-3xl">👥</div>
                    <div>
                        <div class="text-2xl font-bold">Clients</div>
                        <div class="text-xs opacity-80">Fidélisation</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= CTA ================= --}}
    <section class="max-w-7xl mx-auto px-6 pb-24">
        <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-600 via-rose-600 to-orange-700 p-10 md:p-14 text-white">
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/10 blur-3xl animate-pulse-soft"></div>
            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold">Prêt à gérer votre parfumerie ?</h2>
                    <p class="mt-2 text-white/80 max-w-lg">
                        Connectez-vous dès maintenant et profitez de tous les modules immédiatement.
                    </p>
                </div>

                @auth
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
                       class="self-start px-6 py-3 rounded-xl bg-white text-slate-900 font-semibold hover:bg-slate-100 hover:scale-105 transition">
                        Accéder à l’application →
                    </a>
                @else
                    <div class="self-start flex gap-3">
                        <a href="{{ Route::has('login') ? route('login') : url('/login') }}"
                           class="px-6 py-3 rounded-xl bg-white text-slate-900 font-semibold hover:bg-slate-100 hover:scale-105 transition">
                            Se connecter →
                        </a>
                        <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                           class="px-6 py-3 rounded-xl bg-white/10 text-white border border-white/30 font-semibold hover:bg-white/20 transition">
                            Créer un compte
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </section>

    {{-- ================= FOOTER ================= --}}
    <footer class="border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-rose-500 flex items-center justify-center text-white font-bold text-xs">P</div>
                <span>© {{ date('Y') }} {{ $settings->company_name }} — Tous droits réservés.</span>
            </div>
            <div class="flex gap-5">
                <span>Laravel {{ app()->version() }}</span>
                <span>·</span>
                <span>PHP {{ PHP_VERSION }}</span>
            </div>
        </div>
    </footer>

    {{-- ================= SCROLL REVEAL ================= --}}
    <script>
        (function () {
            function initReveal() {
                const els = document.querySelectorAll('.reveal');
                if (!('IntersectionObserver' in window)) {
                    els.forEach(el => el.classList.add('visible'));
                    return;
                }
                const io = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

                els.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        el.classList.add('visible');
                    } else {
                        io.observe(el);
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initReveal);
            } else {
                initReveal();
            }
        })();
    </script>
</body>
</html>