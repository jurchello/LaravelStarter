<?php

declare(strict_types=1);

namespace App\Infrastructure\Health;

use App\Domain\Health\Services\QueueHealthChecker;
use Illuminate\Support\Facades\Queue;
use Throwable;

final class LaravelQueueHealthChecker implements QueueHealthChecker
{
    public function check(): bool
    {
        try {
            Queue::size();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
