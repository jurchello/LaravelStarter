<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Dto\SocialAccountData;
use App\Domain\User\Enums\SocialProvider;

interface SocialAccountRepository
{
    public function findUserIdByProviderIdentity(SocialProvider $provider, string $providerUserId): ?int;

    public function upsertForUser(int $userId, SocialAccountData $account): void;
}
