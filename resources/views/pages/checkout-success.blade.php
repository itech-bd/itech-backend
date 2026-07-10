@extends('layouts.site')

@section('title', __('frontend.order_confirmed') . ' • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
<main>
    <x-site.page-hero :title="__('frontend.order_confirmed')" :subtitle="__('frontend.order_confirmed_subtitle')" badge="Enrollment" />

    <section class="py-12">
        <div class="brand-container max-w-5xl">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-[#292b86]/10 lg:p-10">
                <div class="inline-flex rounded-full bg-emerald-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700 ring-1 ring-emerald-200">
                    {{ __('frontend.order_confirmed') }}
                </div>

                <h1 class="mt-5 text-3xl font-black text-slate-950">{{ __('frontend.thanks_for_order') }}</h1>
                <p class="mt-3 text-slate-600">{{ __('frontend.order_confirmed_subtitle') }}</p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[1.25rem] bg-slate-50 p-5"><div class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('frontend.order_id') }}</div><div class="mt-1 text-lg font-black text-slate-950">#{{ $order->id }}</div></div>
                    <div class="rounded-[1.25rem] bg-slate-50 p-5"><div class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('frontend.selected_course') }}</div><div class="mt-1 text-lg font-black text-slate-950">{{ $order->course->title }}</div></div>
                    <div class="rounded-[1.25rem] bg-slate-50 p-5"><div class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('frontend.amount') }}</div><div class="mt-1 text-lg font-black text-[#f15a24]">{{ number_format((float) $order->amount, 2) }} {{ $order->currency }}</div></div>
                </div>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ url('/contact') }}" class="rounded-full bg-[#f15a24] px-6 py-3 text-center text-sm font-extrabold text-white hover:bg-[#ed1c24]">{{ __('frontend.contact') }}</a>
                    <a href="{{ route('courses') }}" class="rounded-full border border-[#292b86]/15 px-6 py-3 text-center text-sm font-extrabold text-[#292b86] hover:bg-[#292b86] hover:text-white">{{ __('frontend.explore_courses') }}</a>
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-slate-200 px-6 py-3 text-center text-sm font-extrabold text-slate-700 hover:border-[#f15a24] hover:text-[#f15a24]">{{ __('frontend.dashboard') }}</a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
