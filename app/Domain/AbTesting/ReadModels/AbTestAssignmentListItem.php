<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

final readonly class AbTestAssignmentListItem
{
    public function __construct(
        public int $id,
        public string $visitorId,
        public ?int $userId,
        public string $variantName,
        public string $variantSlug,
        public string $createdAt,
    ) {}
}
