<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

final readonly class AbTestEventListItem
{
    public function __construct(
        public int $id,
        public string $event,
        public string $variantName,
        public string $variantSlug,
        public string $visitorId,
        public string $createdAt,
    ) {}
}
