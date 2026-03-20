<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Domain\AbTesting\Repositories\AbTestAssignmentRepository;

final readonly class AttachVisitorAssignmentsToUserAction
{
    public function __construct(
        private AbTestAssignmentRepository $assignments,
    ) {}

    public function execute(?string $visitorId, int $userId): void
    {
        if ($visitorId === null || $visitorId === '') {
            return;
        }

        $this->assignments->attachUser($visitorId, $userId);
    }
}
