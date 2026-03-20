<?php

declare(strict_types=1);

namespace App\Domain\Health\Services;

interface DatabaseHealthChecker
{
    public function check(): bool;
}
