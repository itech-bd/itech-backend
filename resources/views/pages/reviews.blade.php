@extends('layouts.site')

@section('title', __('frontend.reviews') . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php($hero = $cmsSectionsByKey->get('hero'))
<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'Student Reviews'" :subtitle="optional($hero)->content ?: 'Approved learner feedback and success stories.'" badge="Reviews" />

    <section class="py-12">
        <div class="brand-container">
            @if($reviews->count())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($reviews as $review)
                        <article class="reveal rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex gap-1 text-[#f15a24]">
                                @for($i = 1; $i <= (int) $review->rating; $i++) <i class="fa-solid fa-star"></i> @endfor
                            </div>
                            <p class="mt-4 text-sm leading-7 text-slate-600">“{{ $review->quote }}”</p>
                            <div class="mt-5 font-black text-slate-950">{{ $review->name }}</div>
                            @if($review->designation)<div class="text-sm text-slate-500">{{ $review->designation }}</div>@endif
                        </article>
                    @endforeach
                </div>
                <div class="mt-10">{{ $reviews->links() }}</div>
            @else
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-10 text-center">
                    <h2 class="text-2xl font-black text-slate-950">No approved reviews yet</h2>
                    <p class="mt-2 text-slate-600">Approved reviews from the reviews table will appear here automatically.</p>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
