<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            $request->validate([
                'email' => ['required', 'string', 'email'],
            ]);

            /** @var \App\Models\User|null $user */
            $user = User::query()->where('email', (string) $request->input('email'))->first();

            // Always return a generic success response to avoid account enumeration.
            if (! $user || $user->hasVerifiedEmail()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => true,
                        'status' => 'verification-link-sent',
                        'message' => __('frontend.verification_link_sent'),
                    ]);
                }

                return back()->with('status', 'verification-link-sent');
            }
        } else {
            if ($user->hasVerifiedEmail()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => true,
                        'status' => 'already-verified',
                        'message' => __('frontend.email_already_verified'),
                    ]);
                }

                return redirect()->intended(route('dashboard', absolute: false));
            }
        }

        $user->sendEmailVerificationNotification();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => 'verification-link-sent',
                'message' => __('frontend.verification_link_sent'),
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
