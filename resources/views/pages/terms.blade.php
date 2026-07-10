@extends('layouts.site')

@section('title', 'Terms & Conditions • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php($hero = $cmsSectionsByKey->get('hero'))
<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'Terms & Conditions'" :subtitle="'Course enrollment, platform use, and service terms.'" badge="Legal" />
    <section class="py-12">
        <div class="brand-container max-w-4xl">
            <article class="site-prose rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                {!! optional($hero)->content ?: '<p>Replace this placeholder text with your real terms and conditions from the frontend CMS editor.</p>' !!}
            </article>
        </div>
    </section>
</main>
@endsection
