@extends('layouts.site')

@section('title', __('frontend.login') . ' | ' . config('app.name', 'iTechBD Ltd'))

@section('content')
    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-md rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10 sm:p-7">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('frontend.login') }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">{{ __('frontend.login_subtitle') }}</p>

            @if (session('status') === 'verified')
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                    {{ __('frontend.email_verified_success') }}
                </div>
            @elseif (session('status') === 'already-verified')
                <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800">
                    {{ __('frontend.email_already_verified') }}
                </div>
            @elseif (session('status'))
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/5 dark:text-white/90">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login', absolute: false) }}" data-recaptcha-action="login" class="mt-5">
                @csrf

                <div>
                    <label for="email" class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('frontend.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                        class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                    @error('email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="password" class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('frontend.password') }}</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required
                        class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                    @error('password')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                @if (config('recaptcha.enabled') && config('recaptcha.site_key'))
                    @once
                        @push('scripts')
                            @if (config('recaptcha.version') === 'v3')
                                <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
                            @else
                                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                            @endif
                            <script>
                                window.__recaptcha = {
                                    enabled: true,
                                    version: @json(config('recaptcha.version')),
                                    siteKey: @json(config('recaptcha.site_key')),
                                };
                            </script>
                        @endpush
                    @endonce

                    <div class="mt-4">
                        @if (config('recaptcha.version') === 'v3')
                            <input type="hidden" name="g-recaptcha-response" value="" />
                        @else
                            <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.site_key') }}"></div>
                        @endif
                        @error('g-recaptcha-response')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between gap-3">
                    <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-white/20 dark:bg-white/5" />
                        <span>{{ __('frontend.remember_me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="/forgot-password" class="text-sm font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                            {{ __('frontend.forgot_password') }}
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100">
                    {{ __('frontend.login') }}
                </button>

                <div class="mt-4 text-center text-sm text-slate-600 dark:text-slate-200">
                    {{ __('frontend.no_account') }}
                    <a href="/register" class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                        {{ __('frontend.register') }}
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
