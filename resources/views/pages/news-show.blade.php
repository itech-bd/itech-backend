@extends('layouts.site')

@section('title', $newsUpdate->title . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php
    $imagePath = $newsUpdate->image_path ?? null;
    $imageUrl = is_string($imagePath) && trim($imagePath) !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($imagePath, '/')) : null;
@endphp

<main>
    <section class="bg-white py-12 lg:py-16">
        <div class="brand-container max-w-5xl">
            <a href="{{ route('news') }}" class="inline-flex text-sm font-extrabold text-[#292b86] hover:text-[#f15a24]">← Back to news</a>
            <h1 class="mt-5 text-4xl font-black leading-tight text-slate-950 sm:text-5xl">{{ $newsUpdate->title }}</h1>
            <div class="mt-4 text-sm font-bold uppercase tracking-[0.16em] text-[#f15a24]">{{ optional($newsUpdate->published_at ?: $newsUpdate->created_at)->format('d M Y') }}</div>

            <div class="mt-8 overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#292b86] to-[#f15a24]">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $newsUpdate->title }}" class="max-h-[480px] w-full object-cover">
                @else
                    <div class="flex aspect-[16/7] items-center justify-center p-10 text-center text-3xl font-black text-white">iTechBD Update</div>
                @endif
            </div>

            <article class="site-prose mt-8 rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                {!! $newsUpdate->body !!}
            </article>
        </div>
    </section>

    @if($relatedNews->count())
        <section class="py-12">
            <div class="brand-container">
                <x-site.section-title kicker="More Updates" title="Related announcements" />
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach($relatedNews as $news)
                        <x-site.news-card :news="$news" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
@endsection
