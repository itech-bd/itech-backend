<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\ForceEnglishLocale;
use App\Http\Middleware\SetApiLocale;
use App\Http\Middleware\SetFrontendLocale;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (Middleware $middleware): void {
            $middleware->statefulApi();

            // Production is commonly behind Nginx/Apache/Cloudflare where the app
            // receives requests as HTTP from a reverse proxy. Trusting forwarded
            // headers prevents HTTPS / host detection issues (redirect loops,
            // wrong absolute URLs in emails, lost secure cookies).
            $middleware->trustProxies(at: '*');

            $middleware->alias(
                [
                    'frontend.locale' => SetFrontendLocale::class,
                    'api.locale' => SetApiLocale::class,
                    'backend.locale' => ForceEnglishLocale::class,
                    'role' => RoleMiddleware::class,
                    'permission' => PermissionMiddleware::class,
                    'role_or_permission' => RoleOrPermissionMiddleware::class,
                ]
            );
        }
    )
    ->withExceptions(
        function (Exceptions $exceptions): void {
            $exceptions->shouldRenderJsonWhen(
                fn ($request, $exception): bool => $request->is('api/*') || $request->expectsJson()
            );

            $exceptions->render(function (\Illuminate\Validation\ValidationException $exception, $request) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'The provided data is invalid.',
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $exception->errors(),
                ], 422);
            });

            $exceptions->render(function (\Illuminate\Auth\AuthenticationException $exception, $request) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'code' => 'UNAUTHENTICATED',
                    'errors' => null,
                ], 401);
            });

            $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception, $request) {
                if (! $request->is('api/*')) {
                    return null;
                }

                $status = $exception->getStatusCode();
                $message = $exception->getMessage();

                if ($message === '') {
                    $message = \Symfony\Component\HttpFoundation\Response::$statusTexts[$status] ?? 'Request failed.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'code' => 'HTTP_'.$status,
                    'errors' => null,
                ], $status);
            });


            $exceptions->render(function (\Throwable $exception, $request) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return response()->json([
                    'success' => false,
                    'message' => config('app.debug')
                        ? $exception->getMessage()
                        : 'An unexpected server error occurred.',
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'errors' => null,
                ], 500);
            });
        }
    )
    ->create();
