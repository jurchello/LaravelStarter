<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Persistence;

use App\Domain\User\Dto\SocialAccountData;
use App\Domain\User\Enums\SocialProvider;
use App\Domain\User\Repositories\SocialAccountRepository;
use App\Models\SocialAccount;

final class EloquentSocialAccountRepository implements SocialAccountRepository
{
    public function findUserIdByProviderIdentity(SocialProvider $provider, string $providerUserId): ?int
    {
        /** @var int|null $userId */
        $userId = SocialAccount::query()
            ->where('provider', $provider->value)
            ->where('provider_user_id', $providerUserId)
            ->value('user_id');

        return $userId;
    }

    public function upsertForUser(int $userId, SocialAccountData $account): void
    {
        SocialAccount::query()->updateOrCreate(
            [
                'provider' => $account->provider->value,
                'provider_user_id' => $account->providerUserId,
            ],
            [
                'user_id' => $userId,
                'provider_email' => $account->email,
                'provider_name' => $account->name,
                'provider_avatar' => $account->avatar,
            ],
        );
    }
}
