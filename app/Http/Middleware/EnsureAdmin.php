<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $user->hasVerifiedEmail()) {
            abort(404);
        }

        if ($user->is_admin) {
            return $next($request);
        }

        if (! is_string($routeName) || $routeName === '' || ! $user->can($routeName)) {
            abort(404);
        }

        return $next($request);
    }
}
