<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #INV-{{ $order->id }}</title>
    <style>
        @page { margin: 28px; }
        :root { --text: #0f172a; --muted: #475569; --border: #d9e2ec; --soft: #f8fafc; --accent: #4338ca; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: var(--text); font-size: 13px; line-height: 1.45; }
        .page { position: relative; }
        .watermark { position: fixed; top: 24%; left: 12%; width: 430px; opacity: 0.08; z-index: -1; }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 18px; }
        .brand-row { width: 100%; }
        .brand-left, .brand-right { vertical-align: top; }
        .brand-right { text-align: right; }
        .brand-name { font-size: 24px; font-weight: 700; letter-spacing: 0.02em; }
        .brand-sub { color: var(--muted); font-size: 11px; margin-top: 4px; }
        .invoice-title { font-size: 22px; font-weight: 700; color: var(--accent); }
        .invoice-meta { color: var(--muted); font-size: 11px; margin-top: 4px; }
        .status { display: inline-block; margin-top: 10px; padding: 6px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-default { background: #e2e8f0; color: #334155; }
        .summary { margin-top: 22px; width: 100%; }
        .summary td { vertical-align: top; }
        .card { border: 1px solid var(--border); border-radius: 12px; background: rgba(248, 250, 252, 0.88); padding: 14px 16px; }
        .card + .card { margin-top: 12px; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: var(--muted); }
        .value { margin-top: 6px; font-size: 14px; font-weight: 700; }
        .muted { color: var(--muted); }
        .items { margin-top: 24px; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: var(--soft); color: var(--muted); font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; text-align: left; padding: 12px 14px; }
        tbody td, tfoot td { padding: 14px; border-top: 1px solid var(--border); }
        .right { text-align: right; }
        tfoot td { background: var(--soft); font-weight: 700; }
        .footer { margin-top: 22px; padding-top: 14px; border-top: 1px solid var(--border); color: var(--muted); font-size: 11px; }
    </style>
</head>
<body>
    <div class="page">
        @if ($watermarkLogoDataUri)
            <img class="watermark" src="{{ $watermarkLogoDataUri }}" alt="Watermark logo">
        @endif

        @php
            $statusClass = match ($order->status) {
                'paid' => 'status-paid',
                'pending' => 'status-pending',
                'cancelled' => 'status-cancelled',
                default => 'status-default',
            };
        @endphp

        <div class="header">
            <table class="brand-row">
                <tr>
                    <td class="brand-left">
                        @if (!empty($logoDataUri))
                            <img src="{{ $logoDataUri }}" alt="{{ config('app.name') }}" style="max-height: 48px; max-width: 220px; object-fit: contain;">
                        @else
                            <div class="brand-name">{{ config('app.name', 'iTechBD') }}</div>
                        @endif
                        <div class="brand-sub">System-generated course invoice</div>
                    </td>
                    <td class="brand-right">
                        <div class="invoice-title">Invoice #INV-{{ $order->id }}</div>
                        <div class="invoice-meta">Created {{ optional($order->created_at)->format('d M Y, h:i A') }}</div>
                        <span class="status {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="summary">
            <tr>
                <td style="width: 52%; padding-right: 10px;">
                    <div class="card">
                        <div class="label">Billed To</div>
                        <div class="value">{{ $user->name }}</div>
                        <div class="muted">{{ $user->email }}</div>
                    </div>
                    <div class="card">
                        <div class="label">Course</div>
                        <div class="value">{{ $order->course?->title ?? '—' }}</div>
                        <div class="muted">Batch: {{ $order->batch?->name ?? '—' }}</div>
                    </div>
                </td>
                <td style="width: 48%; padding-left: 10px;">
                    <div class="card">
                        <div class="label">Invoice Date</div>
                        <div class="value">{{ optional($order->created_at)->format('d M Y') }}</div>
                    </div>
                    <div class="card">
                        <div class="label">Total</div>
                        <div class="value">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Course enrollment{{ $order->course ? ': ' . $order->course->title : '' }}
                            @if ($order->batch)
                                <span class="muted">(Batch: {{ $order->batch->name }})</span>
                            @endif
                        </td>
                        <td class="right">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="right">Total</td>
                        <td class="right">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer">
            This invoice is generated by the system for record keeping. If there is any discrepancy, please contact support.
            <div style="margin-top: 8px; font-weight: 700; color: #334155;">System Generated Invoice</div>
            <div style="margin-top: 4px; font-weight: 700; color: #334155;">Signature Not Required</div>
        </div>
    </div>
</body>
</html>