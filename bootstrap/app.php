<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureImpersonating;
use App\Http\Middleware\SetRequestId;
use App\Http\Middleware\SetVisitorId;
use App\Http\Support\Api\ApiExceptionResponder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/site_web.php',
        api: __DIR__.'/../routes/site_api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/admin_panel_web.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin_panel_api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SetRequestId::class);
        $middleware->web(SetVisitorId::class);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');
        $middleware->trustHosts();
        $middleware->alias([
            'ensure_admin' => EnsureAdmin::class,
            'ensure_impersonating' => EnsureImpersonating::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            return app(ApiExceptionResponder::class)->toResponse($exception, $request);
        });
    })->create();
