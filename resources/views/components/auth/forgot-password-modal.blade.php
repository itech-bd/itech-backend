<x-modal name="auth-forgot" maxWidth="md" focusable>
    <div data-auth-modal="forgot" class="p-6 sm:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('frontend.forgot_password_title') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">{{ __('frontend.forgot_password_subtitle') }}</p>
            </div>

            <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                    x-on:click="$dispatch('close-modal', 'auth-forgot')"
                    aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <div data-auth-alert class="hidden mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/5 dark:text-white/90">
            @if (session('status'))
                {{ session('status') }}
            @endif
        </div>

        <form method="POST" action="{{ route('password.email', absolute: false) }}" class="mt-5" data-auth-form="forgot">
            @csrf

            <div>
                <label for="auth_forgot_email" class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('frontend.email') }}</label>
                <input id="auth_forgot_email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                       class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                <div class="mt-2 text-sm text-rose-600" data-auth-error-for="email">
                    @foreach($errors->get('email') as $message)
                        <div>{{ $message }}</div>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100"
                    data-auth-submit>
                {{ __('frontend.send_reset_link') }}
            </button>

            <div class="mt-4 text-center text-sm text-slate-600 dark:text-slate-200">
                <a href="/login" data-auth-switch="login" class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                    ← {{ __('frontend.back_to_login') }}
                </a>
            </div>
        </form>
    </div>
</x-modal>
