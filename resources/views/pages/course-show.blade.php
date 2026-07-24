@extends('layouts.site')

@section('title', $course->title . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php

    $thumbnailUrl = $course->thumbnail_url ?? null;
    $onlinePrice = $course->online_discount_price ?: $course->discount_price;
    $offlinePrice = $course->offline_discount_price ?: $course->discount_price;
    $lowestPrice = collect([$onlinePrice, $offlinePrice, $course->discount_price])->filter(fn ($value) => $value !== null && (float) $value > 0)->map(fn ($value) => (float) $value)->min();
@endphp

<main>
    <section class="relative overflow-hidden bg-white">
        <div class="absolute inset-0 bg-gradient-to-br from-[#292b86]/10 via-white to-[#f15a24]/10"></div>
        <div class="relative brand-container grid gap-10 py-12 lg:grid-cols-[1fr_.85fr] lg:items-center lg:py-16">
            <div class="reveal">
                <a href="{{ route('courses') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-[#292b86] hover:text-[#f15a24]">← Back to courses</a>
                <h1 class="mt-5 text-4xl font-black leading-tight text-slate-950 sm:text-5xl">{{ $course->title }}</h1>
                <p class="mt-4 max-w-2xl text-base leading-8 text-slate-600">{{ \Illuminate\Support\Str::limit(trim(strip_tags($course->description)), 220) }}</p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <a href="{{ route('checkout.show', $course) }}" class="inline-flex items-center justify-center rounded-full bg-[#f15a24] px-7 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#f15a24]/20 transition hover:bg-[#ed1c24]">Enroll Now</a>
                    @else
                        <button type="button" data-auth-trigger="login" class="inline-flex items-center justify-center rounded-full bg-[#f15a24] px-7 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#f15a24]/20 transition hover:bg-[#ed1c24]">Login to Enroll</button>
                    @endauth
                    <a href="{{ url('/contact') }}" class="inline-flex items-center justify-center rounded-full border border-[#292b86]/15 bg-white px-7 py-3.5 text-sm font-extrabold text-[#292b86] transition hover:bg-[#292b86] hover:text-white">Ask for details</a>
                </div>
            </div>

            <div class="reveal">
                <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-4 shadow-2xl shadow-[#292b86]/10">
                    <div class="relative aspect-[16/10] overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-[#292b86] to-[#f15a24]">
                        @if($thumbnailUrl)
                            <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center p-8 text-center text-2xl font-black text-white">{{ $course->title }}</div>
                        @endif
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-[#292b86]/5 p-4"><div class="text-xs font-bold uppercase text-slate-500">Online Fee</div><div class="mt-1 text-xl font-black text-[#292b86]">{{ $onlinePrice ? '৳'.number_format((float) $onlinePrice, 0) : 'Contact' }}</div></div>
                        <div class="rounded-2xl bg-[#f15a24]/10 p-4"><div class="text-xs font-bold uppercase text-slate-500">Offline Fee</div><div class="mt-1 text-xl font-black text-[#f15a24]">{{ $offlinePrice ? '৳'.number_format((float) $offlinePrice, 0) : 'Contact' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12">
        <div class="brand-container grid gap-8 lg:grid-cols-[1fr_360px] lg:items-start">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:flex lg:max-h-[calc(100vh-8rem)] lg:flex-col lg:overflow-hidden lg:p-8">
                <h2 class="text-2xl font-black text-slate-950">Course Overview</h2>
                <div class="site-prose mt-4 lg:min-h-0 lg:flex-1 lg:overflow-y-auto lg:pr-3">
                    {!! $course->description !!}
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black text-slate-950">Upcoming batches</h2>
                    @if($course->batches->count())
                        <div class="mt-5 grid gap-4">
                            @foreach($course->batches as $batch)
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="font-black text-slate-950">{{ $batch->name }}</div>
                                        <span class="rounded-full bg-[#292b86]/10 px-2.5 py-1 text-xs font-bold text-[#292b86]">{{ ucfirst($batch->status) }}</span>
                                    </div>
                                    <div class="mt-3 grid gap-2 text-sm text-slate-600">
                                        <div><strong class="text-slate-900">Start:</strong> {{ optional($batch->start_date)->format('d M Y') }}</div>
                                        <div><strong class="text-slate-900">Days:</strong> {{ implode(', ', (array) $batch->class_days) }}</div>
                                        <div><strong class="text-slate-900">Time:</strong> {{ $batch->class_time }}</div>
                                    </div>
                                    @if($batch->mentors->count())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($batch->mentors as $mentorUser)
                                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600">{{ $mentorUser->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm leading-6 text-slate-600">No upcoming batch is currently published for this course. Contact office for the next schedule.</p>
                    @endif
                </div>

                <div class="rounded-[1.75rem] bg-gradient-to-br from-[#292b86] to-[#f15a24] p-6 text-white shadow-xl shadow-[#292b86]/15">
                    <h2 class="text-xl font-black">Need course details?</h2>
                    <p class="mt-2 text-sm leading-6 text-white/80">Ask for outline, schedule, online/offline fee, and admission process.</p>
                    <a href="{{ url('/contact') }}" class="mt-5 inline-flex rounded-full bg-white px-5 py-2.5 text-sm font-extrabold text-[#292b86]">Contact Office</a>
                </div>
            </aside>
        </div>
    </section>

    @if($relatedCourses->count())
        <section class="bg-white py-12">
            <div class="brand-container">
                <x-site.section-title kicker="Related Courses" title="Explore more skill paths" />
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach($relatedCourses as $relatedCourse)
                        <x-site.course-card :course="$relatedCourse" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
@endsection
