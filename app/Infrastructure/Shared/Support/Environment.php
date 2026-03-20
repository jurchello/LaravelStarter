<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Support;

use App\Infrastructure\Shared\Enums\AppEnvironment;

final class Environment
{
    public static function current(): string
    {
        return app()->environment();
    }

    public static function is(AppEnvironment ...$environments): bool
    {
        return app()->environment(array_map(
            static fn (AppEnvironment $environment): string => $environment->value,
            $environments,
        ));
    }

    public static function isLocal(): bool
    {
        return self::is(AppEnvironment::Local);
    }

    public static function isTesting(): bool
    {
        return self::is(AppEnvironment::Testing);
    }

    public static function isStaging(): bool
    {
        return self::is(AppEnvironment::Staging);
    }

    public static function isProduction(): bool
    {
        return self::is(AppEnvironment::Production);
    }

    public static function isLocalOrTesting(): bool
    {
        return self::is(AppEnvironment::Local, AppEnvironment::Testing);
    }
}
