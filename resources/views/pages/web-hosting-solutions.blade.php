@extends('layouts.site')

@section('title', 'Web Hosting Solutions • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php($hero = $cmsSectionsByKey->get('hero'))
<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'Web Hosting Solutions'" :subtitle="optional($hero)->content ?: 'Reliable hosting, deployment, domain support, and uptime-focused environments for websites and web apps.'" badge="Solutions" />

    <section class="py-12">
        <div class="brand-container grid gap-6 lg:grid-cols-3">
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Managed Hosting</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Run websites and applications on optimized servers with monitoring, deployment help, and routine maintenance.</p>
            </article>
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Domain & SSL</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Handle domain setup, DNS configuration, and SSL deployment so your site stays secure and accessible.</p>
            </article>
            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Backup & Recovery</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Keep recovery options ready with regular backups, restore workflows, and uptime-conscious hosting practices.</p>
            </article>
        </div>
    </section>
</main>
@endsection
