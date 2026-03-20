<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Domain\User\Repositories\UserRepository;

final readonly class GetAbTestAudienceEstimateAction
{
    public function __construct(
        private UserRepository $users,
    ) {}

    /**
     * @return array{audienceSize: int, trafficPercent: int, estimatedPeople: int}
     */
    public function execute(int $trafficPercent): array
    {
        $audienceSize = $this->users->countAudience();

        return [
            'audienceSize' => $audienceSize,
            'trafficPercent' => $trafficPercent,
            'estimatedPeople' => (int) floor(($audienceSize * $trafficPercent) / 100),
        ];
    }
}
