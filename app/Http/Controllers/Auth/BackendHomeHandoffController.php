<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BackendHomeHandoffController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate(['state' => ['required', 'regex:/^[a-f0-9]{64}$/'], 'locale' => 'required|in:en,bn']);
        $code = Str::random(64);
        Cache::put('backend_home:'.hash('sha256', $code), [
            'id' => $request->user()->getAuthIdentifier(),
            'guard' => Accounts::guardFor($request->user()),
            'state_hash' => hash('sha256', $data['state']),
        ], now()->addMinute());

        return redirect()->away(rtrim(config('app.frontend_url'), '/').'/auth/backend?'.http_build_query([
            'code' => $code, 'state' => $data['state'], 'locale' => $data['locale'],
        ]))->withHeaders(['Cache-Control' => 'no-store', 'Referrer-Policy' => 'no-referrer']);
    }

    public function exchange(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'regex:/^[A-Za-z0-9]{64}$/'], 'state' => ['required', 'regex:/^[a-f0-9]{64}$/']]);
        $key = 'backend_home:'.hash('sha256', $data['code']);
        $payload = Cache::lock($key.':lock', 5)->get(fn () => Cache::pull($key));
        abort_unless(is_array($payload), 401);
        abort_unless(hash_equals($payload['state_hash'], hash('sha256', $data['state'])), 401);
        $model = match ($payload['guard']) {
            'student' => \App\Models\Student::class,
            'mentor' => \Modules\Mentors\Models\Mentor::class,
            default => \App\Models\User::class,
        };
        $user = $model::find($payload['id']);
        abort_unless($user, 401);
        $ability = match ($payload['guard']) {
            'student' => 'student-panel',
            'mentor' => 'mentor-panel',
            default => 'admin-panel',
        };
        // Homepage identity tokens do not need a long-lived admin session.
        $token = $user->createToken('frontend-home', [$ability], now()->addHours(8));

        return response()->json(['access_token' => $token->plainTextToken])->header('Cache-Control', 'no-store');
    }
}
