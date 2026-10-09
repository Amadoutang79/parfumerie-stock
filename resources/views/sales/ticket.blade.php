<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket {{ $sale->reference }}</title>
    <style>
        :root { --brand-primary: {{ $settings->primary_color }}; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; color: #172033; font: 14px/1.45 Arial, sans-serif; }
        .ticket { width: min(100%, 380px); margin: 0 auto; }
        .center { text-align: center; }
        .muted { color: #64748b; }
        .rule { margin: 16px 0; border: 0; border-top: 1px dashed #cbd5e1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px 0; text-align: left; vertical-align: top; }
        th:last-child, td:last-child { text-align: right; }
        .totals > div { display: flex; justify-content: space-between; padding: 4px 0; }
        .grand-total { border-top: 1px solid #cbd5e1; margin-top: 5px; padding-top: 10px !important; font-size: 18px; font-weight: 700; }
        .actions { display: flex; justify-content: center; gap: 8px; margin: 20px auto; }
        button, a { border: 0; border-radius: 8px; padding: 9px 14px; background: var(--brand-primary); color: #fff; font: inherit; text-decoration: none; cursor: pointer; }
        @media print {
            body { padding: 0; }
            .actions { display: none; }
            .ticket { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="ticket">
        <header class="center">
            @if($settings->logo_url)
                <img src="{{ $settings->logo_url }}" alt="{{ $settings->company_name }}" style="max-width: 100px; max-height: 80px; object-fit: contain">
            @endif
            <h1>{{ $settings->company_name }}</h1>
            @if($settings->tagline)<p class="muted">{{ $settings->tagline }}</p>@endif
            <p class="muted">Ticket de vente</p>
        </header>
        @if($settings->address || $settings->city || $settings->phone)
            <p class="center muted">{{ collect([$settings->address, $settings->city, $settings->phone])->filter()->join(' · ') }}</p>
        @endif
        <hr class="rule">
        <p><strong>Référence :</strong> {{ $sale->reference }}<br>
            <strong>Date :</strong> {{ company_date($sale->sold_at, true) }}<br>
            <strong>Client :</strong> {{ $sale->customer?->name ?? 'Client de passage' }}<br>
            <strong>Vendeur :</strong> {{ $sale->user?->name ?? '—' }}</p>
        <hr class="rule">
        <table>
            <thead><tr><th>Article</th><th>Qté</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}<br><span class="muted">{{ money($item->price) }} / unité</span></td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ money($item->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr class="rule">
        <div class="totals">
            <div><span>Sous-total</span><span>{{ money($sale->subtotal) }}</span></div>
            <div><span>Remise</span><span>- {{ money($sale->discount) }}</span></div>
            @if($sale->vat_rate > 0)
                <div><span>TVA ({{ rtrim(rtrim(number_format((float) $sale->vat_rate, 2, '.', ''), '0'), '.') }}%)</span><span>{{ money($sale->vat_amount) }}</span></div>
            @endif
            <div class="grand-total"><span>Total</span><span>{{ money($sale->total) }}</span></div>
            <div><span>Paiement ({{ $sale->payment_label }})</span><span>{{ money($sale->paid_amount) }}</span></div>
            @if($sale->change_amount > 0)
                <div><span>Monnaie rendue</span><span>{{ money($sale->change_amount) }}</span></div>
            @endif
        </div>
        @if($sale->notes)
            <hr class="rule"><p><strong>Note :</strong> {{ $sale->notes }}</p>
        @endif
        <p class="center muted">Merci de votre confiance !</p>
    </main>
    <div class="actions"><button type="button" onclick="window.print()">Imprimer le ticket</button><a href="{{ route('sales.show', $sale) }}">Retour à la vente</a></div>
</body>
</html>
