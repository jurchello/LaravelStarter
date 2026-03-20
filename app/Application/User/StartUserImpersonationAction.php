<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\Exceptions\CannotImpersonateUser;
use App\Domain\User\ValueObjects\ImpersonationSession;
use App\Models\User;

final readonly class StartUserImpersonationAction
{
    public function execute(?User $admin, User $target): ImpersonationSession
    {
        if (! $admin || ! $admin->is_admin || ! $admin->hasVerifiedEmail() || $admin->is($target)) {
            throw new CannotImpersonateUser();
        }

        return new ImpersonationSession(
            impersonatorId: $admin->id,
            impersonatorName: $admin->name,
        );
    }
}
