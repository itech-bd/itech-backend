<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Accounts;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerifyEmailController extends Controller
{
    /**
     * Verify the user's email address (supports guest verification).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $userId = (string) $request->route('id');
        $hash = (string) $request->route('hash');

        $match = Accounts::findForVerification($userId, $hash);
        $user = $match['account'] ?? null;
        if (! $user) {
            return redirect()->to(route('verification.notice', [], false))
                ->with('status', 'verification-invalid');
        }

        $alreadyVerified = $user->hasVerifiedEmail();

        if (! $alreadyVerified) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        $status = $alreadyVerified ? 'already-verified' : 'verified';

        // If the visitor is already logged in, take them to the dashboard.
        // Otherwise, treat this as *guest verification*: clear any stale intended URL
        // (commonly /dashboard), and redirect to home with the login modal open.
        $currentUser = $request->user();
        if ($currentUser && (string) $currentUser->getAuthIdentifier() === (string) $user->getAuthIdentifier()) {
            return redirect()->to(route('dashboard', [], false))
                ->with('status', $status);
        }

        $request->session()->forget('url.intended');

        return redirect()->to(route('home', [], false) . '?auth=login&verified=1')
            ->with('status', $status)
            ->with('auth_modal', 'auth-verify-status');
    }
}
