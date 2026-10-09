<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings->company_name }} — @yield('title', 'Dashboard')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { --brand-primary: {{ $settings->primary_color }}; }
        [x-cloak] { display: none !important; }
        html, body { font-family: 'Inter', sans-serif; }
        a, button, input, select, textarea {
            transition-property: color, background-color, border-color, box-shadow, opacity, transform;
            transition-duration: 180ms;
            transition-timing-function: cubic-bezier(.2,.75,.25,1);
        }
        .bg-indigo-600 { background-color: var(--brand-primary) !important; }
        .hover\:bg-indigo-700:hover { background-color: color-mix(in srgb, var(--brand-primary), #000 12%) !important; }
        .text-indigo-600, .text-indigo-700 { color: var(--brand-primary) !important; }
        .border-indigo-400, .border-indigo-500 { border-color: var(--brand-primary) !important; }
        .focus\:ring-indigo-500:focus { --tw-ring-color: var(--brand-primary) !important; }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: no-preference) {
            .animate-enter { animation: fade-in-up .45s ease-out both; animation-delay: var(--enter-delay, 0ms); }
            .page-content { animation: fade-in-up .32s ease-out both; }
            .page-content tbody tr { animation: fade-in-up .28s ease-out both; }
            .page-content tbody tr:nth-child(2) { animation-delay: 25ms; }
            .page-content tbody tr:nth-child(3) { animation-delay: 50ms; }
            .page-content tbody tr:nth-child(4) { animation-delay: 75ms; }
            .page-content tbody tr:nth-child(5) { animation-delay: 100ms; }
            .page-content tbody tr:nth-child(6) { animation-delay: 125ms; }
            .page-content tbody tr:nth-child(7) { animation-delay: 150ms; }
            .page-content tbody tr:nth-child(8) { animation-delay: 175ms; }
            .page-content article { animation: fade-in-up .36s ease-out both; animation-delay: var(--enter-delay, 0ms); }
            .page-content a:hover, .page-content button:hover:not(:disabled) { transform: translateY(-1px); }
            .page-content button:active:not(:disabled) { transform: scale(.98); }
            .page-content article:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -16px rgb(15 23 42 / .35); }
            .sidebar-link span:first-child { display: inline-block; transition: transform .2s ease; }
            .sidebar-link:hover span:first-child { transform: scale(1.12) rotate(-5deg); }
            .page-content input:focus, .page-content select:focus, .page-content textarea:focus {
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary), transparent 82%);
            }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-700"
      x-data="{ open: false }">

<div class="min-h-full flex">

    {{-- ============ SIDEBAR ============ --}}
    <aside :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transform transition-transform duration-200 lg:static lg:translate-x-0 overflow-y-auto">

        {{-- Logo --}}
        <div class="flex items-center gap-3 h-16 px-5 border-b border-slate-800">
            @if($settings->logo_url)
                <img src="{{ $settings->logo_url }}" alt="{{ $settings->company_name }}" class="h-9 w-9 rounded-lg bg-white object-contain p-1">
            @else
                <div class="flex h-9 w-9 items-center justify-center rounded-lg text-lg font-bold text-white" style="background-color: var(--brand-primary)">
                    {{ mb_substr($settings->company_name, 0, 1) }}
                </div>
            @endif
            <div>
                <div class="max-w-40 truncate text-sm font-semibold leading-tight text-white">{{ $settings->company_name }}</div>
                <div class="max-w-40 truncate text-[11px] text-slate-500">{{ $settings->tagline ?: 'Gestion de stock' }}</div>
            </div>
        </div>

        @php
            $link = function($routeName, $label, $icon, $activePattern = null) {
                $isActive = request()->routeIs($activePattern ?? $routeName);
                $url = \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : '#';
                return '<a href="'.$url.'" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg transition '.($isActive ? 'bg-indigo-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white').'"><span>'.$icon.'</span><span>'.$label.'</span></a>';
            };
        @endphp

        <nav class="px-3 py-4 space-y-1 text-sm">

            @can('dashboard.view')
                <div class="pt-1 pb-1 px-3 text-[11px] uppercase tracking-wider text-slate-500">Principal</div>
                {!! $link('dashboard', 'Tableau de bord', '🏠') !!}
            @endcan

            {{-- Catalogue --}}
            @can('products.view')
                <div class="pt-4 pb-1 px-3 text-[11px] uppercase tracking-wider text-slate-500">Catalogue</div>
                {!! $link('products.index', 'Parfums', '🧴', 'products.*') !!}
                {!! $link('brands.index', 'Marques', '🏷️', 'brands.*') !!}
                {!! $link('categories.index', 'Catégories', '📂', 'categories.*') !!}
            @endcan

            {{-- Opérations --}}
            @canany(['stock.view', 'sales.view', 'sales.create', 'purchases.view'])
                <div class="pt-4 pb-1 px-3 text-[11px] uppercase tracking-wider text-slate-500">Opérations</div>
                @can('stock.view')
                    {!! $link('stock.index', 'Stock', '📦', 'stock.index') !!}
                    {!! $link('stock.lots', 'Suivi des lots', '🏷️', 'stock.lots') !!}
                @endcan
                @can('sales.view')
                    {!! $link('sales.index', 'Ventes', '🛒', 'sales.*') !!}
                @endcan
                @can('purchases.view')
                    {!! $link('purchases.index', 'Achats', '🚚', 'purchases.*') !!}
                @endcan
            @endcanany

            {{-- Tiers --}}
            @canany(['customers.view', 'suppliers.view'])
                <div class="pt-4 pb-1 px-3 text-[11px] uppercase tracking-wider text-slate-500">Tiers</div>
                @can('customers.view')
                    {!! $link('customers.index', 'Clients', '👥', 'customers.*') !!}
                @endcan
                @can('suppliers.view')
                    {!! $link('suppliers.index', 'Fournisseurs', '🏭', 'suppliers.*') !!}
                @endcan
            @endcanany

            {{-- Système --}}
            @canany(['reports.view', 'users.manage'])
                <div class="pt-4 pb-1 px-3 text-[11px] uppercase tracking-wider text-slate-500">Système</div>
                @can('reports.view')
                    {!! $link('reports.index', 'Rapports', '📊', 'reports.*') !!}
                @endcan
                @can('users.manage')
                    {!! $link('users.index', 'Utilisateurs', '👤', 'users.*') !!}
                    {!! $link('settings.index', 'Paramètres', '⚙️', 'settings.*') !!}
                @endcan
            @endcanany
        </nav>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="open" @click="open = false" x-cloak x-transition.opacity
         class="fixed inset-0 bg-black/40 z-30 lg:hidden"></div>

    {{-- ============ CONTENU ============ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Header --}}
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button @click="open = !open" class="lg:hidden p-2 rounded-lg hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-800">@yield('title', 'Tableau de bord')</h1>
            </div>

            <div class="flex items-center gap-3">
                @can('sales.create')
                    <a href="{{ route('sales.create') }}"
                       class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        ➕ Nouvelle vente
                    </a>
                @endcan

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="text-sm font-medium leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-slate-500 leading-tight">
                                {{ method_exists(auth()->user(), 'getRoleNames') ? (auth()->user()->getRoleNames()->first() ?? 'Utilisateur') : 'Utilisateur' }}
                            </div>
                        </div>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg py-1">
                        @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">Mon profil</a>
                        @endif
                        @can('users.manage')
                            <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">⚙️ Paramètres</a>
                        @endcan
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Contenu principal --}}
        <main class="page-content flex-1 p-4 lg:p-6">

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition.opacity
                     class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200 text-sm">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>