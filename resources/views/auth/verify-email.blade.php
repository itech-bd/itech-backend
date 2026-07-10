@extends('layouts.site')

@section('title', __('frontend.verify_email_title') . ' | ' . config('app.name', 'iTechBD Ltd'))

@section('content')
<section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-lg space-y-6">

        {{-- Resend verification card --}}
        <div class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10 sm:p-7">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('frontend.verify_email_title') }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-200">{{ __('frontend.verify_email_notice') }}</p>

            @if (session('status') == 'verification-link-sent')
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                    {{ __('frontend.verification_link_sent') }}
                </div>
            @elseif (session('status') == 'verification-email-updated')
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                    {{ __('frontend.verification_email_updated') }}
                </div>
            @elseif (session('status') == 'verification-invalid')
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300">
                    {{ __('frontend.verification_invalid') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send', absolute: false) }}" class="mt-5">
                @csrf

                @guest
                    <div class="mb-4">
                        <label for="verification_email" class="text-sm font-medium text-slate-700 dark:text-slate-200">
                            {{ __('frontend.email') }}
                        </label>
                        <input id="verification_email" type="email" name="email" autocomplete="email" required
                               value="{{ old('email', $verificationEmail ?? '') }}"
                               class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        @error('email')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endguest

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100 sm:w-auto">
                        {{ __('frontend.resend_verification') }}
                    </button>
                    <a href="/login"
                       class="text-center text-sm font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                        ← {{ __('frontend.back_to_login') }}
                    </a>
                </div>
            </form>
        </div>

        {{-- Change email card --}}
        <div class="rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10 sm:p-7">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('frontend.change_verification_email') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">{{ __('frontend.verification_change_help') }}</p>

            <form method="POST" action="{{ route('verification.email.update', absolute: false) }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="current_email" class="text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('frontend.current_email') }}
                    </label>
                    <input id="current_email" type="email" name="current_email" autocomplete="email" required
                           value="{{ old('current_email', $verificationEmail ?? auth()->user()?->email ?? '') }}"
                           class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                    @error('current_email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_email" class="text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('frontend.new_email') }}
                    </label>
                    <input id="new_email" type="email" name="new_email" autocomplete="email" required
                           value="{{ old('new_email') }}"
                           class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                    @error('new_email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="verify_password" class="text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('frontend.account_password') }}
                    </label>
                    <input id="verify_password" type="password" name="password" autocomplete="current-password" required
                           class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                    @error('password')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100 sm:w-auto">
                    {{ __('frontend.update_email_and_resend') }}
                </button>
            </form>
        </div>

    </div>
</section>
@endsection
