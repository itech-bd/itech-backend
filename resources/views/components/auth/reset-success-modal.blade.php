<x-modal name="auth-reset-success" maxWidth="md" focusable>
    <div data-auth-modal="reset-success" class="p-6 sm:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ __('frontend.password_updated_success') }}
                </h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">
                    {{ __('Your password has been reset successfully. You can now log in with your new password.') }}
                </p>
            </div>

            <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                    x-on:click="$dispatch('close-modal', 'auth-reset-success')"
                    aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="button"
                    data-auth-switch="login"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100">
                {{ __('frontend.login') }}
            </button>

            <button type="button"
                    x-on:click="$dispatch('close-modal', 'auth-reset-success')"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-white/10">
                {{ __('frontend.back_to_home') }}
            </button>
        </div>
    </div>
</x-modal>
