@extends('layouts.site')

@section('title', $mentor->name . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php

    $user = $mentor->user;
    $imagePath = optional($user)->profile_image;
    $imageUrl = is_string($imagePath) && trim($imagePath) !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($imagePath, '/')) : null;
    $initials = collect(explode(' ', $mentor->name))->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->implode('');
@endphp

<main>
    <section class="relative overflow-hidden bg-gradient-to-br from-[#292b86] via-[#30339a] to-[#ed1c24] text-white">
        <div class="absolute inset-0 opacity-15 [background-image:radial-gradient(#ffffff_1px,transparent_1px)] [background-size:22px_22px]"></div>
        <div class="relative brand-container grid gap-10 py-14 lg:grid-cols-[320px_1fr] lg:items-center lg:py-20">
            <div class="reveal mx-auto h-64 w-64 overflow-hidden rounded-[2.5rem] bg-white/15 p-2 shadow-2xl shadow-black/20">
                <div class="h-full w-full overflow-hidden rounded-[2rem] bg-white">
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $mentor->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-white text-5xl font-black text-[#292b86]">{{ $initials ?: 'M' }}</div>
                    @endif
                </div>
            </div>
            <div class="reveal">
                <a href="{{ route('mentors') }}" class="inline-flex text-sm font-extrabold text-white/80 hover:text-white">← Back to mentors</a>
                <h1 class="mt-5 text-4xl font-black sm:text-5xl">{{ $mentor->name }}</h1>
                @if($mentor->topic)
                    <p class="mt-3 text-xl font-bold text-[#ffd3c2]">{{ $mentor->topic }}</p>
                @endif
                @if(optional($user)->email)
                    <a href="mailto:{{ $user->email }}" class="mt-5 inline-flex rounded-full bg-white/10 px-4 py-2 text-sm font-bold ring-1 ring-white/20 hover:bg-white/20">{{ $user->email }}</a>
                @endif
            </div>
        </div>
    </section>

    <section class="py-12">
        <div class="brand-container grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                <h2 class="text-2xl font-black text-slate-950">Profile</h2>
                <div class="site-prose mt-4">
                    {!! $mentor->bio ?: '<p>Mentor profile details will appear here once added from the admin panel.</p>' !!}
                </div>
            </div>

            <aside class="space-y-6">
                @if($user && $user->skills && $user->skills->count())
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-black text-slate-950">Skills</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($user->skills as $skill)
                                <span class="rounded-full bg-[#292b86]/10 px-3 py-1 text-xs font-bold text-[#292b86]">{{ $skill->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($user && $user->experiences && $user->experiences->count())
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-black text-slate-950">Experience</h2>
                        <div class="mt-4 grid gap-4">
                            @foreach($user->experiences->take(4) as $experience)
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <div class="font-bold text-slate-950">{{ $experience->title ?? $experience->designation ?? 'Experience' }}</div>
                                    <div class="text-sm text-slate-600">{{ $experience->company ?? '' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    @if($relatedCourses->count())
        <section class="bg-white py-12">
            <div class="brand-container">
                <x-site.section-title kicker="Related Courses" title="Courses related to this mentor" />
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach($relatedCourses as $course)
                        <x-site.course-card :course="$course" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
@endsection
