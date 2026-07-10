@props([
    'title' => null,
    'subtitle' => null,
    'badge' => 'iTechBD Ltd.',
])

<section class="relative overflow-hidden bg-gradient-to-br from-[#292b86] via-[#30339a] to-[#ed1c24] text-white">
    <div class="absolute inset-0 opacity-15 [background-image:radial-gradient(#ffffff_1px,transparent_1px)] [background-size:22px_22px]"></div>
    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-[#f15a24]/40 blur-3xl"></div>
    <div class="absolute -bottom-24 -left-20 h-72 w-72 rounded-full bg-white/20 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <div class="max-w-3xl">
            @if($badge)
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] ring-1 ring-white/20">
                    <span class="h-2 w-2 rounded-full bg-[#f15a24]"></span>
                    {{ $badge }}
                </div>
            @endif

            @if($title)
                <h1 class="mt-5 text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">{!! $title !!}</h1>
            @endif

            @if($subtitle)
                <div class="mt-5 max-w-2xl text-base leading-8 text-white/85 sm:text-lg">{!! $subtitle !!}</div>
            @endif
        </div>
    </div>
</section>
