<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set the locale for the public frontend based on session preference.
 *
 * @category Middleware
 * @package  App\Http\Middleware
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com/docs/localization
 */
class SetFrontendLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->session()->get(
            'locale',
            (string) config('app.locale', 'en')
        );

        if (! in_array($locale, ['bn', 'en'], true)) {
            $locale = (string) config('app.locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
