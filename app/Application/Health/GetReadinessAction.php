<?php

declare(strict_types=1);

namespace App\Application\Health;

use App\Domain\Health\Services\HealthCheckService;

final readonly class GetReadinessAction
{
    public function __construct(
        private HealthCheckService $health,
    ) {}

    /**
     * @return array{status: 'ok'|'error', checks: array{db: bool, redis: bool, queue: bool}}
     */
    public function execute(): array
    {
        $checks = $this->health->check();

        return [
            'status' => $this->allPassed($checks) ? 'ok' : 'error',
            'checks' => $checks,
        ];
    }

    /**
     * @param  array{db: bool, redis: bool, queue: bool}  $checks
     */
    private function allPassed(array $checks): bool
    {
        return ! in_array(false, $checks, true);
    }
}
