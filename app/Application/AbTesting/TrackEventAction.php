<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Domain\AbTesting\Dto\AbTestEventDto;
use App\Domain\AbTesting\Repositories\AbTestAssignmentRepository;
use App\Domain\AbTesting\Repositories\AbTestEventRepository;
use App\Domain\AbTesting\Repositories\AbTestRepository;

final readonly class TrackEventAction
{
    public function __construct(
        private AbTestRepository $tests,
        private AbTestAssignmentRepository $assignments,
        private AbTestEventRepository $events,
    ) {}

    public function execute(string $testSlug, string $visitorId, string $event, ?int $userId): void
    {
        $test = $this->tests->findActiveBySlug($testSlug);

        if ($test === null) {
            return;
        }

        $assignment = $this->assignments->findByTestAndVisitor($test->id, $visitorId);

        if ($assignment === null && $userId !== null) {
            $assignment = $this->assignments->findByTestAndUser($test->id, $userId);
        }

        if ($assignment === null) {
            return;
        }

        $this->events->record(new AbTestEventDto(
            abTestAssignmentId: $assignment->id,
            event: $event,
        ));
    }
}
