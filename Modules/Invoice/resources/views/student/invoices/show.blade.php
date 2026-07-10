<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Invoice Details</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Invoice #INV-{{ $order->id }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Created {{ optional($order->created_at)->format('d M Y, h:i A') }}</p>
            </div>

            <div class="flex flex-wrap gap-2 print:hidden">
                <x-panel.action-link href="{{ url('/dashboard/student/invoices') }}" tone="secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </x-panel.action-link>
                <x-panel.action-link href="{{ url('/dashboard/student/invoices/'.$order->getRouteKey().'/download') }}" tone="primary">
                    <i class="fa-solid fa-download"></i> Download PDF
                </x-panel.action-link>
                <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#F47B20] px-4 py-2 text-sm font-bold text-white shadow-sm shadow-[#F47B20]/20 transition hover:bg-[#d96816]">
                    <i class="fa-solid fa-print"></i> Print
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            .print\:hidden { display: none !important; }
            body { background: white !important; }
            header, aside { display: none !important; }
            main { padding: 0 !important; }
        }
    </style>

    <div class="mx-auto max-w-4xl">
        <div class="overflow-hidden rounded-[2rem] bg-white shadow-sm ring-1 ring-slate-200/70">
            <div class="bg-gradient-to-br from-[#2E3192] via-[#20236f] to-[#151748] p-6 text-white sm:p-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        @php
                            $siteLogo = \App\Models\FrontendSetting::where('key', 'site_logo_path')->value('value_en');
                        @endphp
                        <div class="inline-flex rounded-2xl bg-white p-3 shadow-sm">
                            @if ($siteLogo)
                                <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ config('app.name') }}" class="h-12 w-auto max-w-[220px] object-contain">
                            @else
                                <img src="{{ asset('brand/itechbd-logo.png') }}" alt="iTechBD logo" class="h-12 w-auto">
                            @endif
                        </div>
                        <h3 class="mt-6 text-3xl font-extrabold">Invoice #INV-{{ $order->id }}</h3>
                        <p class="mt-2 text-sm text-white/70">System Generated Invoice</p>
                    </div>

                    <div class="rounded-2xl bg-white/10 p-4 text-left ring-1 ring-white/15 sm:text-right">
                        <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-white/50">Status</div>
                        <div class="mt-2"><x-panel.status-badge :status="$order->status" /></div>
                        <div class="mt-4 text-xs font-extrabold uppercase tracking-[0.18em] text-white/50">Total Amount</div>
                        <div class="mt-1 text-2xl font-extrabold">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-3xl bg-slate-50 p-5 ring-1 ring-slate-100">
                        <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Billed To</div>
                        <div class="mt-3 text-lg font-extrabold text-slate-950">{{ $user->name }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $user->email }}</div>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 ring-1 ring-slate-100">
                        <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Invoice Date</div>
                        <div class="mt-3 text-lg font-extrabold text-slate-950">{{ optional($order->created_at)->format('d M Y') }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ optional($order->created_at)->format('h:i A') }}</div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-3xl ring-1 ring-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">Description</th>
                                <th class="px-5 py-4 text-right text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr>
                                <td class="px-5 py-5">
                                    <div class="font-extrabold text-slate-950">Course enrollment</div>
                                    <div class="mt-1 text-sm text-slate-500">{{ $order->course?->title ?? '—' }}</div>
                                    @if($order->batch)
                                        <div class="mt-2 inline-flex rounded-full bg-[#2E3192]/10 px-3 py-1 text-xs font-extrabold text-[#2E3192]">Batch: {{ $order->batch->name }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-5 text-right text-base font-extrabold text-slate-950">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td class="px-5 py-4 text-right text-sm font-extrabold text-slate-700">Total</td>
                                <td class="px-5 py-4 text-right text-lg font-extrabold text-[#2E3192]">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-6 rounded-3xl bg-[#2E3192]/5 p-5 text-sm leading-6 text-slate-600 ring-1 ring-[#2E3192]/10">
                    This invoice is generated by the system. If you have any payment issue, please contact iTechBD support.
                    <div class="mt-3 font-extrabold text-slate-800">System Generated · Signature Not Required</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
