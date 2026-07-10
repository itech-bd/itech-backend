<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View|RedirectResponse
    {
        $intended = (string) session()->get('url.intended', '');
        $intendedPath = (string) parse_url($intended, PHP_URL_PATH);

        if ($intendedPath !== '' && (Str::startsWith($intendedPath, ['/admin', '/dashboard']))) {
            return view('auth.forgot-password');
        }

        $previous = (string) url()->previous();
        $previousPath = (string) parse_url($previous, PHP_URL_PATH);
        $isUnsafePrevious = $previousPath === ''
            || Str::startsWith($previousPath, ['/login', '/register', '/forgot-password', '/admin', '/dashboard']);

        $target = $isUnsafePrevious ? '' : $previous;
        if ($target === '') {
            $target = route('home', absolute: false);
        }

        return redirect()->to($target)->with('auth_modal', 'forgot');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($request->expectsJson()) {
            if ($status == Password::RESET_LINK_SENT) {
                return response()->json([
                    'ok' => true,
                    'message' => __($status),
                ]);
            }

            return response()->json([
                'message' => __($status),
                'errors' => ['email' => [__($status)]],
            ], 422);
        }

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
