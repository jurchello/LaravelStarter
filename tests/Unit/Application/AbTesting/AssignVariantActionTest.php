<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AbTesting;

use App\Application\AbTesting\AssignVariantAction;
use App\Domain\AbTesting\Dto\AbTestAssignmentDto;
use App\Domain\AbTesting\Entities\AbTest;
use App\Domain\AbTesting\Entities\AbTestAssignment;
use App\Domain\AbTesting\Entities\AbTestVariant;
use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Repositories\AbTestAssignmentRepository;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\Shared\Randomizer;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class AssignVariantActionTest extends TestCase
{
    private AbTestRepository&MockInterface $tests;
    private AbTestAssignmentRepository&MockInterface $assignments;
    private Randomizer&MockInterface $randomizer;
    private AssignVariantAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tests = Mockery::mock(AbTestRepository::class);
        $this->assignments = Mockery::mock(AbTestAssignmentRepository::class);
        $this->randomizer = Mockery::mock(Randomizer::class);

        $this->action = new AssignVariantAction(
            $this->tests,
            $this->assignments,
            $this->randomizer,
        );
    }

    public function test_returns_null_when_test_not_found(): void
    {
        $this->tests->shouldReceive('findActiveBySlug')
            ->with('some-test')
            ->andReturn(null);

        $result = $this->action->execute('some-test', 'visitor-uuid', null);

        $this->assertNull($result);
    }

    public function test_returns_existing_variant_slug(): void
    {
        $variant = new AbTestVariant(id: 10, slug: 'control', weight: 100);
        $assignment = new AbTestAssignment(
            id: 42,
            abTestId: 1,
            abTestVariantId: 10,
            visitorId: 'visitor-uuid',
            userId: null,
            variant: $variant,
        );
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

        $this->assignments->shouldNotReceive('findByTestAndUser');
        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->with(1, 'visitor-uuid')
            ->andReturn($assignment);

        $result = $this->action->execute('my-test', 'visitor-uuid', null);

        $this->assertSame('control', $result);
    }

    public function test_returns_null_when_visitor_not_enrolled(): void
    {
        $test = new AbTest(
            id: 1,
            slug: 'my-test',
            trafficPercent: 50,
            distributionMode: AbTestDistributionMode::Manual,
            variants: [],
        );

        $this->tests->shouldReceive('findActiveBySlug')
            ->andReturn($test);

        $this->assignments->shouldNotReceive('findByTestAndUser');
        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->andReturn(null);

        $this->randomizer->shouldReceive('int')
            ->with(1, 100)
            ->andReturn(51);

        $this->assignments->shouldNotReceive('create');

        $result = $this->action->execute('my-test', 'visitor-uuid', null);

        $this->assertNull($result);
    }

    public function test_creates_assignment_and_returns_variant_slug(): void
    {
        $variant = new AbTestVariant(id: 10, slug: 'treatment', weight: 100);
        $test = new AbTest(
            id: 1,
            slug: 'my-test',
            trafficPercent: 100,
            distributionMode: AbTestDistributionMode::Manual,
            variants: [$variant],
        );

        $this->tests->shouldReceive('findActiveBySlug')
            ->with('my-test')
            ->andReturn($test);

        $this->assignments->shouldNotReceive('findByTestAndUser');
        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->with(1, 'visitor-uuid')
            ->andReturn(null);

        $this->randomizer->shouldReceive('int')
            ->with(1, 100)
            ->twice()
            ->andReturn(1, 50);

        $this->assignments->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (AbTestAssignmentDto $dto): bool {
                return $dto->abTestId === 1
                    && $dto->abTestVariantId === 10
                    && $dto->visitorId === 'visitor-uuid'
                    && $dto->userId === null;
            }))
            ->andReturn(new AbTestAssignment(
                id: 100,
                abTestId: 1,
                abTestVariantId: 10,
                visitorId: 'visitor-uuid',
                userId: null,
                variant: $variant,
            ));

        $result = $this->action->execute('my-test', 'visitor-uuid', null);

        $this->assertSame('treatment', $result);
    }

    public function test_returns_null_when_variants_empty(): void
    {
        $test = new AbTest(
            id: 1,
            slug: 'my-test',
            trafficPercent: 100,
            distributionMode: AbTestDistributionMode::Manual,
            variants: [],
        );

        $this->tests->shouldReceive('findActiveBySlug')
            ->andReturn($test);

        $this->assignments->shouldNotReceive('findByTestAndUser');
        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->andReturn(null);

        $this->randomizer->shouldReceive('int')
            ->with(1, 100)
            ->andReturn(1);

        $this->assignments->shouldNotReceive('create');

        $result = $this->action->execute('my-test', 'visitor-uuid', null);

        $this->assertNull($result);
    }

    public function test_authenticated_user_reuses_existing_assignment_by_user_id(): void
    {
        $variant = new AbTestVariant(id: 10, slug: 'control', weight: 100);
        $assignment = new AbTestAssignment(
            id: 42,
            abTestId: 1,
            abTestVariantId: 10,
            visitorId: 'old-visitor-uuid',
            userId: 5,
            variant: $variant,
        );
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

        $this->assignments->shouldReceive('findByTestAndUser')
            ->once()
            ->with(1, 5)
            ->andReturn($assignment);

        $this->assignments->shouldNotReceive('findByTestAndVisitor');
        $this->assignments->shouldNotReceive('create');

        $result = $this->action->execute('my-test', 'new-visitor-uuid', 5);

        $this->assertSame('control', $result);
    }

    public function test_equal_split_creates_assignment_using_uniform_variant_pick(): void
    {
        $variantA = new AbTestVariant(id: 10, slug: 'control', weight: 33);
        $variantB = new AbTestVariant(id: 11, slug: 'treatment-a', weight: 33);
        $variantC = new AbTestVariant(id: 12, slug: 'treatment-b', weight: 33);
        $test = new AbTest(
            id: 1,
            slug: 'my-test',
            trafficPercent: 100,
            distributionMode: AbTestDistributionMode::Equal,
            variants: [$variantA, $variantB, $variantC],
        );

        $this->tests->shouldReceive('findActiveBySlug')
            ->with('my-test')
            ->andReturn($test);

        $this->assignments->shouldNotReceive('findByTestAndUser');
        $this->assignments->shouldReceive('findByTestAndVisitor')
            ->with(1, 'visitor-uuid')
            ->andReturn(null);

        $this->randomizer->shouldReceive('int')
            ->with(1, 100)
            ->once()
            ->andReturn(1);

        $this->randomizer->shouldReceive('int')
            ->with(0, 2)
            ->once()
            ->andReturn(2);

        $this->assignments->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (AbTestAssignmentDto $dto): bool {
                return $dto->abTestId === 1
                    && $dto->abTestVariantId === 12
                    && $dto->visitorId === 'visitor-uuid'
                    && $dto->userId === null;
            }))
            ->andReturn(new AbTestAssignment(
                id: 100,
                abTestId: 1,
                abTestVariantId: 12,
                visitorId: 'visitor-uuid',
                userId: null,
                variant: $variantC,
            ));

        $result = $this->action->execute('my-test', 'visitor-uuid', null);

        $this->assertSame('treatment-b', $result);
    }
}
