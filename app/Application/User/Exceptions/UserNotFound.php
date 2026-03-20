<?php

declare(strict_types=1);

namespace App\Application\User\Exceptions;

use App\Application\Shared\Exceptions\ApplicationNotFoundException;

final class UserNotFound extends ApplicationNotFoundException
{
    public static function forId(int $id): self
    {
        return new self("User {$id} was not found.");
    }
}
