<?php

declare(strict_types=1);

namespace App\Application\AbTesting\Exceptions;

use App\Application\Shared\Exceptions\ApplicationValidationException;

final class AbTestConfigurationInvalid extends ApplicationValidationException
{
    /**
     * @param array<int, string> $violations
     */
    public static function fromViolations(array $violations): self
    {
        return new self(implode(' ', $violations));
    }
}
