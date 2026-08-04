@props([
    'label' => '',
    'value' => '0',
    'hint' => null,
    'icon' => 'fa-solid fa-chart-simple',
    'tone' => 'blue',
])

@php
    $tones = [
        'blue' => 'from-[#2E3192]/10 to-[#2E3192]/5 text-[#2E3192] ring-[#2E3192]/10',
        'orange' => 'from-[#F47B20]/10 to-[#F47B20]/5 text-[#C9570B] ring-[#F47B20]/10',
        'red' => 'from-[#E5362C]/10 to-[#E5362C]/5 text-[#C82219] ring-[#E5362C]/10',
        'green' => 'from-emerald-500/10 to-emerald-500/5 text-emerald-700 ring-emerald-500/10',
        'slate' => 'from-slate-500/10 to-slate-500/5 text-slate-700 ring-slate-500/10',
    ];
    $toneClass = $tones[$tone] ?? $tones['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-3xl border border-white/70 bg-white p-5 shadow-[0_18px_42px_rgba(15,23,42,.08)] ring-1 ring-slate-200/70 transition duration-200 hover:-translate-y-1 hover:shadow-[0_24px_55px_rgba(15,23,42,.11)]']) }}>
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $toneClass }}"></div>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-extrabold text-slate-500">{{ $label }}</p>
            <div class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ $value }}</div>
            @if($hint)
                <p class="mt-1 text-xs font-bold text-slate-500">{{ $hint }}</p>
            @endif
        </div>
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br {{ $toneClass }} ring-1 transition duration-200 group-hover:scale-105">
            <i class="{{ $icon }} text-lg"></i>
        </div>
    </div>
</div>
