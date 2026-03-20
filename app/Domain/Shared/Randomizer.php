<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface Randomizer
{
    public function int(int $min, int $max): int;
}
