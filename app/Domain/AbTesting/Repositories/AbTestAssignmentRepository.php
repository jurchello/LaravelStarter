<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Repositories;

use App\Domain\AbTesting\Dto\AbTestAssignmentDto;
use App\Domain\AbTesting\Entities\AbTestAssignment;

interface AbTestAssignmentRepository
{
    public function findByTestAndVisitor(int $abTestId, string $visitorId): ?AbTestAssignment;

    public function findByTestAndUser(int $abTestId, int $userId): ?AbTestAssignment;

    public function create(AbTestAssignmentDto $dto): AbTestAssignment;

    public function attachUser(string $visitorId, int $userId): void;
}
