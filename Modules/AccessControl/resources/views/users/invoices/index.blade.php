<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">
                    Invoices: {{ $student->name }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">{{ $student->email }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('users.index') }}"
                    class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Back to Users
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl">
            <div class="mb-4 flex flex-wrap items-center gap-2">
                @php
                    $filters = [
                        '' => 'All',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ];
                @endphp

                @foreach ($filters as $value => $label)
                    @php
                        $isActive = ($activeStatus === $value) || ($value === '' && $activeStatus === null);
                    @endphp
                    <a
                        href="{{ route('users.invoices.index', $student) . ($value !== '' ? ('?status=' . $value) : '') }}"
                        class="rounded-md px-3 py-2 text-sm font-semibold {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Course</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Batch</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($orders as $order)
                                @php
                                    $badge = match ($order->status) {
                                        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                        default => 'bg-slate-50 text-slate-700 ring-slate-200',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900">#INV-{{ $order->id }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $order->course?->title ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $order->batch?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">
                                        {{ $order->currency }} {{ number_format((float) $order->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        {{ optional($order->created_at)->format('d M Y, h:i A') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a
                                                href="{{ route('users.invoices.show', [$student, $order]) }}"
                                                class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
                                            >
                                                View
                                            </a>
                                            <a
                                                href="{{ route('users.invoices.download', [$student, $order]) }}"
                                                class="rounded-md bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                            >
                                                Download
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                        No invoices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($orders, 'links'))
                    <div class="border-t border-slate-200 p-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
