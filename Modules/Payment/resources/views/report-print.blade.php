<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment report</title>
    <style>
        body { font: 12px Arial, sans-serif; color: #172033; margin: 24px; }
        h1 { font-size: 24px; margin-bottom: 8px; }
        p { line-height: 1.5; }
        a { color: inherit; text-decoration: none; }
        dl { display: flex; gap: 32px; padding: 16px; border: 1px solid #cbd5e1; }
        dl > div { flex: 1; }
        dt { font-weight: bold; margin-bottom: 8px; }
        dd { margin: 4px 0; }
        dd > span { display: block; }
        .sr-only { display: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #cbd5e1; padding: 9px 6px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        th { background: #f1f5f9; }
        td p { margin: 3px 0; }
        tr { break-inside: avoid; }
        button { padding: 10px 16px; cursor: pointer; }
        @page { size: A4 landscape; margin: 12mm; }
        @media print { .print-controls { display: none; } body { margin: 0; } thead { display: table-header-group; } }
    </style>
</head>
<body>
    <div class="print-controls"><button type="button" onclick="window.print()">Print / Save as PDF</button></div>
    <h1>Payment report{{ $student ? ': '.$student->name : '' }}</h1>
    <p>Generated {{ now()->format('d M Y, H:i') }} ({{ config('app.timezone') }})<br>
        Payment dates: {{ $filters['from'] ?? 'Any start date' }} to {{ $filters['to'] ?? 'Any end date' }}<br>
        Status: {{ ucfirst($filters['status'] ?? 'All statuses') }} &middot;
        Method: {{ ($filters['payment_method'] ?? '') === 'legacy' ? 'Previous invoice' : ucfirst(str_replace('_', ' ', $filters['payment_method'] ?? 'All methods')) }}
        @if (! empty($filters['search']))<br>Search: {{ $filters['search'] }}@endif
    </p>
    @include('payment::partials.report-summary')
    <p>All matching transactions are included. Amounts are separated by currency. Received excludes reversed payments.
        Dates use payment time, not reversal time. Previous invoice dates are estimates.</p>
    @include('payment::partials.report-table')
</body>
</html>
