<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Health;

use App\Domain\Health\Services\DatabaseHealthChecker;
use App\Domain\Health\Services\HealthCheckService;
use App\Domain\Health\Services\QueueHealthChecker;
use App\Domain\Health\Services\RedisHealthChecker;
use PHPUnit\Framework\TestCase;

final class HealthCheckServiceTest extends TestCase
{
    public function test_returns_check_results_for_all_dependencies(): void
    {
        $service = new HealthCheckService(
            new class implements DatabaseHealthChecker
            {
                public function check(): bool
                {
                    return true;
                }
            },
            new class implements RedisHealthChecker
            {
                public function check(): bool
                {
                    return false;
                }
            },
            new class implements QueueHealthChecker
            {
                public function check(): bool
                {
                    return true;
                }
            },
        );

        self::assertSame([
            'db' => true,
            'redis' => false,
            'queue' => true,
        ], $service->check());
    }
}
