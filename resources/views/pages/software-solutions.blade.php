@extends('layouts.site')

@section('title', 'Software Solutions • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php($hero = $cmsSectionsByKey->get('hero'))
<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'Software Solutions'" :subtitle="optional($hero)->content ?: 'Custom software, web applications, and business systems tailored to your workflow and growth plan.'" badge="Solutions" />

    <section class="py-12">
        <div class="brand-container grid gap-6 lg:grid-cols-3">
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Custom Web Apps</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Build secure and scalable systems for internal operations, customer portals, and service delivery.</p>
            </article>
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Business Automation</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Reduce manual work with software that streamlines reporting, approvals, billing, and team collaboration.</p>
            </article>
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Ongoing Support</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Get maintenance, updates, and performance improvements after launch so the product stays dependable.</p>
            </article>
        </div>
    </section>
</main>
@endsection
