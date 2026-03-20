<?php

declare(strict_types=1);

namespace App\Domain\User\Dto;

use App\Domain\User\Enums\SocialProvider;

final readonly class SocialAccountData
{
    public function __construct(
        public SocialProvider $provider,
        public string $providerUserId,
        public ?string $email,
        public ?string $name,
        public ?string $avatar,
    ) {}
}
