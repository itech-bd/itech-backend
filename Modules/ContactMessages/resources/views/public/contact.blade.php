@extends('layouts.site')

@section('title', 'Contact • ' . config('app.name', 'iTechBD Ltd'))

@section('content')
@php
    $hero = $cmsSectionsByKey->get('hero');
    $emailSection = $cmsSectionsByKey->get('contact_email');
    $phoneSection = $cmsSectionsByKey->get('contact_phone');
    $currentUser = auth()->user();

    $normalizeInlineText = function ($value): string {
        $text = trim((string) $value);
        if ($text === '') return '';
        $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text);
    };

    $emailValue = $normalizeInlineText(optional($emailSection)->content);
    if ($emailValue !== '' && preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $emailValue, $m)) $emailValue = $m[0];
    $emailValue = $emailValue ?: ($frontendSettings['site_email'] ?? 'info@example.com');
    $emailHref = optional($emailSection)->button_link ?: ('mailto:' . $emailValue);

    $phoneValue = $normalizeInlineText(optional($phoneSection)->content);
    if ($phoneValue !== '' && preg_match('/\+?[0-9][0-9\s\-().]{6,}/', $phoneValue, $m)) $phoneValue = trim($m[0]);
    $phoneValue = $phoneValue ?: ($frontendSettings['site_phone'] ?? '+880 10 0000 0000');
    $phoneHref = optional($phoneSection)->button_link ?: ('tel:' . preg_replace('/[^\d+]/', '', $phoneValue));
@endphp

<main>
    <x-site.page-hero :title="optional($hero)->title ?: 'Contact iTechBD'" :subtitle="optional($hero)->content ?: 'Have questions about admission, courses, batches, or software services? Send us a message.'" badge="Contact" />

    <section class="py-12">
        <div class="brand-container grid gap-8 lg:grid-cols-[1fr_390px]">
            <div id="contact-form" class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                <h2 class="text-2xl font-black text-slate-950">Send us a message</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Your message will be saved in the contact messages module and reviewed by the admin team.</p>

                @if (session('contact_success'))
                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                        {{ session('contact_success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-5" data-recaptcha-action="contact">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="contact_name" class="block text-sm font-bold text-slate-900">Full name</label>
                            <input id="contact_name" name="name" type="text" value="{{ old('name', $currentUser?->name) }}" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]" required>
                            @error('name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="contact_email_input" class="block text-sm font-bold text-slate-900">Email address</label>
                            <input id="contact_email_input" name="email" type="email" value="{{ old('email', $currentUser?->email) }}" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]" required>
                            @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="contact_phone" class="block text-sm font-bold text-slate-900">Phone number</label>
                            <input id="contact_phone" name="phone" type="text" value="{{ old('phone') }}" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]" required>
                            @error('phone') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="contact_subject" class="block text-sm font-bold text-slate-900">Subject</label>
                            <input id="contact_subject" name="subject" type="text" value="{{ old('subject') }}" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]" required>
                            @error('subject') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="contact_message" class="block text-sm font-bold text-slate-900">Message</label>
                        <textarea id="contact_message" name="message" rows="6" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]" required>{{ old('message') }}</textarea>
                        @error('message') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    @if (config('recaptcha.enabled') && config('recaptcha.site_key'))
                        <div>
                            @if (config('recaptcha.version') === 'v3')
                                <input type="hidden" name="g-recaptcha-response" value="" />
                            @else
                                <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.site_key') }}"></div>
                            @endif
                            @error('g-recaptcha-response') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <button type="submit" class="rounded-full bg-[#f15a24] px-7 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#f15a24]/20 transition hover:bg-[#ed1c24]">Send Message</button>
                </form>
            </div>

            <aside class="space-y-6">
                <div class="rounded-[1.75rem] bg-[#292b86] p-6 text-white shadow-xl shadow-[#292b86]/15">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10"><i class="fa-solid fa-envelope"></i></div>
                    <h3 class="mt-5 text-xl font-black">{{ optional($emailSection)->title ?: 'Email' }}</h3>
                    <p class="mt-2 text-sm text-white/75">Best for admission, support, and service enquiries.</p>
                    <a href="{{ $emailHref }}" class="mt-4 inline-flex font-bold text-[#ffd5c7] hover:text-white">{{ $emailValue }}</a>
                </div>

                <div class="rounded-[1.75rem] bg-[#f15a24] p-6 text-white shadow-xl shadow-[#f15a24]/15">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10"><i class="fa-solid fa-phone"></i></div>
                    <h3 class="mt-5 text-xl font-black">{{ optional($phoneSection)->title ?: 'Phone' }}</h3>
                    <p class="mt-2 text-sm text-white/85">Call for course, batch, and admission information.</p>
                    <a href="{{ $phoneHref }}" class="mt-4 inline-flex font-bold text-white hover:underline">{{ $phoneValue }}</a>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-xl font-black text-slate-950">Office Address</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $frontendSettings['site_address'] ?? 'Dhaka, Bangladesh' }}</p>
                </div>
            </aside>
        </div>
    </section>
</main>
@endsection

@push('scripts')
@if (config('recaptcha.enabled') && config('recaptcha.site_key'))
    @if (config('recaptcha.version') === 'v3')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}" async defer></script>
        <script>
            window.__recaptcha = {
                enabled: true,
                version: 'v3',
                siteKey: @json(config('recaptcha.site_key')),
            };
        </script>
    @else
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
@endif
@endpush
