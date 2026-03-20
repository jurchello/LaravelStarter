<?php

declare(strict_types=1);

namespace App\Infrastructure\Health;

use App\Domain\Health\Services\RedisHealthChecker;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class LaravelRedisHealthChecker implements RedisHealthChecker
{
    public function check(): bool
    {
        try {
            $response = Redis::connection()->ping();

            return $response === true || $response === '+PONG' || $response === 'PONG';
        } catch (Throwable) {
            return false;
        }
    }
}
