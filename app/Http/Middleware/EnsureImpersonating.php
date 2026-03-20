<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\User\ValueObjects\ImpersonationSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureImpersonating
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->session()->has(ImpersonationSession::IMPERSONATOR_ID)) {
            abort(404);
        }

        return $next($request);
    }
}
