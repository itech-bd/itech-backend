@extends('layouts.site')

@section('title', __('frontend.about') . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php
    $hero = $cmsSectionsByKey->get('hero');
    $intro = $cmsSectionsByKey->get('about_intro');
    $mission = $cmsSectionsByKey->get('about_mission');
    $vision = $cmsSectionsByKey->get('about_vision');
    $cta = $cmsSectionsByKey->get('about_cta');
    $values = collect(['about_value_1','about_value_2','about_value_3','about_value_4','about_value_5','about_value_6'])->map(fn ($key) => $cmsSectionsByKey->get($key))->filter();
@endphp

<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'About iTechBD Ltd.'" :subtitle="optional($hero)->content ?: 'A software development company and professional IT training institute focused on practical skill development.'" badge="About" />

    <section class="py-12">
        <div class="brand-container grid gap-8 lg:grid-cols-[1fr_420px] lg:items-start">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                <h2 class="text-2xl font-black text-slate-950">{{ optional($intro)->title ?: 'Who we are' }}</h2>
                <div class="site-prose mt-4">
                    {!! optional($intro)->content ?: optional($hero)->content ?: '<p>iTechBD Ltd. helps learners build job-ready and freelance-ready skills through structured courses, mentor guidance, and practical projects.</p>' !!}
                </div>
            </div>

            <aside class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-[1.75rem] bg-[#292b86] p-6 text-white shadow-xl shadow-[#292b86]/15">
                    <div class="text-sm font-bold uppercase tracking-[0.16em] text-white/70">Mission</div>
                    <h3 class="mt-2 text-xl font-black">{{ optional($mission)->title ?: 'Our mission' }}</h3>
                    <div class="mt-3 text-sm leading-7 text-white/80">{!! optional($mission)->content ?: '<p>Make skill-building practical, structured, and outcome-driven.</p>' !!}</div>
                </div>
                <div class="rounded-[1.75rem] bg-[#f15a24] p-6 text-white shadow-xl shadow-[#f15a24]/15">
                    <div class="text-sm font-bold uppercase tracking-[0.16em] text-white/80">Vision</div>
                    <h3 class="mt-2 text-xl font-black">{{ optional($vision)->title ?: 'Our vision' }}</h3>
                    <div class="mt-3 text-sm leading-7 text-white/90">{!! optional($vision)->content ?: '<p>Create a community of skilled professionals ready for jobs, freelancing, and software careers.</p>' !!}</div>
                </div>
            </aside>
        </div>
    </section>

    <section class="bg-white py-12">
        <div class="brand-container">
            <x-site.section-title kicker="Live Platform Data" title="iTechBD at a glance" subtitle="These numbers are calculated from your database tables, not manually written counters." />
            <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-5">
                <div class="rounded-[1.35rem] border border-slate-200 p-5 text-center"><div class="text-3xl font-black text-[#292b86]">{{ $stats['courses'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Courses</div></div>
                <div class="rounded-[1.35rem] border border-slate-200 p-5 text-center"><div class="text-3xl font-black text-[#292b86]">{{ $stats['batches'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Batches</div></div>
                <div class="rounded-[1.35rem] border border-slate-200 p-5 text-center"><div class="text-3xl font-black text-[#292b86]">{{ $stats['mentors'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Mentors</div></div>
                <div class="rounded-[1.35rem] border border-slate-200 p-5 text-center"><div class="text-3xl font-black text-[#292b86]">{{ $stats['students'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Students</div></div>
                <div class="rounded-[1.35rem] border border-slate-200 p-5 text-center"><div class="text-3xl font-black text-[#292b86]">{{ $stats['classes'] ?? 0 }}+</div><div class="text-xs font-bold uppercase tracking-wide text-slate-500">Classes</div></div>
            </div>
        </div>
    </section>

    @if($values->count())
        <section class="py-12">
            <div class="brand-container">
                <x-site.section-title kicker="What We Focus On" title="Training values managed from CMS sections" />
                <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($values as $value)
                        <article class="reveal rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f15a24]/10 text-[#f15a24]"><i class="fa-solid fa-check"></i></div>
                            <h3 class="mt-5 text-lg font-black text-slate-950">{{ $value->title }}</h3>
                            <div class="site-prose mt-2 text-sm">{!! $value->content !!}</div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($featuredCourses->count())
        <section class="bg-white py-12">
            <div class="brand-container">
                <x-site.section-title kicker="Courses" title="Current active courses" :action-url="route('courses')" action-label="Explore all" />
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach($featuredCourses as $course)
                        <x-site.course-card :course="$course" compact />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="py-12">
        <div class="brand-container">
            <div class="rounded-[2rem] bg-gradient-to-r from-[#292b86] to-[#f15a24] p-8 text-white shadow-2xl shadow-[#292b86]/20">
                <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <h2 class="text-3xl font-black">{{ optional($cta)->title ?: 'Ready to start learning?' }}</h2>
                        <div class="mt-3 text-white/80">{!! optional($cta)->content ?: '<p>Explore courses, pick a batch, and start building your professional skills.</p>' !!}</div>
                    </div>
                    <a href="{{ optional($cta)->button_link ?: route('courses') }}" class="rounded-full bg-white px-6 py-3 text-center text-sm font-extrabold text-[#292b86]">{{ optional($cta)->button_text ?: 'Explore Courses' }}</a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
