<x-modal name="auth-verify-status" maxWidth="md" focusable>
    <div data-auth-modal="verify-status" class="p-6 sm:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ session('status') === 'already-verified' ? __('frontend.email_already_verified') : __('frontend.verification_thank_you') }}
                </h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">
                    {{ session('status') === 'already-verified' ? __('frontend.verification_already_confirmed_help') : __('frontend.verification_confirmed_help') }}
                </p>
            </div>

            <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                    x-on:click="$dispatch('close-modal', 'auth-verify-status')"
                    aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            {{ session('status') === 'already-verified' ? __('frontend.email_already_verified') : __('frontend.email_verified_success') }}
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button type="button"
                    data-auth-switch="login"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100">
                {{ __('frontend.login') }}
            </button>

            <button type="button"
                    x-on:click="$dispatch('close-modal', 'auth-verify-status')"
                    class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/10">
                {{ __('frontend.back_to_home') }}
            </button>
        </div>
    </div>
</x-modal>
