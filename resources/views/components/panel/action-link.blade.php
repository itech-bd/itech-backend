@props([
    'href' => '#',
    'tone' => 'primary',
])

@php
    $classes = [
        'primary' => 'bg-[#2E3192] text-white shadow-[0_12px_26px_rgba(46,49,146,.22)] hover:bg-[#252879]',
        'orange' => 'bg-[#F47B20] text-white shadow-[0_12px_26px_rgba(244,123,32,.22)] hover:bg-[#d96816]',
        'danger' => 'bg-[#E5362C] text-white shadow-[0_12px_26px_rgba(229,54,44,.20)] hover:bg-[#c9271e]',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-200 shadow-sm hover:border-[#2E3192]/30 hover:bg-[#2E3192]/5 hover:text-[#2E3192]',
    ];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-extrabold transition duration-200 hover:-translate-y-0.5 '.$classes[$tone]]) }}>
    {{ $slot }}
</a>
