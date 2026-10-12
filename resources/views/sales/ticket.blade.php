<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticket {{ $sale->reference }} — {{ $settings->company_name }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <style>
        :root {
            --brand-primary: {{ $settings->primary_color ?? '#6366f1' }};
            --brand-dark: #0f172a;
            --brand-soft: #f8fafc;
            --brand-border: #e2e8f0;
            --brand-muted: #64748b;
            --brand-success: #10b981;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', system-ui, -apple-system, Arial, sans-serif;
            background:
                radial-gradient(circle at 15% 20%, color-mix(in srgb, var(--brand-primary) 10%, transparent), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(99, 102, 241, .12), transparent 45%),
                linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #172033;
            min-height: 100vh;
            padding: 32px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ============= ANIMATIONS ============= */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(.92); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes slideRight {
            from { opacity: 0; transform: translateX(-16px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes popIn {
            0%   { opacity: 0; transform: scale(.5); }
            60%  { transform: scale(1.08); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        @keyframes pulseRing {
            0%   { box-shadow: 0 0 0 0 color-mix(in srgb, var(--brand-success) 50%, transparent); }
            70%  { box-shadow: 0 0 0 12px color-mix(in srgb, var(--brand-success) 0%, transparent); }
            100% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--brand-success) 0%, transparent); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        @keyframes drawLine {
            from { transform: scaleX(0); }
            to   { transform: scaleX(1); }
        }

        /* ============= CONTENEUR TICKET ============= */
        .ticket {
            position: relative;
            width: min(100%, 400px);
            background: #fff;
            border-radius: 20px;
            padding: 0;
            box-shadow:
                0 1px 3px rgba(15, 23, 42, .04),
                0 20px 50px -12px rgba(15, 23, 42, .18);
            animation: fadeUp .7s cubic-bezier(.2,.8,.2,1) both;
            overflow: hidden;
        }

        /* Bande de marque en haut */
        .brand-band {
            height: 6px;
            background: linear-gradient(90deg,
                var(--brand-primary) 0%,
                color-mix(in srgb, var(--brand-primary) 60%, #d946ef) 50%,
                var(--brand-primary) 100%);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        .ticket-inner { padding: 28px 26px 24px; }

        /* ============= HEADER ============= */
        .ticket-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--brand-border);
            animation: scaleIn .6s cubic-bezier(.2,.8,.2,1) both;
        }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 76px;
            height: 76px;
            border-radius: 20px;
            background: linear-gradient(135deg,
                color-mix(in srgb, var(--brand-primary) 15%, #fff),
                color-mix(in srgb, var(--brand-primary) 5%, #fff));
            box-shadow: 0 8px 20px -8px color-mix(in srgb, var(--brand-primary) 40%, transparent);
            margin-bottom: 14px;
            animation: float 4s ease-in-out infinite;
            overflow: hidden;
        }

        .logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .logo-letter {
            font-size: 32px;
            font-weight: 800;
            color: var(--brand-primary);
            letter-spacing: -1px;
        }

        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--brand-dark);
            letter-spacing: -.4px;
            margin-bottom: 3px;
        }

        .tagline {
            font-size: 12px;
            color: var(--brand-muted);
            font-weight: 500;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .ticket-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 5px 12px;
            border-radius: 20px;
            background: color-mix(in srgb, var(--brand-primary) 10%, #fff);
            color: var(--brand-primary);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            animation: popIn .6s .2s cubic-bezier(.2,.8,.2,1) both;
        }

        .contact-line {
            margin-top: 10px;
            font-size: 12px;
            color: var(--brand-muted);
            animation: fadeUp .6s .25s cubic-bezier(.2,.8,.2,1) both;
        }

        /* ============= INFOS VENTE ============= */
        .sale-meta {
            padding: 18px 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            animation: fadeUp .6s .3s cubic-bezier(.2,.8,.2,1) both;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .meta-label {
            font-size: 10px;
            color: var(--brand-muted);
            letter-spacing: .8px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .meta-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--brand-dark);
            word-break: break-word;
        }

        .meta-value.mono {
            font-family: ui-monospace, 'SF Mono', Menlo, monospace;
            font-size: 12px;
            color: var(--brand-primary);
            font-weight: 700;
        }

        /* ============= ARTICLES ============= */
        .items-section {
            border-top: 2px dashed var(--brand-border);
            padding-top: 16px;
            animation: fadeUp .6s .35s cubic-bezier(.2,.8,.2,1) both;
        }

        .items-title {
            font-size: 10px;
            color: var(--brand-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .item-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 12px;
            align-items: start;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            animation: slideRight .5s cubic-bezier(.2,.8,.2,1) both;
        }

        .item-row:last-child { border-bottom: none; }

        .item-name {
            font-weight: 600;
            color: var(--brand-dark);
            font-size: 13px;
            margin-bottom: 2px;
        }

        .item-price {
            font-size: 11px;
            color: var(--brand-muted);
        }

        .item-qty {
            font-size: 12px;
            font-weight: 600;
            color: var(--brand-muted);
            padding: 2px 8px;
            background: var(--brand-soft);
            border-radius: 6px;
            align-self: start;
        }

        .item-subtotal {
            font-weight: 700;
            color: var(--brand-dark);
            font-size: 13px;
            white-space: nowrap;
        }

        /* ============= TOTAUX ============= */
        .totals-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px dashed var(--brand-border);
            animation: fadeUp .6s .45s cubic-bezier(.2,.8,.2,1) both;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #334155;
        }

        .total-row.discount { color: #dc2626; }
        .total-row.tax { color: var(--brand-muted); font-size: 12px; }

        .grand-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            padding: 16px 18px;
            border-radius: 14px;
            background: linear-gradient(135deg,
                color-mix(in srgb, var(--brand-primary) 8%, #fff),
                color-mix(in srgb, var(--brand-primary) 3%, #fff));
            border: 1.5px solid color-mix(in srgb, var(--brand-primary) 20%, transparent);
            animation: popIn .7s .55s cubic-bezier(.2,.8,.2,1) both;
        }

        .grand-total-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--brand-primary);
        }

        .grand-total-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--brand-primary);
            letter-spacing: -.5px;
            animation: popIn .8s .7s cubic-bezier(.2,.8,.2,1) both;
        }

        .payment-info {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--brand-soft);
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .payment-info .label {
            color: var(--brand-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .6px;
        }

        .payment-info .amount {
            font-weight: 700;
            color: var(--brand-dark);
            font-size: 13px;
        }

        .change-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--brand-success) 12%, #fff);
            color: #047857;
            font-size: 12px;
            font-weight: 700;
            animation: popIn .6s .8s cubic-bezier(.2,.8,.2,1) both;
        }

        .change-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--brand-success);
            animation: pulseRing 1.8s infinite;
        }

        /* ============= NOTE ============= */
        .note-box {
            margin-top: 16px;
            padding: 12px 14px;
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            border-radius: 8px;
            font-size: 12px;
            color: #78350f;
            animation: slideRight .6s .85s cubic-bezier(.2,.8,.2,1) both;
        }

        .note-box strong {
            display: block;
            font-size: 10px;
            letter-spacing: .8px;
            text-transform: uppercase;
            margin-bottom: 4px;
            color: #b45309;
        }

        /* ============= FOOTER ============= */
        .ticket-footer {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 2px dashed var(--brand-border);
            text-align: center;
            animation: fadeUp .6s .95s cubic-bezier(.2,.8,.2,1) both;
        }

        .thank-you {
            font-size: 14px;
            font-weight: 700;
            color: var(--brand-dark);
            letter-spacing: -.2px;
        }

        .footer-sub {
            font-size: 11px;
            color: var(--brand-muted);
            margin-top: 4px;
        }

        .barcode {
            margin-top: 14px;
            height: 40px;
            background-image: repeating-linear-gradient(
                90deg,
                var(--brand-dark) 0,
                var(--brand-dark) 2px,
                transparent 2px,
                transparent 3px,
                var(--brand-dark) 3px,
                var(--brand-dark) 6px,
                transparent 6px,
                transparent 8px
            );
            opacity: .8;
            border-radius: 3px;
            animation: fadeUp .6s 1.05s cubic-bezier(.2,.8,.2,1) both;
        }

        .barcode-text {
            font-family: ui-monospace, monospace;
            font-size: 10px;
            letter-spacing: 3px;
            color: var(--brand-muted);
            margin-top: 4px;
            animation: fadeUp .6s 1.1s cubic-bezier(.2,.8,.2,1) both;
        }

        /* ============= ACTIONS ============= */
        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            width: min(100%, 400px);
            animation: fadeUp .7s .4s cubic-bezier(.2,.8,.2,1) both;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all .25s cubic-bezier(.2,.8,.2,1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg,
                var(--brand-primary),
                color-mix(in srgb, var(--brand-primary) 75%, #4338ca));
            color: #fff;
            box-shadow: 0 8px 20px -6px color-mix(in srgb, var(--brand-primary) 50%, transparent);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px -6px color-mix(in srgb, var(--brand-primary) 60%, transparent);
        }

        .btn-primary:active { transform: translateY(0); }

        .btn-secondary {
            background: #fff;
            color: var(--brand-dark);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .08), 0 4px 12px rgba(15, 23, 42, .06);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
        }

        .btn svg {
            width: 16px;
            height: 16px;
            transition: transform .25s;
        }

        .btn:hover svg { transform: scale(1.15); }

        /* Success toast */
        .toast {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: #fff;
            border-radius: 14px;
            padding: 14px 20px;
            box-shadow: 0 20px 40px -12px rgba(15, 23, 42, .25);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #065f46;
            animation: slideDown .6s cubic-bezier(.2,.8,.2,1) forwards;
            z-index: 100;
        }

        .toast.hide {
            animation: slideUp .4s cubic-bezier(.4,0,1,1) forwards;
        }

        .toast::before {
            content: '';
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--brand-success);
            position: relative;
            flex-shrink: 0;
        }

        .toast::after {
            content: '✓';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
        }

        @keyframes slideDown {
            from { transform: translateX(-50%) translateY(-100px); opacity: 0; }
            to   { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateX(-50%) translateY(0); opacity: 1; }
            to   { transform: translateX(-50%) translateY(-100px); opacity: 0; }
        }

        /* ============= IMPRESSION ============= */
        @media print {
            body {
                background: #fff;
                padding: 0;
                gap: 0;
                display: block;
            }
            .actions, .toast { display: none !important; }
            .ticket {
                width: 100%;
                max-width: 320px;
                margin: 0 auto;
                box-shadow: none;
                border-radius: 0;
                animation: none;
            }
            .brand-band { display: none; }
            .ticket-inner { padding: 12px; }
            * { animation: none !important; }
        }

        @media (max-width: 480px) {
            body { padding: 16px 10px; }
            .ticket-inner { padding: 22px 18px 20px; }
            .grand-total-value { font-size: 20px; }
            .sale-meta { grid-template-columns: 1fr; }
            .actions { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>

    {{-- Toast succès --}}
    @if(session('success'))
        <div class="toast" id="toast">{{ session('success') }}</div>
    @endif

    {{-- ============ TICKET ============ --}}
    <main class="ticket" id="ticket">

        <div class="brand-band"></div>

        <div class="ticket-inner">

            {{-- Header --}}
            <header class="ticket-header">
                <div class="logo-wrap">
                    @if($settings->logo_url)
                        <img src="{{ $settings->logo_url }}" alt="{{ $settings->company_name }}">
                    @else
                        <span class="logo-letter">{{ strtoupper(substr($settings->company_name, 0, 1)) }}</span>
                    @endif
                </div>

                <h1 class="company-name">{{ $settings->company_name }}</h1>
                @if($settings->tagline)
                    <p class="tagline">{{ $settings->tagline }}</p>
                @endif

                <span class="ticket-type">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                    Ticket de vente
                </span>

                @if($settings->address || $settings->city || $settings->phone)
                    <p class="contact-line">
                        {{ collect([$settings->address, $settings->city, $settings->phone])->filter()->join(' · ') }}
                    </p>
                @endif
            </header>

            {{-- Infos vente --}}
            <div class="sale-meta">
                <div class="meta-item">
                    <span class="meta-label">Référence</span>
                    <span class="meta-value mono">{{ $sale->reference }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Date</span>
                    <span class="meta-value">{{ company_date($sale->sold_at, true) }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Client</span>
                    <span class="meta-value">{{ $sale->customer?->name ?? 'Client de passage' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Vendeur</span>
                    <span class="meta-value">{{ $sale->user?->name ?? '—' }}</span>
                </div>
            </div>

            {{-- Articles --}}
            <section class="items-section">
                <div class="items-title">Articles</div>

                @foreach($sale->items as $index => $item)
                    <div class="item-row" style="animation-delay: {{ 0.4 + ($index * 0.06) }}s">
                        <div>
                            <div class="item-name">{{ $item->product_name }}</div>
                            <div class="item-price">{{ money($item->price) }} / unité</div>
                        </div>
                        <span class="item-qty">×{{ $item->quantity }}</span>
                        <span class="item-subtotal">{{ money($item->subtotal) }}</span>
                    </div>
                @endforeach
            </section>

            {{-- Totaux --}}
            <section class="totals-section">
                <div class="total-row">
                    <span>Sous-total</span>
                    <span>{{ money($sale->subtotal) }}</span>
                </div>

                @if($sale->discount > 0)
                    <div class="total-row discount">
                        <span>Remise</span>
                        <span>− {{ money($sale->discount) }}</span>
                    </div>
                @endif

                @if($sale->vat_rate > 0)
                    <div class="total-row tax">
                        <span>TVA ({{ rtrim(rtrim(number_format((float) $sale->vat_rate, 2, '.', ''), '0'), '.') }} %)</span>
                        <span>{{ money($sale->vat_amount) }}</span>
                    </div>
                @endif

                <div class="grand-total">
                    <span class="grand-total-label">Total à payer</span>
                    <span class="grand-total-value">{{ money($sale->total) }}</span>
                </div>

                <div class="payment-info">
                    <span class="label">Payé par {{ $sale->payment_label }}</span>
                    <span class="amount">{{ money($sale->paid_amount) }}</span>
                </div>

                @if($sale->change_amount > 0)
                    <div class="change-badge">
                        Monnaie rendue : {{ money($sale->change_amount) }}
                    </div>
                @endif
            </section>

            {{-- Note --}}
            @if($sale->notes)
                <div class="note-box">
                    <strong>Note</strong>
                    {{ $sale->notes }}
                </div>
            @endif

            {{-- Footer --}}
            <footer class="ticket-footer">
                <p class="thank-you">Merci de votre confiance !</p>
                <p class="footer-sub">À bientôt dans notre boutique 🌸</p>

                <div class="barcode"></div>
                <div class="barcode-text">{{ $sale->reference }}</div>
            </footer>

        </div>
    </main>

    {{-- ============ ACTIONS ============ --}}
    <div class="actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z"/>
            </svg>
            Imprimer
        </button>

        <a href="{{ route('sales.create') }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle vente
        </a>

        <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Détails
        </a>
    </div>

    <script>
        // Auto-hide toast après 4s
        const toast = document.getElementById('toast');
        if (toast) {
            setTimeout(() => toast.classList.add('hide'), 4000);
            setTimeout(() => toast.remove(), 4500);
        }

        // Impression avec titre propre
        window.addEventListener('beforeprint', () => {
            document.title = 'Ticket {{ $sale->reference }}';
        });

        // Raccourci clavier : Ctrl+P → imprime directement
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>

</body>
</html>