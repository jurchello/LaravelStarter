<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Shared;

use App\Infrastructure\Shared\Enums\AppEnvironment;
use App\Infrastructure\Shared\Support\Environment;
use Tests\TestCase;

final class EnvironmentTest extends TestCase
{
    public function test_reports_local_environment_helpers(): void
    {
        $this->app->detectEnvironment(fn (): string => AppEnvironment::Local->value);

        self::assertSame(AppEnvironment::Local->value, Environment::current());
        self::assertTrue(Environment::isLocal());
        self::assertTrue(Environment::isLocalOrTesting());
        self::assertFalse(Environment::isTesting());
        self::assertFalse(Environment::isProduction());
    }

    public function test_reports_testing_environment_helpers(): void
    {
        $this->app->detectEnvironment(fn (): string => AppEnvironment::Testing->value);

        self::assertTrue(Environment::isTesting());
        self::assertTrue(Environment::isLocalOrTesting());
        self::assertFalse(Environment::isLocal());
        self::assertFalse(Environment::isStaging());
    }

    public function test_reports_production_environment_helpers(): void
    {
        $this->app->detectEnvironment(fn (): string => AppEnvironment::Production->value);

        self::assertTrue(Environment::isProduction());
        self::assertFalse(Environment::isLocalOrTesting());
        self::assertFalse(Environment::isStaging());
    }
}
