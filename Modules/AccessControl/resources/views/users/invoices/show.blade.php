<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">Invoice #INV-{{ $order->id }}</h2>
                <p class="mt-1 text-sm text-slate-500">Created {{ optional($order->created_at)->format('d M Y, h:i A') }}</p>
            </div>

            <div class="flex flex-wrap gap-2 print:hidden">
                <a
                    href="{{ route('users.invoices.index', $student) }}"
                    class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Back
                </a>
                <a
                    href="{{ route('users.invoices.download', [$student, $order]) }}"
                    class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Download
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
                >
                    Print
                </button>
            </div>
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

    <div class="mx-auto max-w-3xl">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
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
    </div>
</x-app-layout>
