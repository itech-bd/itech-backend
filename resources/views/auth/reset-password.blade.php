@extends('layouts.site')

@section('title', __('Reset Password') . ' | ' . config('app.name', 'iTechBD Ltd'))

@section('content')
    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-md rounded-2xl bg-white/90 p-6 shadow-sm ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10 sm:p-7">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Reset Password') }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-200">{{ __('Please enter your email address and a new password to reset your account.') }}</p>

            <form method="POST" action="{{ route('password.store', absolute: false) }}" class="mt-6">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" class="mt-2 w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-white/10 dark:bg-white/5 dark:text-white" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-slate-600 dark:text-slate-300">
                <a href="{{ route('login', absolute: false) }}" class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200">
                    {{ __('frontend.login') }}
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const params = new URLSearchParams(window.location.search);
                const auth = params.get('auth');
                if (!auth) return;

                const map = {
                    login: 'auth-login',
                    register: 'auth-register',
                    forgot: 'auth-forgot',
                    'reset-success': 'auth-reset-success'
                };

                const modalName = map[auth] || auth;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: modalName }));
            } catch (e) {
                // ignore
            }
        });
    </script>
@endpush
