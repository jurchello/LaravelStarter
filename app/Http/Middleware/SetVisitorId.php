<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class SetVisitorId
{
    public const COOKIE_NAME = 'visitor_id';
    public const COOKIE_TTL_DAYS = 365;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasCookie(self::COOKIE_NAME)) {
            $request->cookies->set(self::COOKIE_NAME, Str::uuid()->toString());
        }

        $response = $next($request);

        if (! $request->cookie(self::COOKIE_NAME)) {
            return $response;
        }

        return $response->withCookie(
            cookie(
                self::COOKIE_NAME,
                $request->cookie(self::COOKIE_NAME),
                self::COOKIE_TTL_DAYS * 24 * 60,
                httpOnly: true,
                secure: true,
                sameSite: 'lax',
            )
        );
    }
}