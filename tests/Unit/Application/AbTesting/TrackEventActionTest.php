<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AbTesting;

use App\Application\AbTesting\TrackEventAction;
use App\Domain\AbTesting\Dto\AbTestEventDto;
use App\Domain\AbTesting\Entities\AbTest;
use App\Domain\AbTesting\Entities\AbTestAssignment;
use App\Domain\AbTesting\Entities\AbTestVariant;
use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Repositories\AbTestAssignmentRepository;
use App\Domain\AbTesting\Repositories\AbTestEventRepository;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class TrackEventActionTest extends TestCase
{
    private AbTestRepository&MockInterface $tests;

    private AbTestAssignmentRepository&MockInterface $assignments;

    private AbTestEventRepository&MockInterface $events;

    private TrackEventAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tests = Mockery::mock(AbTestRepository::class);
        $this->assignments = Mockery::mock(AbTestAssignmentRepository::class);
        $this->events = Mockery::mock(AbTestEventRepository::class);

        $this->action = new TrackEventAction(
            $this->tests,
            $this->assignments,
            $this->events,
        );
    }

    public function test_does_nothing_when_test_not_found(): void
    {
        $this->tests->shouldReceive('findActiveBySlug')
            ->with('missing-test')
            ->andReturn(null);

        $this->assignments->shouldNotReceive('findByTestAndVisitor');
        $this->assignments->shouldNotReceive('findByTestAndUser');
        $this->events->shouldNotReceive('record');

        $this->action->execute('missing-test', 'visitor-uuid', 'signup', null);
    }

    public function test_does_nothing_when_assignment_not_found(): void
    {
        $test = new AbTest(
            id: 1,
            slug: 'my-test',
            trafficPercent: 100,
            distributionMode: AbTestDistributionMode::Manual,
            variants: [],
        );

        $this->tests->shouldReceive('findActiveBySlug')
            ->with('my-test')
            ->andReturn($test);

        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->with(1, 'visitor-uuid')
            ->andReturn(null);
        $this->assignments->shouldNotReceive('findByTestAndUser');

        $this->events->shouldNotReceive('record');

        $this->action->execute('my-test', 'visitor-uuid', 'signup', null);
    }

    public function test_records_event_with_correct_dto(): void
    {
        $test = new AbTest(
            id: 1,
            slug: 'my-test',
            trafficPercent: 100,
            distributionMode: AbTestDistributionMode::Manual,
            variants: [],
        );
        $assignment = new AbTestAssignment(
            id: 42,
            abTestId: 1,
            abTestVariantId: 10,
            visitorId: 'visitor-uuid',
            userId: null,
            variant: new AbTestVariant(id: 10, slug: 'control', weight: 100),
        );

        $this->tests->shouldReceive('findActiveBySlug')
            ->with('my-test')
            ->andReturn($test);

        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->with(1, 'visitor-uuid')
            ->andReturn($assignment);
        $this->assignments->shouldNotReceive('findByTestAndUser');

        $this->events->shouldReceive('record')
            ->once()
            ->with(Mockery::on(function (AbTestEventDto $dto): bool {
                return $dto->abTestAssignmentId === 42
                    && $dto->event === 'signup';
            }));

        $this->action->execute('my-test', 'visitor-uuid', 'signup', null);
    }

    public function test_falls_back_to_user_assignment_when_visitor_assignment_is_missing(): void
    {
        $test = new AbTest(
            id: 1,
            slug: 'my-test',
            trafficPercent: 100,
            distributionMode: AbTestDistributionMode::Manual,
            variants: [],
        );
        $assignment = new AbTestAssignment(
            id: 42,
            abTestId: 1,
            abTestVariantId: 10,
            visitorId: 'old-visitor-uuid',
            userId: 5,
            variant: new AbTestVariant(id: 10, slug: 'control', weight: 100),
        );

        $this->tests->shouldReceive('findActiveBySlug')
            ->with('my-test')
            ->andReturn($test);

        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->once()
            ->with(1, 'new-visitor-uuid')
            ->andReturn(null);

        $this->assignments->shouldReceive('findByTestAndUser')
            ->once()
            ->with(1, 5)
            ->andReturn($assignment);

        $this->events->shouldReceive('record')
            ->once()
            ->with(Mockery::on(fn (AbTestEventDto $dto): bool => $dto->abTestAssignmentId === 42 && $dto->event === 'signup'));

        $this->action->execute('my-test', 'new-visitor-uuid', 'signup', 5);
    }
}
