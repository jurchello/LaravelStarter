<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared;

use App\Domain\Shared\Randomizer;

final class PhpRandomizer implements Randomizer
{
    public function int(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}