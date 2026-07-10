@extends('layouts.site')

@section('title', __('frontend.news') . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php($hero = $cmsSectionsByKey->get('hero'))
<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'News & Updates'" :subtitle="optional($hero)->content ?: 'Latest announcements, workshops, and institute updates.'" badge="News" />

    <section class="py-12">
        <div class="brand-container">
            @if($newsUpdates->count())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($newsUpdates as $news)
                        <x-site.news-card :news="$news" class="reveal" />
                    @endforeach
                </div>
                <div class="mt-10">{{ $newsUpdates->links() }}</div>
            @else
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center">
                    <h2 class="text-2xl font-black text-slate-950">No published news found</h2>
                    <p class="mt-2 text-slate-600">Publish a news update from the admin panel to show it here.</p>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
