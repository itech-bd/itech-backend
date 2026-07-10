@extends('layouts.site')

@section('title', config('app.name', 'iTechBD Ltd') . ' • Develop Software and Professional Skills')

@section('content')
@php

    $heroPrimary = $cmsSectionsByKey->get('hero_primary');
    $heroEmphasis = $cmsSectionsByKey->get('hero_emphasis');
    $heroParagraph = $cmsSectionsByKey->get('hero_paragraph');
    $heroCtaPrimary = $cmsSectionsByKey->get('hero_cta_primary');
    $homeAboutTitle = $cmsSectionsByKey->get('home_about_title');
    $homeAboutSubtitle = $cmsSectionsByKey->get('home_about_subtitle');
    $skillTracksTitle = $cmsSectionsByKey->get('home_skill_tracks_title');
    $skillTracksSubtitle = $cmsSectionsByKey->get('home_skill_tracks_subtitle');

    $heroTitle = optional($heroPrimary)->title ?: 'Develop Software and';
    $heroHighlight = optional($heroEmphasis)->title ?: 'Professional Skills';
    $heroText = optional($heroParagraph)->content ?: '<p>iTechBD Ltd. offers practical, mentor-led training with real projects, batch schedules, and career-focused support for learners in Bangladesh.</p>';
    $heroButtonText = optional($heroCtaPrimary)->button_text ?: 'Explore Courses';
    $heroButtonLink = optional($heroCtaPrimary)->button_link ?: route('courses');

    $heroCourses = $popularCourses
        ->filter(fn ($course) => ($course->relationLoaded('batches') ? $course->batches : collect())
            ->contains(fn ($batch) => in_array($batch->status, ['upcoming', 'running'], true)))
        ->values();

    if ($heroCourses->isEmpty() && $popularCourses->isNotEmpty()) {
        $heroCourses = $popularCourses->take(1)->values();
    }
@endphp

<main id="top">
    <section class="relative overflow-hidden bg-white">
        <div class="absolute inset-0 bg-gradient-to-br from-[#292b86]/8 via-white to-[#f15a24]/10"></div>
        <div class="absolute -right-24 top-24 h-72 w-72 rounded-full bg-[#f15a24]/20 blur-3xl"></div>
        <div class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-[#292b86]/15 blur-3xl"></div>

        <div class="relative brand-container grid gap-12 py-14 lg:grid-cols-[1.05fr_.95fr] lg:items-center lg:py-20">
            <div class="reveal">
                <div class="inline-flex items-center gap-2 rounded-full border border-[#292b86]/10 bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#292b86] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-[#ed1c24]"></span>
                    Admissions going on
                </div>

                <h2 class="mt-6 text-3xl font-black leading-[1.05] tracking-tight text-slate-950 sm:text-4xl lg:text-5xl">
                    {{ $heroTitle }}
                    <span class="block text-[#f15a24]">{{ $heroHighlight }}</span>
                </h2>

                <div class="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg site-prose">
                    {!! $heroText !!}
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $heroButtonLink }}" class="inline-flex items-center justify-center rounded-full bg-[#f15a24] px-7 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#f15a24]/25 transition hover:-translate-y-0.5 hover:bg-[#ed1c24]">
                        {{ $heroButtonText }} <span class="ml-2">&rarr;</span>
                    </a>
                    <a href="{{ url('/contact') }}" class="inline-flex items-center justify-center rounded-full border border-[#292b86]/15 bg-white px-7 py-3.5 text-sm font-extrabold text-[#292b86] transition hover:-translate-y-0.5 hover:bg-[#292b86] hover:text-white">
                        Free Consultation
                    </a>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="text-2xl font-black text-[#292b86]">{{ $stats['courses'] ?? 0 }}+</div>
                        <div class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Active courses</div>
                    </div>
                    <div class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="text-2xl font-black text-[#292b86]">{{ $stats['batches'] ?? 0 }}+</div>
                        <div class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Live batches</div>
                    </div>
                    <div class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="text-2xl font-black text-[#292b86]">{{ $stats['mentors'] ?? 0 }}+</div>
                        <div class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Mentors</div>
                    </div>
                    <div class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="text-2xl font-black text-[#292b86]">{{ $stats['classes'] ?? 0 }}+</div>
                        <div class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">Scheduled classes</div>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <div class="relative rounded-[2rem] border border-slate-200 bg-white p-4 shadow-2xl shadow-[#292b86]/10">
                    <div class="absolute -right-5 -top-5 rounded-3xl bg-[#ed1c24] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#ed1c24]/25">
                        New Batch
                    </div>

                    @if($heroCourses->isNotEmpty())
                        <div x-data="courseCardRotator({{ $heroCourses->count() }})" x-ref="cardsWrap" class="relative">
                            @foreach($heroCourses as $heroCourse)
                                <div
                                    data-hero-course-card
                                    x-cloak
                                    x-show="isActive({{ $loop->index }})"
                                    x-init="$nextTick(() => syncHeight())"
                                    x-transition:enter="transition ease-out duration-500"
                                    x-transition:enter-start="opacity-0 translate-y-3"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-300"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-3"
                                    class="{{ $loop->first ? '' : 'absolute inset-0' }}"
                                >
                                    <x-site.course-card :course="$heroCourse" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-[1.5rem] bg-gradient-to-br from-[#292b86] to-[#f15a24] p-8 text-white">
                            <div class="text-2xl font-black">Courses will appear here</div>
                            <p class="mt-3 text-white/80">Add active courses from admin panel to populate the homepage automatically.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if($courseTracks->count())
        <section class="py-14">
            <div class="brand-container">
                <x-site.section-title
                    kicker="Course Categories"
                    :title="optional($skillTracksTitle)->title ?: 'Skill tracks from your active courses'"
                    :subtitle="optional($skillTracksSubtitle)->content ?: 'The categories below are generated from your course database, so they stay aligned with the courses you publish.'"
                />

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($courseTracks as $trackName => $trackCourses)
                        <a href="{{ route('courses', ['track' => $trackName]) }}" class="reveal group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-[#f15a24]/40 hover:shadow-xl hover:shadow-[#292b86]/10">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#292b86]/10 text-[#292b86] transition group-hover:bg-[#f15a24] group-hover:text-white">
                                <i class="fa-solid fa-layer-group text-xl"></i>
                            </div>
                            <h3 class="mt-5 text-lg font-black text-slate-950">{{ $trackName }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $trackCourses->count() }} active {{ \Illuminate\Support\Str::plural('course', $trackCourses->count()) }}</p>
                            <div class="mt-5 text-sm font-extrabold text-[#292b86] group-hover:text-[#f15a24]">Browse track &rarr;</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($popularCourses->count())
        <section class="bg-white py-14">
            <div class="brand-container">
                <x-site.section-title
                    kicker="Popular Courses"
                    title="Choose a course and start learning"
                    subtitle="These cards are loaded directly from the active courses in your database, including batch timing and pricing when available."
                    :action-url="route('courses')"
                    action-label="View all courses"
                />

                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($popularCourses as $course)
                        <x-site.course-card :course="$course" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($upcomingBatches->count())
        <section class="py-14">
            <div class="brand-container">
                <x-site.section-title kicker="Upcoming Batches" title="Admission is going on" subtitle="Batch cards are connected with the batches table and show course, date, class days, time, and mentor information dynamically." />

                <div class="mt-10 grid gap-5 lg:grid-cols-2">
                    @foreach($upcomingBatches as $batch)
                        <article class="reveal rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="inline-flex rounded-full bg-[#f15a24]/10 px-3 py-1 text-xs font-extrabold text-[#f15a24]">{{ ucfirst($batch->status) }}</div>
                                    <h3 class="mt-3 text-xl font-black text-slate-950">{{ $batch->name }}</h3>
                                    @if($batch->course)
                                        <a href="{{ route('courses.show', $batch->course) }}" class="mt-1 inline-flex text-sm font-bold text-[#292b86] hover:text-[#f15a24]">{{ $batch->course->title }}</a>
                                    @endif
                                </div>
                                <div class="rounded-2xl bg-[#292b86] px-4 py-3 text-center text-white">
                                    <div class="text-xs font-bold uppercase opacity-80">Starts</div>
                                    <div class="text-sm font-black">{{ optional($batch->start_date)->format('d M Y') }}</div>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 text-sm text-slate-600 sm:grid-cols-3">
                                <div class="rounded-2xl bg-slate-50 p-3"><span class="block font-black text-slate-950">Class days</span>{{ implode(', ', (array) $batch->class_days) ?: 'Announced soon' }}</div>
                                <div class="rounded-2xl bg-slate-50 p-3"><span class="block font-black text-slate-950">Class time</span>{{ $batch->class_time ?: 'Contact office' }}</div>
                                <div class="rounded-2xl bg-slate-50 p-3"><span class="block font-black text-slate-950">Duration</span>{{ optional($batch->start_date)->format('M') }} - {{ optional($batch->end_date)->format('M Y') }}</div>
                            </div>

                            @if($batch->mentors->count())
                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach($batch->mentors as $mentorUser)
                                        <span class="rounded-full border border-[#292b86]/10 bg-[#292b86]/5 px-3 py-1 text-xs font-bold text-[#292b86]">{{ $mentorUser->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-white py-14">
        <div class="brand-container grid gap-8 lg:grid-cols-[.95fr_1.05fr] lg:items-center">
            <div class="reveal rounded-[2rem] bg-gradient-to-br from-[#292b86] to-[#ed1c24] p-8 text-white shadow-2xl shadow-[#292b86]/15">
                <div class="inline-flex rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em]">Why iTechBD</div>
                <h2 class="mt-5 text-3xl font-black sm:text-4xl">{{ optional($homeAboutTitle)->title ?: 'Practical learning for real outcomes' }}</h2>
                <div class="mt-4 text-white/80 site-prose [&_p]:text-white/80">
                    {!! optional($homeAboutSubtitle)->content ?: '<p>We connect courses, batches, mentors, and student enrollment flow in one platform so learners can move from admission to class schedule smoothly.</p>' !!}
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['home_about_card_1', 'home_about_card_2', 'home_about_card_3'] as $index => $key)
                    @php($card = $cmsSectionsByKey->get($key))
                    <div class="reveal rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm {{ $index === 2 ? 'sm:col-span-2' : '' }}">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f15a24]/10 text-[#f15a24]"><i class="fa-solid fa-check"></i></div>
                        <h3 class="mt-5 text-lg font-black text-slate-950">{{ optional($card)->title ?: ['Mentor-led learning', 'Portfolio-focused practice', 'Career support'][$index] }}</h3>
                        <div class="mt-2 text-sm leading-6 text-slate-600 site-prose">
                            {!! optional($card)->content ?: '<p>Structured guidance, feedback, and practical tasks help learners build confidence.</p>' !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if($mentors->count())
        <section class="py-14">
            <div class="brand-container">
                <x-site.section-title kicker="Expert Mentors" title="Learn from industry practitioners" subtitle="Mentors are loaded from the mentors module and connected user profiles when available." :action-url="route('mentors')" action-label="View all mentors" />

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($mentors as $mentor)
                        <x-site.mentor-card :mentor="$mentor" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($latestNews->count())
        <section class="py-14">
            <div class="brand-container">
                <x-site.section-title kicker="News & Updates" title="Latest announcements" subtitle="Published news from the database appears automatically." :action-url="route('news')" action-label="All news" />
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach($latestNews as $news)
                        <x-site.news-card :news="$news" class="reveal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($reviews->count())
        <section class="bg-white py-14">
            <div class="brand-container">
                <x-site.section-title kicker="Success Stories" title="What learners say" subtitle="Only approved reviews from your reviews table are shown here." :action-url="route('reviews')" action-label="See all reviews" />
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach($reviews as $review)
                        <article class="reveal rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex gap-1 text-[#f15a24]">
                                @for($i = 1; $i <= (int) $review->rating; $i++) <i class="fa-solid fa-star"></i> @endfor
                            </div>
                            <p class="mt-4 text-sm leading-7 text-slate-600">&ldquo;{{ $review->quote }}&rdquo;</p>
                            <div class="mt-5 font-black text-slate-950">{{ $review->name }}</div>
                            @if($review->designation)<div class="text-sm text-slate-500">{{ $review->designation }}</div>@endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="py-14">
        <div class="brand-container">
            <div class="reveal overflow-hidden rounded-[2rem] bg-gradient-to-r from-[#292b86] via-[#30339a] to-[#f15a24] p-8 text-white shadow-2xl shadow-[#292b86]/20 lg:p-10">
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <div class="text-sm font-extrabold uppercase tracking-[0.18em] text-white/70">Start Learning</div>
                        <h2 class="mt-3 text-3xl font-black sm:text-4xl">Join an upcoming batch and build professional skills.</h2>
                        <p class="mt-3 max-w-2xl text-white/80">Course, batch, mentor and enrollment data will stay dynamic from your admin panel.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('courses') }}" class="rounded-full bg-white px-6 py-3 text-center text-sm font-extrabold text-[#292b86] transition hover:bg-slate-100">Explore Courses</a>
                        <a href="{{ url('/contact') }}" class="rounded-full border border-white/30 px-6 py-3 text-center text-sm font-extrabold text-white transition hover:bg-white/10">Contact Office</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
