<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use RuntimeException;
use Spatie\Permission\Models\Role;

class AuthController extends ApiController
{
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if ($this->recaptchaRequired()) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha('register')];
        }

        $data = $request->validate($rules);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'password' => Hash::make($data['password']),
        ]);

        $studentRole = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'web',
        ]);
        $user->assignRole($studentRole);

        event(new Registered($user));

        return $this->success([
            'user' => $this->userPayload($user),
            'verification_required' => $user instanceof MustVerifyEmail,
        ], 'Registration successful. Please verify your email before signing in.', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'issue_token' => ['nullable', 'boolean'],
        ];

        if ($this->recaptchaRequired()) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha('login')];
        }

        $data = $request->validate($rules);
        $key = Str::transliterate(Str::lower($data['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->failure(
                'Too many login attempts. Please try again later.',
                429,
                'LOGIN_THROTTLED',
                ['retry_after' => RateLimiter::availableIn($key)]
            );
        }

        $user = User::query()->where('email', Str::lower($data['email']))->first();

        if (! $user || ! $this->passwordMatches($data['password'], (string) $user->password)) {
            RateLimiter::hit($key, 60);

            return $this->failure('The provided credentials are incorrect.', 422, 'INVALID_CREDENTIALS', [
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        RateLimiter::clear($key);

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return $this->failure(
                'Your email address is not verified.',
                403,
                'EMAIL_NOT_VERIFIED',
                ['email' => $user->email]
            );
        }

        if ($this->passwordNeedsRehash((string) $user->password)) {
            $user->forceFill(['password' => Hash::make($data['password'])])->save();
        }

        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }

        $token = null;
        if ($request->boolean('issue_token', true)) {
            $tokenName = trim((string) ($data['device_name'] ?? 'nextjs-web')) ?: 'nextjs-web';
            $token = $user->createToken($tokenName, ['student-panel'])->plainTextToken;
        }

        $user->loadMissing(['roles', 'permissions']);

        return $this->success([
            'token_type' => $token ? 'Bearer' : null,
            'access_token' => $token,
            'user' => $this->userPayload($user),
            'must_change_password' => (bool) $user->must_change_password,
        ], 'Login successful.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing([
            'roles',
            'permissions',
            'profile',
        ]);

        return $this->success($this->userPayload($user, true));
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user?->currentAccessToken();

        if ($currentToken && method_exists($currentToken, 'delete')) {
            $currentToken->delete();
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->success(null, 'Logged out successfully.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->success(null, 'Logged out from all devices.');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', Str::lower($data['email']))->first();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return $this->success(null, 'If the account exists and is unverified, a new verification link has been sent.');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink(['email' => Str::lower($data['email'])]);

        return $this->success(null, 'If the account exists, a password reset link has been sent.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            [
                'email' => Str::lower($data['email']),
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->failure(__($status), 422, 'PASSWORD_RESET_FAILED', [
                'email' => [__($status)],
            ]);
        }

        return $this->success(null, __($status));
    }

    private function passwordMatches(string $plain, string $hash): bool
    {
        try {
            return Hash::check($plain, $hash);
        } catch (RuntimeException $exception) {
            if (! Str::contains($exception->getMessage(), 'Bcrypt algorithm')) {
                throw $exception;
            }

            return Str::startsWith($hash, '$2a$') && password_verify($plain, $hash);
        }
    }

    private function passwordNeedsRehash(string $hash): bool
    {
        try {
            return Hash::needsRehash($hash);
        } catch (RuntimeException $exception) {
            if (! Str::contains($exception->getMessage(), 'Bcrypt algorithm')) {
                throw $exception;
            }

            return Str::startsWith($hash, '$2a$');
        }
    }

    private function recaptchaRequired(): bool
    {
        return (bool) config('recaptcha.enabled')
            && ! (config('recaptcha.skip_in_testing') && app()->environment('testing'));
    }

    private function userPayload(User $user, bool $includeProfile = false): array
    {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => ! is_null($user->email_verified_at),
            'profile_image_url' => $user->profile_image_url,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ];

        if ($includeProfile) {
            $payload['profile'] = $user->profile;
        }

        return $payload;
    }
}
