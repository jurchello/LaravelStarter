<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\Exceptions\CannotLeaveImpersonation;
use App\Domain\User\ValueObjects\ImpersonationSession;
use App\Models\User;

final readonly class StopUserImpersonationAction
{
    public function execute(?int $impersonatorId): User
    {
        if (! $impersonatorId) {
            throw new CannotLeaveImpersonation;
        }

        $impersonator = User::query()->find($impersonatorId);

        if (! $impersonator || ! $impersonator->is_admin || ! $impersonator->hasVerifiedEmail()) {
            throw new CannotLeaveImpersonation;
        }

        return $impersonator;
    }

    public function clearImpersonationSessionKeys(): array
    {
        return ImpersonationSession::keys();
    }
}
