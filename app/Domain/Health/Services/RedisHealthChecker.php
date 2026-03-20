<?php

declare(strict_types=1);

namespace App\Domain\Health\Services;

interface RedisHealthChecker
{
    public function check(): bool;
}
