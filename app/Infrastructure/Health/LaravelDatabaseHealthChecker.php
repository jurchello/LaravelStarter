<?php

declare(strict_types=1);

namespace App\Infrastructure\Health;

use App\Domain\Health\Services\DatabaseHealthChecker;
use Illuminate\Support\Facades\DB;
use Throwable;

final class LaravelDatabaseHealthChecker implements DatabaseHealthChecker
{
    public function check(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
