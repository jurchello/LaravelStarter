<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\Exceptions\SocialAuthenticationFailed;
use App\Domain\User\Dto\SocialAccountData;
use App\Domain\User\Repositories\SocialAccountRepository;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final readonly class AuthenticateWithSocialAccountAction
{
    public function __construct(
        private SocialAccountRepository $socialAccounts,
    ) {}

    public function execute(SocialAccountData $account): User
    {
        $user = $this->findExistingSocialUser($account)
            ?? $this->findOrCreateEmailUser($account);

        $this->socialAccounts->upsertForUser((int) $user->id, $account);

        return $user;
    }

    private function findExistingSocialUser(SocialAccountData $account): ?User
    {
        $userId = $this->socialAccounts->findUserIdByProviderIdentity($account->provider, $account->providerUserId);

        if ($userId === null) {
            return null;
        }

        return User::query()->findOrFail($userId);
    }

    private function findOrCreateEmailUser(SocialAccountData $account): User
    {
        if ($account->email === null || $account->email === '') {
            throw new SocialAuthenticationFailed();
        }

        $user = User::query()->firstWhere('email', $account->email);

        if ($user instanceof User) {
            if (! $user->hasVerifiedEmail()) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            return $user;
        }

        $user = User::query()->create([
            'name' => $this->resolveDisplayName($account),
            'email' => $account->email,
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
        ]);

        Event::dispatch(new Registered($user));

        return $user;
    }

    private function resolveDisplayName(SocialAccountData $account): string
    {
        if ($account->name !== null && $account->name !== '') {
            return $account->name;
        }

        if ($account->email !== null && $account->email !== '') {
            return Str::before($account->email, '@');
        }

        return 'Google user';
    }
}
