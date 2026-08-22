<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Support\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Mentors\Models\Mentor;

class FrontendLoginHandoffController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $payload = Cache::pull($this->cacheKey($token));
        abort_unless($payload, 404);

        $guard = is_array($payload) ? (string) ($payload['guard'] ?? 'web') : 'web';
        $userId = is_array($payload) ? ($payload['id'] ?? null) : $payload;

        $model = match ($guard) {
            'student' => Student::class,
            'mentor' => Mentor::class,
            default => User::class,
        };

        $user = $model::query()->find($userId);
        abort_unless($user, 404);

        Auth::guard($guard)->login($user);
        Auth::shouldUse($guard);
        $request->session()->regenerate();

        if (($user->must_change_password ?? false) && Accounts::typeFor($user) === 'mentor') {
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
