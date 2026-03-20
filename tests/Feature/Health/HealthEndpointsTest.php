<?php

declare(strict_types=1);

namespace Tests\Feature\Health;

use App\Domain\Health\Services\DatabaseHealthChecker;
use App\Domain\Health\Services\QueueHealthChecker;
use App\Domain\Health\Services\RedisHealthChecker;
use Tests\TestCase;

final class HealthEndpointsTest extends TestCase
{
    public function test_live_endpoint_returns_ok(): void
    {
        $this->getJson('/health/live')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_ready_endpoint_returns_ok_when_all_checks_pass(): void
    {
        $this->mock(DatabaseHealthChecker::class)->shouldReceive('check')->once()->andReturn(true);
        $this->mock(RedisHealthChecker::class)->shouldReceive('check')->once()->andReturn(true);
        $this->mock(QueueHealthChecker::class)->shouldReceive('check')->once()->andReturn(true);

        $this->getJson('/health/ready')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'checks' => [
                    'db' => true,
                    'redis' => true,
                    'queue' => true,
                ],
            ]);
    }

    public function test_ready_endpoint_returns_service_unavailable_when_a_check_fails(): void
    {
        $this->mock(DatabaseHealthChecker::class)->shouldReceive('check')->once()->andReturn(true);
        $this->mock(RedisHealthChecker::class)->shouldReceive('check')->once()->andReturn(false);
        $this->mock(QueueHealthChecker::class)->shouldReceive('check')->once()->andReturn(true);

        $this->getJson('/health/ready')
            ->assertStatus(503)
            ->assertExactJson([
                'status' => 'error',
                'checks' => [
                    'db' => true,
                    'redis' => false,
                    'queue' => true,
                ],
            ]);
    }
}
