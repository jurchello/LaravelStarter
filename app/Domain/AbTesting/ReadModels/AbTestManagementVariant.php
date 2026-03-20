<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\ReadModels;

final readonly class AbTestManagementVariant
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public int $weight,
        public int $assignmentsCount,
    ) {}
}
