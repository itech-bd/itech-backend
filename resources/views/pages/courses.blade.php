@extends('layouts.site')

@section('title', __('frontend.courses') . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php
    $hero = $cmsSectionsByKey->get('hero');
    $heroTitle = optional($hero)->title ?: __('frontend.courses_title');
    $heroSubtitle = optional($hero)->content ?: __('frontend.courses_subtitle');
@endphp

<main>
    <x-site.page-hero :title="$heroTitle" :subtitle="$heroSubtitle" badge="Courses" />

    <section class="py-10">
        <div class="brand-container">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('courses') }}" class="grid gap-4 lg:grid-cols-[1fr_260px_auto] lg:items-end">
                    <div>
                        <label for="course-search" class="block text-sm font-extrabold text-slate-900">Search courses</label>
                        <input id="course-search" type="search" name="search" value="{{ $search ?? '' }}" placeholder="Course name or keyword" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]">
                    </div>
                    <div>
                        <label for="course-track" class="block text-sm font-extrabold text-slate-900">Track</label>
                        <select id="course-track" name="track" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]">
                            <option value="">All tracks</option>
                            @foreach($tracks as $track)
                                <option value="{{ $track }}" @selected(($selectedTrack ?? '') === $track)>{{ $track }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-2xl bg-[#292b86] px-6 py-3 text-sm font-extrabold text-white transition hover:bg-[#1f216d]">Filter</button>
                        <a href="{{ route('courses') }}" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-extrabold text-slate-700 transition hover:border-[#f15a24] hover:text-[#f15a24]">Reset</a>
                    </div>
                </form>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-[1.25rem] border border-slate-200 bg-white p-4 shadow-sm"><div class="text-2xl font-black text-[#292b86]">{{ $stats['courses'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Courses</div></div>
                <div class="rounded-[1.25rem] border border-slate-200 bg-white p-4 shadow-sm"><div class="text-2xl font-black text-[#292b86]">{{ $stats['batches'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Batches</div></div>
                <div class="rounded-[1.25rem] border border-slate-200 bg-white p-4 shadow-sm"><div class="text-2xl font-black text-[#292b86]">{{ $stats['mentors'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Mentors</div></div>
                <div class="rounded-[1.25rem] border border-slate-200 bg-white p-4 shadow-sm"><div class="text-2xl font-black text-[#292b86]">{{ $stats['classes'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Classes</div></div>
            </div>

            @if($courses->count())
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($courses as $course)
                        <x-site.course-card :course="$course" class="reveal" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $courses->links() }}
                </div>
            @else
                <div class="mt-10 rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center">
                    <h2 class="text-2xl font-black text-slate-950">No active courses found</h2>
                    <p class="mt-2 text-slate-600">Publish courses from the admin panel or clear the current filters.</p>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
