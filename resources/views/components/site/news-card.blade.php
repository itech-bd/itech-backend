@props(['news'])

@php

    $title = (string) ($news->title ?? 'News update');
    $date = ($news->published_at ?: $news->created_at)?->format('d M Y');
    $imagePath = $news->image_path ?? null;
    $imageUrl = is_string($imagePath) && trim($imagePath) !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($imagePath, '/')) : null;
    $excerpt = trim((string) ($news->excerpt ?? ''));
    if ($excerpt === '') {
        $excerpt = \Illuminate\Support\Str::limit(strip_tags((string) ($news->body ?? '')), 120);
    }
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#f15a24]/40 hover:shadow-xl hover:shadow-[#292b86]/10']) }}>
    <a href="{{ route('news.show', $news) }}" class="block">
        <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-[#292b86] via-[#ed1c24] to-[#f15a24]">
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @else
                <div class="absolute inset-0 opacity-30 [background-image:radial-gradient(#ffffff_1px,transparent_1px)] [background-size:18px_18px]"></div>
                <div class="flex h-full items-center justify-center p-8 text-center text-xl font-black text-white">iTechBD Update</div>
            @endif
            @if($date)
                <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-[#292b86]">{{ $date }}</div>
            @endif
        </div>
    </a>
    <div class="p-5">
        <h3 class="line-clamp-2 text-lg font-extrabold leading-snug text-slate-950">
            <a href="{{ route('news.show', $news) }}" class="hover:text-[#292b86]">{{ $title }}</a>
        </h3>
        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $excerpt }}</p>
        <a href="{{ route('news.show', $news) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold text-[#292b86] transition group-hover:text-[#f15a24]">
            Read update <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
