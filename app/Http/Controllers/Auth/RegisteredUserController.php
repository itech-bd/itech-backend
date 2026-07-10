<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        $intended = (string) session()->get('url.intended', '');
        $intendedPath = (string) parse_url($intended, PHP_URL_PATH);

        if ($intendedPath !== '' && (Str::startsWith($intendedPath, ['/admin', '/dashboard']))) {
            return view('auth.register');
        }

        $previous = (string) url()->previous();
        $previousPath = (string) parse_url($previous, PHP_URL_PATH);
        $isUnsafePrevious = $previousPath === ''
            || Str::startsWith($previousPath, ['/login', '/register', '/forgot-password', '/admin', '/dashboard']);

        if (! $isUnsafePrevious && preg_match('#^/email/verify(?:/|$)#', $previousPath)) {
            $isUnsafePrevious = true;
        }

        $target = $isUnsafePrevious ? '' : $previous;
        if ($target === '') {
            $target = route('home', absolute: false);
        }

        return redirect()->to($target)->with('auth_modal', 'register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        $shouldRequireRecaptcha = config('recaptcha.enabled')
            && ! (config('recaptcha.skip_in_testing') && app()->environment('testing'));

        if ($shouldRequireRecaptcha) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha('register')];
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Default role assignment for new registrations
        $studentRole = Role::firstOrCreate(
            ['name' => 'student', 'guard_name' => 'web'],
            ['name' => 'student', 'guard_name' => 'web'],
        );
        $user->assignRole($studentRole);

        event(new Registered($user));

        session()->put('verification_email', (string) $user->email);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'verification_required' => true,
                'email' => (string) $user->email,
                'message' => __('frontend.verification_link_sent'),
            ]);
        }

        return redirect()->to(route('verification.notice', absolute: false))
            ->with('status', 'verification-link-sent');
    }
}
