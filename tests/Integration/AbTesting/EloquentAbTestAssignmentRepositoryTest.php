<?php

declare(strict_types=1);

namespace Tests\Integration\AbTesting;

use App\Domain\AbTesting\Dto\AbTestAssignmentDto;
use App\Domain\AbTesting\Entities\AbTestAssignment;
use App\Infrastructure\AbTesting\Persistence\EloquentAbTestAssignmentRepository;
use App\Models\AbTestAssignment as AbTestAssignmentModel;
use App\Models\User;
use Database\Factories\AbTestFactory;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EloquentAbTestAssignmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentAbTestAssignmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentAbTestAssignmentRepository();
    }

    public function test_finds_assignment_by_test_and_visitor(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);

        $visitorId = Str::uuid()->toString();

        AbTestAssignmentModel::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => $visitorId,
        ]);

        $result = $this->repository->findByTestAndVisitor($test->id, $visitorId);

        $this->assertNotNull($result);
        $this->assertInstanceOf(AbTestAssignment::class, $result);
        $this->assertSame($visitorId, $result->visitorId);
        $this->assertSame($test->id, $result->abTestId);
        $this->assertSame($variant->slug, $result->variant->slug);
    }

    public function test_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByTestAndVisitor(999, 'nonexistent-visitor');

        $this->assertNull($result);
    }

    public function test_creates_assignment_from_dto(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);

        $visitorId = Str::uuid()->toString();

        $dto = new AbTestAssignmentDto(
            abTestId: $test->id,
            abTestVariantId: $variant->id,
            visitorId: $visitorId,
            userId: null,
        );

        $assignment = $this->repository->create($dto);

        $this->assertInstanceOf(AbTestAssignment::class, $assignment);
        $this->assertSame($variant->slug, $assignment->variant->slug);
        $this->assertDatabaseHas('ab_test_assignments', [
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => $visitorId,
            'user_id' => null,
        ]);
    }

    public function test_attach_user_links_visitor_to_user(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        $user = User::factory()->create();

        $visitorId = Str::uuid()->toString();

        $assignment = AbTestAssignmentModel::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => $visitorId,
            'user_id' => null,
        ]);

        $this->repository->attachUser($visitorId, $user->id);

        $this->assertDatabaseHas('ab_test_assignments', [
            'id' => $assignment->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_attach_user_skips_already_linked(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        $existingUser = User::factory()->create();
        $newUser = User::factory()->create();

        $visitorId = Str::uuid()->toString();

        $assignment = AbTestAssignmentModel::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => $visitorId,
            'user_id' => $existingUser->id,
        ]);

        $this->repository->attachUser($visitorId, $newUser->id);

        $this->assertDatabaseHas('ab_test_assignments', [
            'id' => $assignment->id,
            'user_id' => $existingUser->id,
        ]);
    }

    public function test_finds_assignment_by_test_and_user(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        $user = User::factory()->create();

        $assignment = AbTestAssignmentModel::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => Str::uuid()->toString(),
            'user_id' => $user->id,
        ]);

        $result = $this->repository->findByTestAndUser($test->id, $user->id);

        $this->assertNotNull($result);
        $this->assertSame($assignment->id, $result->id);
        $this->assertSame($user->id, $result->userId);
    }
}
