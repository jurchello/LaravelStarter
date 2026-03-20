<?php

declare(strict_types=1);

namespace App\Domain\Health\Services;

interface QueueHealthChecker
{
    public function check(): bool;
}
