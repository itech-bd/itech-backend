@props([
    'href' => '#',
    'tone' => 'primary',
])

@php
    $classes = [
        'primary' => 'bg-[#2E3192] text-white shadow-sm shadow-[#2E3192]/20 hover:bg-[#252879]',
        'orange' => 'bg-[#F47B20] text-white shadow-sm shadow-[#F47B20]/20 hover:bg-[#d96816]',
        'danger' => 'bg-[#E5362C] text-white shadow-sm shadow-[#E5362C]/20 hover:bg-[#c9271e]',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50',
    ];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition '.$classes[$tone]]) }}>
    {{ $slot }}
</a>
