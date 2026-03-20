<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Health;

use App\Application\Health\GetReadinessAction;
use App\Domain\Health\Services\DatabaseHealthChecker;
use App\Domain\Health\Services\HealthCheckService;
use App\Domain\Health\Services\QueueHealthChecker;
use App\Domain\Health\Services\RedisHealthChecker;
use PHPUnit\Framework\TestCase;

final class GetReadinessActionTest extends TestCase
{
    public function test_returns_ok_payload_when_all_checks_pass(): void
    {
        $action = new GetReadinessAction($this->service(true, true, true));

        self::assertSame([
            'status' => 'ok',
            'checks' => [
                'db' => true,
                'redis' => true,
                'queue' => true,
            ],
        ], $action->execute());
    }

    public function test_returns_error_payload_when_any_check_fails(): void
    {
        $action = new GetReadinessAction($this->service(true, false, true));

        self::assertSame([
            'status' => 'error',
            'checks' => [
                'db' => true,
                'redis' => false,
                'queue' => true,
            ],
        ], $action->execute());
    }

    private function service(bool $db, bool $redis, bool $queue): HealthCheckService
    {
        return new HealthCheckService(
            new class ($db) implements DatabaseHealthChecker
            {
                public function __construct(
                    private readonly bool $result,
                ) {}

                public function check(): bool
                {
                    return $this->result;
                }
            },
            new class ($redis) implements RedisHealthChecker
            {
                public function __construct(
                    private readonly bool $result,
                ) {}

                public function check(): bool
                {
                    return $this->result;
                }
            },
            new class ($queue) implements QueueHealthChecker
            {
                public function __construct(
                    private readonly bool $result,
                ) {}

                public function check(): bool
                {
                    return $this->result;
                }
            },
        );
    }
}
