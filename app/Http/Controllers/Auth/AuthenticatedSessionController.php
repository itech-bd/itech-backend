<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private function isUnsafeRedirectPath(string $path): bool
    {
        if ($path === '') {
            return true;
        }

        return Str::startsWith($path, ['/login', '/register', '/forgot-password', '/reset-password', '/email/verify']);
    }

    /**
     * Display the login view.
     */
    public function create(): View|RedirectResponse
    {
        $intended = (string) session()->get('url.intended', '');
        $intendedPath = (string) parse_url($intended, PHP_URL_PATH);

        $courseIdForCheckout = null;
        if (preg_match('#^/courses/(\d+)/checkout$#', $intendedPath, $m)) {
            $courseIdForCheckout = (string) $m[1];
        }

        if ($intendedPath !== '' && (Str::startsWith($intendedPath, ['/admin', '/dashboard']))) {
            return view('auth.login');
        }

        $previous = (string) url()->previous();
        $previousPath = (string) parse_url($previous, PHP_URL_PATH);

        // If user landed here from email verification (or directly), show the full login page.
        // This avoids a confusing redirect loop where /login redirects to home but the modal
        // cannot open due to missing flash/session in some environments.
        if ($previousPath === '' || preg_match('#^/email/verify(?:/|$)#', $previousPath)) {
            return view('auth.login');
        }

        if (is_null($courseIdForCheckout) && preg_match('#^/courses/(\d+)/checkout$#', $previousPath, $m)) {
            $courseIdForCheckout = (string) $m[1];
        }

        $isUnsafePrevious = $previousPath === ''
            || Str::startsWith($previousPath, ['/login', '/register', '/forgot-password', '/reset-password', '/admin', '/dashboard'])
            || preg_match('#^/email/verify(?:/|$)#', $previousPath)
            || preg_match('#^/courses/\d+/checkout$#', $previousPath)
            || preg_match('#^/checkout/orders/\d+$#', $previousPath);

        $target = '';

        if (! is_null($courseIdForCheckout)) {
            $target = url('/courses/' . $courseIdForCheckout);
        } elseif (! $isUnsafePrevious) {
            $target = $previous;
        }

        if ($target === '') {
            $target = route('home', absolute: false);
        }

        return redirect()->to($target)->with('auth_modal', 'login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            // For AJAX (modal) requests, detect the "email not verified" case and
            // return a structured response so the modal can show the resend panel.
            if ($request->expectsJson()) {
                $emailErrors = $e->errors()['email'] ?? [];
                if (in_array(trans('frontend.email_not_verified'), (array) $emailErrors, true)) {
                    return response()->json([
                        'message'               => $e->getMessage(),
                        'errors'                => $e->errors(),
                        'verification_required' => true,
                        'email'                 => (string) $request->input('email', ''),
                    ], 422);
                }
            }
            throw $e;
        }

        $request->session()->regenerate();

        $user = $request->user();
        if ($user && ($user->must_change_password ?? false)) {
            try {
                if (method_exists($user, 'hasRole') && $user->hasRole('mentor')) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'ok' => true,
                            'redirect' => route('profile.edit', absolute: false),
                            'message' => 'must-change-password',
                        ]);
                    }

                    return redirect()->to(route('profile.edit', absolute: false))
                        ->with('status', 'must-change-password');
                }
            } catch (\Throwable $e) {
                // If role check fails for any reason, continue normal flow.
            }
        }

        if ($request->expectsJson()) {
            $redirectTo = '';
            $redirectToRaw = trim((string) $request->input('redirect_to', ''));

            if ($redirectToRaw !== '') {
                if (Str::startsWith($redirectToRaw, ['/']) && ! Str::startsWith($redirectToRaw, ['//'])) {
                    $redirectTo = $redirectToRaw;
                } elseif (filter_var($redirectToRaw, FILTER_VALIDATE_URL)) {
                    $host = (string) parse_url($redirectToRaw, PHP_URL_HOST);
                    if ($host !== '' && strcasecmp($host, (string) $request->getHost()) === 0) {
                        $path = (string) (parse_url($redirectToRaw, PHP_URL_PATH) ?? '/');
                        $query = (string) parse_url($redirectToRaw, PHP_URL_QUERY);
                        $fragment = (string) parse_url($redirectToRaw, PHP_URL_FRAGMENT);
                        $redirectTo = $path
                            . ($query !== '' ? ('?' . $query) : '')
                            . ($fragment !== '' ? ('#' . $fragment) : '');
                    }
                }
            }

            if ($redirectTo === '') {
                $redirectTo = (string) $request->session()->pull('url.intended', route('dashboard', absolute: false));
            }

            $redirectToPath = (string) parse_url($redirectTo, PHP_URL_PATH);
            if ($this->isUnsafeRedirectPath($redirectToPath)) {
                $redirectTo = route('dashboard', [], false);
            }

            return response()->json([
                'ok' => true,
                'redirect' => $redirectTo,
                'message' => __('frontend.login_success'),
            ]);
        }

        $fallback = route('dashboard', [], false);
        $intended = (string) $request->session()->pull('url.intended', '');
        $intendedPath = (string) parse_url($intended, PHP_URL_PATH);

        if ($intended !== '' && ! $this->isUnsafeRedirectPath($intendedPath)) {
            return redirect()->to($intended);
        }

        return redirect()->to($fallback);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
