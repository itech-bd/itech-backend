@props(['mentor'])

@php

    $name = (string) ($mentor->name ?: optional($mentor->user)->name ?: 'Mentor');
    $topic = (string) ($mentor->topic ?: 'Professional Mentor');
    $imagePath = optional($mentor->user)->profile_image;
    $imageUrl = is_string($imagePath) && trim($imagePath) !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($imagePath, '/')) : null;
    $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->implode('');
    $routeKey = $mentor->public_route_key ?? ($mentor->slug ?: $mentor->id);
@endphp

<article {{ $attributes->merge(['class' => 'group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#292b86]/30 hover:shadow-xl hover:shadow-[#292b86]/10']) }}>
    <a href="{{ route('mentors.show', $routeKey) }}" class="block">
        <div class="mx-auto flex h-32 w-32 items-center justify-center rounded-[2.2rem] bg-gradient-to-br from-[#292b86] via-[#6d3b8d] to-[#f15a24] p-[3px] shadow-lg shadow-slate-200/80">
            <div class="flex h-full w-full items-center justify-center overflow-hidden rounded-[2rem] bg-white p-[7px]">
                @if($imageUrl)
                    <div class="flex h-full w-full items-center justify-center overflow-hidden rounded-[1.55rem] bg-slate-100">
                        <img src="{{ $imageUrl }}" alt="{{ $name }}" class="h-full w-full object-contain object-center transition duration-500 group-hover:scale-105">
                    </div>
                @else
                    <div class="flex h-full w-full items-center justify-center rounded-[1.55rem] bg-[#292b86]/10 text-2xl font-black text-[#292b86]">
                        {{ $initials ?: 'M' }}
                    </div>
                @endif
            </div>
        </div>
    </a>

    <div class="mt-5 text-center">
        <h3 class="text-lg font-extrabold text-slate-950">
            <a href="{{ route('mentors.show', $routeKey) }}" class="hover:text-[#292b86]">{{ $name }}</a>
        </h3>
        <p class="mt-1 min-h-[2.5rem] text-sm leading-5 text-slate-600">{{ $topic }}</p>
        <a href="{{ route('mentors.show', $routeKey) }}" class="mt-4 inline-flex items-center gap-2 rounded-full border border-[#292b86]/15 px-4 py-2 text-xs font-extrabold text-[#292b86] transition hover:bg-[#292b86] hover:text-white">
            View profile <span aria-hidden="true">&rarr;</span>
        </a>
    </div>
</article>
