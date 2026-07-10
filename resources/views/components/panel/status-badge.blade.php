@props(['status' => null])

@php
    $value = strtolower((string) $status);
    $map = [
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'running' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'upcoming' => 'bg-[#2E3192]/10 text-[#2E3192] ring-[#2E3192]/20',
        'inactive' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];
    $class = $map[$value] ?? 'bg-slate-50 text-slate-700 ring-slate-200';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset '.$class]) }}>
    {{ $status ? ucfirst((string) $status) : 'N/A' }}
</span>
