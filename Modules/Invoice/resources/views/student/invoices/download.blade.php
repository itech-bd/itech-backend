<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #INV-{{ $order->id }}</title>
    <style>
        :root { --text: #0f172a; --muted: #475569; --border: #e2e8f0; --bg: #ffffff; --soft: #f8fafc; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif; color: var(--text); background: var(--bg); }
        .container { max-width: 860px; margin: 0 auto; }
        .card { border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
        .header { padding: 18px 20px; background: var(--soft); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .title { font-size: 18px; font-weight: 700; margin: 0; }
        .sub { font-size: 12px; color: var(--muted); margin-top: 6px; }
        .section { padding: 20px; }
        .row { display: flex; gap: 18px; flex-wrap: wrap; justify-content: space-between; }
        .box { min-width: 240px; padding: 12px 14px; border: 1px solid var(--border); border-radius: 12px; background: var(--soft); }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); font-weight: 700; }
        .value { margin-top: 6px; font-size: 14px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        th, td { padding: 10px 12px; border-bottom: 1px solid var(--border); font-size: 13px; }
        thead th { background: var(--soft); text-transform: uppercase; letter-spacing: .06em; font-size: 11px; color: var(--muted); text-align: left; }
        td.right, th.right { text-align: right; }
        tfoot td { background: var(--soft); font-weight: 800; }
        .muted { color: var(--muted); font-weight: 500; }
        @media print {
            body { padding: 0; }
            .card { border: none; border-radius: 0; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <div>
                <h1 class="title">Invoice #INV-{{ $order->id }}</h1>
                <div class="sub">Created {{ optional($order->created_at)->format('d M Y, h:i A') }}</div>
            </div>
            <div>
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($order->status) }}</div>
            </div>
        </div>

        <div class="section">
            <div class="row">
                <div class="box">
                    <div class="label">Billed To</div>
                    <div class="value">{{ $user->name }}</div>
                    <div class="muted" style="margin-top:4px; font-size:12px;">{{ $user->email }}</div>
                </div>
                <div class="box">
                    <div class="label">Course</div>
                    <div class="value">{{ $order->course?->title ?? '—' }}</div>
                    <div class="muted" style="margin-top:4px; font-size:12px;">Batch: {{ $order->batch?->name ?? '—' }}</div>
                </div>
                <div class="box">
                    <div class="label">Total</div>
                    <div class="value">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</div>
                </div>
            </div>

            <div style="margin-top:16px;">
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
                                Course enrollment{{ $order->course ? ': '.$order->course->title : '' }}
                                @if($order->batch)
                                    <span class="muted"> (Batch: {{ $order->batch->name }})</span>
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

            <div class="muted" style="margin-top:14px; font-size:12px;">
                Tip: open this file in your browser and press Ctrl+P to print.
            </div>
        </div>
    </div>
</div>
</body>
</html>
