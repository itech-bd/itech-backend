<x-modal name="auth-login" maxWidth="md" focusable>
    <div data-auth-modal="login" class="p-6 sm:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('frontend.login') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">{{ __('frontend.login_subtitle') }}</p>
            </div>

            <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                    x-on:click="$dispatch('close-modal', 'auth-login')"
                    aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <div data-auth-alert class="{{ session('status') ? 'block' : 'hidden' }} mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/5 dark:text-white/90">
            @if (session('status'))
                {{ session('status') }}
            @endif
        </div>

        <div data-auth-panel="login-form">
        <form method="POST" action="{{ route('login', absolute: false) }}" class="mt-5" data-auth-form="login" data-recaptcha-action="login">
            @csrf
            <input type="hidden" name="redirect_to" value="" data-auth-redirect-to>

            <div>
                <label for="auth_login_email" class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('frontend.email') }}</label>
                <input id="auth_login_email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required
                       class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                <div class="mt-2 text-sm text-rose-600" data-auth-error-for="email">
                    @foreach($errors->get('email') as $message)
                        <div>{{ $message }}</div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                <label for="auth_login_password" class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('frontend.password') }}</label>
                <input id="auth_login_password" type="password" name="password" autocomplete="current-password" required
                       class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                <div class="mt-2 text-sm text-rose-600" data-auth-error-for="password">
                    @foreach($errors->get('password') as $message)
                        <div>{{ $message }}</div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-white/20 dark:bg-white/5" />
                    <span>{{ __('frontend.remember_me') }}</span>
                </label>

                <a href="/forgot-password" data-auth-switch="forgot" class="text-sm font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                    {{ __('frontend.forgot_password') }}
                </a>
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
                    <div class="mt-2 text-sm text-rose-600" data-auth-error-for="g-recaptcha-response">
                        @foreach($errors->get('g-recaptcha-response') as $message)
                            <div>{{ $message }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <button type="submit"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                    data-auth-submit>
                {{ __('frontend.login') }}
            </button>

            <div class="mt-4 text-center text-sm text-slate-600 dark:text-slate-200">
                {{ __('frontend.no_account') }}
                <a href="/register" data-auth-switch="register" class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                    {{ __('frontend.register') }}
                </a>
            </div>
        </form>
        </div>

        <div data-auth-panel="login-verify" class="hidden mt-5">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('frontend.verify_email_title') }}</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-200" data-verify-message>
                {{ __('frontend.verification_link_sent') }}
            </p>

            <p class="mt-2 text-xs text-slate-500 dark:text-slate-300">
                {{ __('frontend.verification_sent_to') }}
                <span class="font-medium" data-verify-email></span>
            </p>

            <form method="POST" action="{{ route('verification.send', absolute: false) }}" class="mt-4">
                @csrf
                <input type="hidden" name="email" value="">

                <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                        data-auth-resend-verification>
                    {{ __('frontend.resend_verification') }}
                </button>
            </form>

            <div class="mt-4 text-center text-sm text-slate-600 dark:text-slate-200">
                <a href="{{ route('verification.notice', absolute: false) }}" class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200" data-verify-change-email-link>
                    {{ __('frontend.change_verification_email') }}
                </a>
            </div>

            <div class="mt-2 text-center text-sm text-slate-600 dark:text-slate-200">
                <a href="/login" data-auth-switch="login" data-auth-switch-reset="login"
                   class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                    ← {{ __('frontend.back_to_login') }}
                </a>
            </div>
        </div>
    </div>
</x-modal>
