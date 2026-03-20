<?php

declare(strict_types=1);

namespace App\Application\User\Exceptions;

use App\Application\Shared\Exceptions\ApplicationValidationException;

final class SocialAuthenticationFailed extends ApplicationValidationException
{
    public function __construct(string $message = 'Google sign-in failed.')
    {
        parent::__construct($message);
    }
}
