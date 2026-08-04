@props([
    'title' => 'No data found',
    'message' => 'There is nothing to show right now.',
    'icon' => 'fa-regular fa-folder-open',
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-slate-300 bg-white/90 p-10 text-center shadow-[0_18px_42px_rgba(15,23,42,.06)]']) }}>
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-[#2E3192]/10 text-[#2E3192] ring-1 ring-[#2E3192]/10">
        <i class="{{ $icon }} text-2xl"></i>
    </div>
    <h3 class="mt-4 text-lg font-black text-slate-950">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-500">{{ $message }}</p>
    @if(trim($slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
