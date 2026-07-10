<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = strtolower((string) ($request->header('X-Locale') ?: $request->query('locale', 'en')));
        $locale = str_starts_with($locale, 'bn') ? 'bn' : 'en';

        app()->setLocale($locale);

        return $next($request);
    }
}
