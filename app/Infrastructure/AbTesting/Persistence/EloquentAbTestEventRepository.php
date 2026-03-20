<?php

declare(strict_types=1);

namespace App\Infrastructure\AbTesting\Persistence;

use App\Domain\AbTesting\Dto\AbTestEventDto;
use App\Domain\AbTesting\Repositories\AbTestEventRepository;
use App\Models\AbTestEvent;

final class EloquentAbTestEventRepository implements AbTestEventRepository
{
    public function record(AbTestEventDto $dto): void
    {
        AbTestEvent::create([
            'ab_test_assignment_id' => $dto->abTestAssignmentId,
            'event' => $dto->event,
        ]);
    }
}
