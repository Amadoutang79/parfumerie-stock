<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Connexion') — {{ config('app.name', 'Parfumerie Stock') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        html, body { font-family: 'Inter', sans-serif; }

        /* ---------- KEYFRAMES ---------- */
        @keyframes float {
            0%,100% { transform: translateY(0) rotate(0deg); }
            50%     { transform: translateY(-20px) rotate(5deg); }
        }
        @keyframes floatSlow {
            0%,100% { transform: translateY(0) translateX(0); }
            50%     { transform: translateY(-30px) translateX(20px); }
        }
        @keyframes gradientMove {
            0%,100% { background-position: 0% 50%; }
            50%     { background-position: 100% 50%; }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(.9); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes pulseSoft {
            0%,100% { opacity: .6; transform: scale(1); }
            50%     { opacity: 1; transform: scale(1.05); }
        }
        @keyframes shimmer {
            0%   { background-position: -500px 0; }
            100% { background-position: 500px 0; }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%,60% { transform: translateX(-8px); }
            40%,80% { transform: translateX(8px); }
        }

        /* ---------- CLASSES UTILITAIRES ---------- */
        .animate-float        { animation: float 7s ease-in-out infinite; }
        .animate-float-slow   { animation: floatSlow 12s ease-in-out infinite; }
        .animate-fade-up      { animation: fadeUp .7s cubic-bezier(.2,.8,.2,1) both; }
        .animate-fade-in      { animation: fadeIn .8s ease-out both; }
        .animate-scale-in     { animation: scaleIn .5s cubic-bezier(.2,.8,.2,1) both; }
        .animate-pulse-soft   { animation: pulseSoft 4s ease-in-out infinite; }
        .animate-slide-left   { animation: slideInLeft .6s cubic-bezier(.2,.8,.2,1) both; }
        .animate-shake        { animation: shake .4s ease-in-out; }

        .bg-animated-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 25%, #4c1d95 50%, #831843 75%, #0f172a 100%);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
        }

        .bg-grid {
            background-image:
                linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .glass {
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255,255,255,.1);
        }

        .glass-input {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            transition: all .3s ease;
        }
        .glass-input:focus {
            background: rgba(255,255,255,.1);
            border-color: rgba(217,70,239,.6);
            box-shadow: 0 0 0 4px rgba(217,70,239,.15);
            outline: none;
        }

        .gradient-text {
            background: linear-gradient(90deg, #e879f9, #818cf8, #f472b6, #e879f9);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradientMove 8s ease infinite;
        }

        .btn-gradient {
            background: linear-gradient(90deg, #d946ef, #6366f1, #d946ef);
            background-size: 200% 100%;
            transition: all .4s ease;
        }
        .btn-gradient:hover {
            background-position: 100% 0;
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -12px rgba(217,70,239,.5);
        }
        .btn-gradient:active {
            transform: translateY(0);
        }

        /* Input focus animation */
        .input-wrapper { position: relative; }
        .input-wrapper .input-line {
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #d946ef, #6366f1);
            transition: all .3s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        .input-wrapper:focus-within .input-line {
            width: calc(100% - 24px);
        }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            transition: all .3s ease;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-animated-gradient">

    {{-- Décor de fond --}}
    <div class="fixed inset-0 bg-grid opacity-50 pointer-events-none"></div>

    {{-- Orbes flottants --}}
    <div class="fixed -top-40 -left-40 w-[600px] h-[600px] rounded-full bg-fuchsia-500/15 blur-3xl animate-float-slow pointer-events-none"></div>
    <div class="fixed -bottom-40 -right-40 w-[600px] h-[600px] rounded-full bg-indigo-500/15 blur-3xl animate-float-slow pointer-events-none" style="animation-delay:3s"></div>
    <div class="fixed top-1/3 right-1/4 w-72 h-72 rounded-full bg-purple-500/10 blur-3xl animate-pulse-soft pointer-events-none"></div>
    <div class="fixed bottom-1/4 left-1/4 w-80 h-80 rounded-full bg-pink-500/10 blur-3xl animate-pulse-soft pointer-events-none" style="animation-delay:2s"></div>

    {{-- Particules --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        @for($i = 0; $i < 20; $i++)
            <div class="absolute w-1 h-1 rounded-full bg-white/20 animate-float"
                 style="top: {{ rand(0, 100) }}%; left: {{ rand(0, 100) }}%; animation-duration: {{ rand(6, 15) }}s; animation-delay: {{ rand(0, 5) }}s;"></div>
        @endfor
    </div>

    <div class="relative min-h-screen flex flex-col">

        {{-- Header --}}
        <header class="px-6 py-5 animate-fade-in">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-fuchsia-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-fuchsia-500/30 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                    P
                </div>
                <div>
                    <div class="font-bold text-white leading-tight">Parfumerie</div>
                    <div class="text-[11px] text-white/60 leading-tight">Gestion de stock</div>
                </div>
            </a>
        </header>

        {{-- Contenu --}}
        <main class="flex-1 flex items-center justify-center px-6 py-8">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </main>

        {{-- Footer --}}
        <footer class="px-6 py-5 text-center text-xs text-white/40 animate-fade-in" style="animation-delay:.3s">
            © {{ date('Y') }} Parfumerie Stock — Tous droits réservés.
        </footer>
    </div>

</body>
</html>