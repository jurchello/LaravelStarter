<?php

declare(strict_types=1);

namespace App\Application\AbTesting\Exceptions;

use App\Application\Shared\Exceptions\ApplicationNotFoundException;

final class AbTestNotFound extends ApplicationNotFoundException
{
    public static function forId(int $id): self
    {
        return new self("AB test {$id} was not found.");
    }
}
