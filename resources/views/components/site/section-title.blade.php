@props([
    'kicker' => null,
    'title' => null,
    'subtitle' => null,
    'actionUrl' => null,
    'actionLabel' => null,
    'align' => 'center',
])

@php
    $alignClass = $align === 'left' ? 'text-left items-start' : 'text-center items-center';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col '.$alignClass]) }}>
    @if($kicker)
        <div class="inline-flex items-center gap-2 rounded-full border border-[#f15a24]/15 bg-[#f15a24]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-[#f15a24]">
            <span class="h-1.5 w-1.5 rounded-full bg-[#ed1c24]"></span>
            {{ $kicker }}
        </div>
    @endif

    @if($title)
        <h2 class="mt-4 max-w-3xl text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
            {!! $title !!}
        </h2>
    @endif

    @if($subtitle)
        <div class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
            {!! $subtitle !!}
        </div>
    @endif

    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#292b86] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#292b86]/20 transition hover:-translate-y-0.5 hover:bg-[#1f216d]">
            {{ $actionLabel }}
            <span aria-hidden="true">→</span>
        </a>
    @endif
</div>
