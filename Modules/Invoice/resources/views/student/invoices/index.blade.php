<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Payments</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Invoices</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Track payment status and download your course invoices.</p>
            </div>
            <x-panel.action-link href="{{ route('courses') }}" tone="orange">
                <i class="fa-solid fa-plus"></i>
                Enroll New Course
            </x-panel.action-link>
        </div>
    </x-slot>

    @php
        $tabs = [
            ['label' => 'All', 'value' => null, 'icon' => 'fa-solid fa-layer-group'],
            ['label' => 'Paid', 'value' => 'paid', 'icon' => 'fa-solid fa-circle-check'],
            ['label' => 'Pending', 'value' => 'pending', 'icon' => 'fa-solid fa-clock'],
            ['label' => 'Cancelled', 'value' => 'cancelled', 'icon' => 'fa-solid fa-circle-xmark'],
        ];
    @endphp

    <div class="mb-5 flex flex-wrap items-center gap-2 rounded-3xl bg-white p-2 shadow-sm ring-1 ring-slate-200/70">
        @foreach($tabs as $tab)
            @php
                $isActive = ($activeStatus === $tab['value']) || ($activeStatus === null && $tab['value'] === null);
                $href = $tab['value'] ? url('/dashboard/student/invoices?status='.$tab['value']) : url('/dashboard/student/invoices');
            @endphp
            <a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold transition {{ $isActive ? 'bg-[#2E3192] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-[#2E3192]' }}">
                <i class="{{ $tab['icon'] }}"></i>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    @if($orders->count())
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($orders as $order)
                <article class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2E3192]/10">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-slate-500">Invoice</div>
                            <h3 class="mt-1 text-2xl font-extrabold text-slate-950">#INV-{{ $order->id }}</h3>
                        </div>
                        <x-panel.status-badge :status="$order->status" />
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100">
                            <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Course</div>
                            <div class="mt-2 line-clamp-2 font-bold text-slate-950">{{ $order->course?->title ?? '—' }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100">
                                <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Batch</div>
                                <div class="mt-2 truncate font-bold text-slate-950">{{ $order->batch?->name ?? '—' }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100">
                                <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Date</div>
                                <div class="mt-2 font-bold text-slate-950">{{ optional($order->created_at)->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Total</div>
                            <div class="mt-1 text-xl font-extrabold text-[#2E3192]">{{ $order->currency }} {{ number_format((float) $order->amount, 2) }}</div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2">
                            <x-panel.action-link href="{{ url('/dashboard/student/invoices/'.$order->getRouteKey()) }}" tone="secondary">View</x-panel.action-link>
                            <x-panel.action-link href="{{ url('/dashboard/student/invoices/'.$order->getRouteKey().'/download') }}" tone="primary">PDF</x-panel.action-link>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200/70">
            {{ $orders->links() }}
        </div>
    @else
        <x-panel.empty-state title="No invoice found" message="Invoices will appear after you enroll in a course." icon="fa-regular fa-file-lines">
            <x-panel.action-link href="{{ route('courses') }}" tone="orange">Browse Courses</x-panel.action-link>
        </x-panel.empty-state>
    @endif
</x-app-layout>
