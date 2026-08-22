<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Modules\Mentors\Models\Mentor;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountType
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $account = $request->user();

        $allowed = collect(explode('|', $type))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();

        $matches = match (true) {
            in_array('student', $allowed, true) && $account instanceof Student => true,
            in_array('mentor', $allowed, true) && $account instanceof Mentor => true,
            in_array('admin', $allowed, true) && $account instanceof User && $account->hasRole('admin') => true,
            default => false,
        };

        abort_unless($matches, 403);

        return $next($request);
    }
}
