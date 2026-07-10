<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUnverifiedEmailController extends Controller
{
    /**
     * Update an unverified user's email and resend verification.
     *
     * Supports both logged-in unverified users and guest users.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'current_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'new_email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'different:current_email',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string'],
        ]);

        /** @var \App\Models\User|null $user */
        $user = null;

        if ($authUser instanceof User) {
            if ($authUser->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard', absolute: false));
            }

            if (! hash_equals((string) $authUser->email, (string) $validated['current_email'])) {
                throw ValidationException::withMessages([
                    'current_email' => [__('frontend.verification_credentials_invalid')],
                ]);
            }

            $user = $authUser;
        } else {
            $user = User::query()
                ->where('email', (string) $validated['current_email'])
                ->first();

            if (! $user || $user->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    'current_email' => [__('frontend.verification_credentials_invalid')],
                ]);
            }
        }

        if (! Hash::check((string) $validated['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('frontend.verification_credentials_invalid')],
            ]);
        }

        $newEmail = (string) $validated['new_email'];

        $user->forceFill([
            'email' => $newEmail,
            'email_verified_at' => null,
        ])->save();

        session()->put('verification_email', $newEmail);

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-email-updated');
    }
}
