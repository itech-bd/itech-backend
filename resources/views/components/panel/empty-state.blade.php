@props([
    'title' => 'No data found',
    'message' => 'There is nothing to show right now.',
    'icon' => 'fa-regular fa-folder-open',
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-slate-300 bg-white/70 p-10 text-center']) }}>
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-500">
        <i class="{{ $icon }} text-2xl"></i>
    </div>
    <h3 class="mt-4 text-base font-bold text-slate-950">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $message }}</p>
    @if(trim($slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
