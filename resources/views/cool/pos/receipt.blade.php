<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->sale_no }} · Cool Stationary</title>
    <style>
        :root {
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: var(--ink);
            background: #f3f4f6;
        }
        .wrap {
            max-width: 420px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
        }
        .actions {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .btn {
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 999px;
            padding: 8px 14px;
            text-decoration: none;
            color: var(--ink);
            font-size: 14px;
            cursor: pointer;
        }
        .btn-primary {
            background: #0f766e;
            border-color: #0f766e;
            color: #fff;
        }
        h1 {
            margin: 0;
            font-size: 22px;
        }
        .muted { color: var(--muted); font-size: 13px; }
        .header {
            text-align: center;
            margin-bottom: 18px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 4px 0;
            font-size: 14px;
        }
        .items {
            border-top: 1px dashed var(--line);
            border-bottom: 1px dashed var(--line);
            margin: 16px 0;
            padding: 12px 0;
        }
        .item {
            margin-bottom: 10px;
        }
        .item:last-child { margin-bottom: 0; }
        .totals .row { font-size: 14px; }
        .totals .total {
            font-size: 18px;
            font-weight: 700;
            margin-top: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: var(--muted);
        }
        @media print {
            body { background: #fff; }
            .wrap { border: 0; margin: 0; max-width: none; border-radius: 0; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    @php
        $currency = optional($sale->shop)->currency_code ?: 'MMK';
        $fmt = fn ($value) => number_format((float) $value, 0) . ' ' . $currency;
    @endphp

    <div class="wrap">
        <div class="actions">
            <button class="btn btn-primary" type="button" onclick="window.print()">Print slip</button>
            @if(auth()->user()->hasRole('manager', 'cashier'))
                <a class="btn" href="{{ route('cool.pos.index') }}">Back to POS</a>
            @endif
            @if(auth()->user()->hasRole('owner', 'manager'))
                <a class="btn" href="{{ route('cool.dashboard') }}">Dashboard</a>
            @endif
        </div>

        <div class="header">
            <h1>Cool Stationary</h1>
            @if(optional($sale->shop)->receipt_header)
                <div class="muted">{{ $sale->shop->receipt_header }}</div>
            @endif
            <div class="muted">Sale {{ $sale->sale_no }}</div>
            <div class="muted">{{ optional($sale->sold_at)->format('d M Y, h:i A') }}</div>
        </div>

        <div class="row"><span>Cashier</span><span>{{ optional($sale->cashier)->name }} ({{ optional($sale->cashier)->login_id }})</span></div>
        <div class="row"><span>Customer</span><span>{{ optional($sale->customer)->name ?: 'Walk-in' }}</span></div>
        <div class="row"><span>Payment</span><span class="capitalize">{{ $sale->payment_status }}</span></div>

        <div class="items">
            @foreach ($sale->items as $item)
                <div class="item">
                    <div class="row">
                        <strong>{{ $item->description }}</strong>
                        <strong>{{ $fmt($item->line_total) }}</strong>
                    </div>
                    <div class="muted">{{ rtrim(rtrim(number_format((float) $item->quantity_base, 3), '0'), '.') }} × {{ $fmt($item->unit_price) }}</div>
                </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="row"><span>Subtotal</span><span>{{ $fmt($sale->subtotal) }}</span></div>
            @if((float) $sale->tax_amount > 0)
                <div class="row">
                    <span>Tax @if(optional($sale->shop)->tax_rate)({{ rtrim(rtrim(number_format((float) $sale->shop->tax_rate, 2), '0'), '.') }}%)@endif</span>
                    <span>{{ $fmt($sale->tax_amount) }}</span>
                </div>
            @endif
            <div class="row total"><span>Total</span><span>{{ $fmt($sale->total) }}</span></div>
            <div class="row"><span>Paid</span><span>{{ $fmt($sale->amount_paid) }}</span></div>
            @if((float) $sale->amount_due > 0)
                <div class="row"><span>Credit due</span><span>{{ $fmt($sale->amount_due) }}</span></div>
            @endif
        </div>

        <div class="footer">
            @if(optional($sale->shop)->receipt_footer)
                <div>{{ $sale->shop->receipt_footer }}</div>
            @else
                <div>Thank you for shopping with Cool Stationary.</div>
            @endif
        </div>
    </div>
</body>
</html>
