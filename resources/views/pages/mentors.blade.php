@extends('layouts.site')

@section('title', __('frontend.mentors') . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php
    $hero = $cmsSectionsByKey->get('hero');
@endphp

<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'Expert Mentors'" :subtitle="optional($hero)->content ?: 'Meet the mentors behind iTechBD courses and upcoming batches.'" badge="Mentors" />

    <section class="py-12">
        <div class="brand-container">
            @if($mentors->count())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($mentors as $mentor)
                        <x-site.mentor-card :mentor="$mentor" class="reveal" />
                    @endforeach
                </div>
                <div class="mt-10">{{ $mentors->links() }}</div>
            @else
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center">
                    <h2 class="text-2xl font-black text-slate-950">No mentors found</h2>
                    <p class="mt-2 text-slate-600">Add active mentors from the admin panel to display them here.</p>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
