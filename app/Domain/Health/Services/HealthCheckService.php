<?php

declare(strict_types=1);

namespace App\Domain\Health\Services;

final readonly class HealthCheckService
{
    public function __construct(
        private DatabaseHealthChecker $database,
        private RedisHealthChecker $redis,
        private QueueHealthChecker $queue,
    ) {}

    /**
     * @return array{db: bool, redis: bool, queue: bool}
     */
    public function check(): array
    {
        return [
            'db' => $this->database->check(),
            'redis' => $this->redis->check(),
            'queue' => $this->queue->check(),
        ];
    }
}
