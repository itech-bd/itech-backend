<?php

namespace App\Http\Requests\Auth;

use App\Rules\Recaptcha;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        $shouldRequireRecaptcha = config('recaptcha.enabled')
            && ! (config('recaptcha.skip_in_testing') && app()->environment('testing'));

        if ($shouldRequireRecaptcha) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha('login')];
        }

        return $rules;
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');

        $attempted = false;

        try {
            $attempted = Auth::attempt($credentials, $remember);
        } catch (RuntimeException $e) {
            // Fallback for legacy bcrypt hashes (e.g. "$2a$") that may fail strict algorithm checks.
            if (! Str::contains($e->getMessage(), 'Bcrypt algorithm')) {
                throw $e;
            }

            $provider = Auth::getProvider();
            $legacyUser = $provider->retrieveByCredentials([
                'email' => $credentials['email'] ?? null,
            ]);

            $plainPassword = (string) ($credentials['password'] ?? '');
            $storedHash = (string) ($legacyUser?->password ?? '');

            if ($legacyUser && Str::startsWith($storedHash, '$2a$') && password_verify($plainPassword, $storedHash)) {
                Auth::login($legacyUser, $remember);
                $attempted = true;

                try {
                    if (Hash::needsRehash($storedHash)) {
                        $legacyUser->forceFill([
                            'password' => Hash::make($plainPassword),
                        ])->save();
                    }
                } catch (\Throwable $rehashError) {
                    // Ignore rehash/storage issues; keep successful login.
                }
            }
        }

        if (! $attempted) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $email = (string) ($user->email ?? '');

            Auth::logout();

            if ($email !== '') {
                $this->session()->put('verification_email', $email);

                try {
                    $user->sendEmailVerificationNotification();
                    $this->session()->flash('status', 'verification-link-sent');
                } catch (\Throwable $e) {
                    // Mail may be misconfigured; still redirect to verification prompt.
                }
            }

            $exception = ValidationException::withMessages([
                'email' => [trans('frontend.email_not_verified')],
            ]);

            if ($email !== '') {
                $exception->redirectTo(route('verification.notice', ['email' => $email]));
            }

            throw $exception;
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
