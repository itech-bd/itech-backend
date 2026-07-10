@extends('layouts.site')

@section('title', 'IT Solutions • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php($hero = $cmsSectionsByKey->get('hero'))
<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'IT Solutions'" :subtitle="optional($hero)->content ?: 'Practical IT support, infrastructure planning, and technical guidance for modern organizations.'" badge="Solutions" />

    <section class="py-12">
        <div class="brand-container grid gap-6 lg:grid-cols-3">
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Infrastructure Setup</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Plan and deploy office networks, devices, and operational systems with stable long-term structure.</p>
            </article>
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Security & Monitoring</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Protect critical systems with access control, monitoring, backup routines, and incident response support.</p>
            </article>
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Technical Consulting</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Choose the right tools and architecture with guidance that fits your budget, team size, and business goals.</p>
            </article>
        </div>
    </section>
</main>
@endsection
