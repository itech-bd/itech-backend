<x-app-layout>
    <x-slot name="header">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">Invoice #INV-{{ $order->id }}</h2>
                <p class="mt-1 text-sm text-slate-500">Created {{ optional($order->created_at)->format('d M Y, h:i A') }}</p>
            </div>
    </x-slot>
    <x-slot name="headerActions">
            <div class="flex flex-wrap items-center gap-2 print:hidden">
                <a
                    href="{{ $backUrl ?? route('users.invoices.index', $student) }}"
                    class="inline-flex min-h-11 items-center justify-center whitespace-nowrap rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                >
                    Back to invoices
                </a>
                <a
                    href="{{ route('users.invoices.download', [$student, $order]) }}"
                    class="inline-flex min-h-11 items-center justify-center whitespace-nowrap rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
                >
                    Download
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex min-h-11 items-center justify-center whitespace-nowrap rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-700"
                >
                    Print
                </button>
            </div>
    </x-slot>

    <style>
        @media print {
            .print\:hidden {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>

    <div class="mx-auto grid max-w-7xl grid-cols-1 items-start gap-5 xl:grid-cols-5 print:block print:max-w-3xl">
        @php
            $received = (float) $order->payments->where('status', 'successful')->sum('amount');
            $due = $order->status === 'pending' ? max(0, (float) $order->amount - $received) : 0;
        @endphp
        <section class="order-1 rounded-xl bg-white p-4 ring-1 ring-slate-200 sm:px-6 xl:col-span-5 print:hidden" aria-label="Payment balance">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <dl class="flex flex-wrap gap-8">
                    <div><dt class="text-xs font-semibold text-slate-500">Paid</dt><dd class="mt-1 text-lg font-bold text-emerald-700">{{ $order->currency }} {{ number_format($received, 2) }}</dd></div>
                    <div><dt class="text-xs font-semibold text-slate-500">Due</dt><dd class="mt-1 text-lg font-bold text-amber-700">{{ $order->currency }} {{ number_format($due, 2) }}</dd></div>
                </dl>
                @if ($order->status === 'pending')
                    <a href="{{ route('dashboard.admin.payments.create', $order) }}" class="rounded-xl bg-indigo-700 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-800">Record payment</a>
                @endif
            </div>
        </section>
        <div class="order-3 min-w-0 rounded-xl bg-white shadow-sm ring-1 ring-slate-200 xl:order-2 xl:col-span-3">
            <div class="border-b border-slate-200 p-6">
                <div class="mb-6 flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    @php
                        $siteLogo = \App\Models\FrontendSetting::where('key', 'site_logo_path')->value('value_en');
                    @endphp
                    @if ($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ config('app.name') }}" class="h-12 w-auto max-w-[200px] object-contain">
                    @else
                        <img src="{{ asset('brand/itechbd-logo.svg') }}" alt="iTechBD logo" class="h-12 w-auto">
                    @endif
                    <div class="text-right text-xs text-slate-500">System Generated Invoice</div>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <div class="text-sm font-semibold text-slate-500">Billed To</div>
                        <div class="mt-1 text-base font-semibold text-slate-900">{{ $student->name }}</div>
                        <div class="text-sm text-slate-600">{{ $student->email }}</div>
                    </div>

                    <div class="text-right">
                        <div class="text-sm font-semibold text-slate-500">Status</div>
                        @php
                            $badge = match ($order->status) {
                                'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                default => 'bg-slate-50 text-slate-700 ring-slate-200',
                            };
                        @endphp
                        <span class="mt-1 inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-600">Course</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $order->course?->title ?? '—' }}</dd>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-600">Batch</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $order->batch?->name ?? '—' }}</dd>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-600">Invoice Date</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ optional($order->created_at)->format('d M Y') }}</dd>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-600">Total</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        Course enrollment{{ $order->course ? ': '.$order->course->title : '' }}
                                        @if($order->batch)
                                            <span class="text-slate-500">(Batch: {{ $order->batch->name }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">
                                        {{ $order->currency }} {{ number_format((float) $order->amount, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50">
                                <tr>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Total</td>
                                    <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">
                                        {{ $order->currency }} {{ number_format((float) $order->amount, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mt-6 text-xs text-slate-500">
                    This invoice is generated by the system. If you have any issues, please contact support.
                    <div class="mt-2 font-semibold text-slate-600">System Generated Invoice</div>
                    <div class="mt-1 font-semibold text-slate-600">Signature Not Required</div>
                </div>
            </div>
        </div>
        <section id="payment-history" class="order-2 min-w-0 scroll-mt-32 rounded-xl bg-white p-5 ring-1 ring-slate-200 xl:order-3 xl:col-span-2 print:hidden">
            <h3 class="text-lg font-bold text-slate-900">Payment history</h3>
            <p class="mt-1 text-sm text-slate-500">Payments and reversals for this invoice. Dates in {{ config('app.timezone') }}.</p>
            <div class="mt-5 space-y-4">
                @forelse ($order->payments as $payment)
                    @php($isImported = $payment->payment_method === 'legacy')
                    <article class="break-words rounded-xl border border-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-bold text-slate-900">#PAY-{{ $payment->id }} &middot; {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
                            <span class="rounded-lg px-2.5 py-1 text-xs font-bold {{ $payment->status === 'successful' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($payment->status) }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">{{ $isImported ? 'Previous invoice' : ucfirst(str_replace('_', ' ', $payment->payment_method)) }}@if ($payment->transaction_reference) &middot; Reference: {{ $payment->transaction_reference }}@endif</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $isImported ? 'Estimated payment time' : 'Payment time' }}: {{ $payment->paid_at?->format('d M Y, H:i') ?? 'Unknown' }}</p>
                        <div class="mt-3 space-y-3 border-l-2 border-slate-200 pl-3 text-xs text-slate-600">
                            @include('payment::partials.transaction-details')
                        </div>
                    </article>
                @empty
                    <p class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500">No payments recorded for this invoice.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
