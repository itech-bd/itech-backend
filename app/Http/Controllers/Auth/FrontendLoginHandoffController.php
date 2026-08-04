<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FrontendLoginHandoffController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $userId = Cache::pull($this->cacheKey($token));
        abort_unless($userId, 404);

        $user = User::query()->find($userId);
        abort_unless($user, 404);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        if (($user->must_change_password ?? false) && method_exists($user, 'hasRole') && $user->hasRole('mentor')) {
            return redirect()->to(route('profile.edit', absolute: false))
                ->with('status', 'must-change-password');
        }

        return redirect()->to(route('dashboard', absolute: false));
    }

    private function cacheKey(string $token): string
    {
        return 'frontend_login_handoff:'.$token;
    }
}
