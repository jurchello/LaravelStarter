<?php

declare(strict_types=1);

namespace App\Application\AbTesting\Exceptions;

use App\Application\Shared\Exceptions\ApplicationNotFoundException;

final class AbTestVariantNotFound extends ApplicationNotFoundException
{
    public static function forId(int $id): self
    {
        return new self("AB test variant {$id} was not found.");
    }
}
