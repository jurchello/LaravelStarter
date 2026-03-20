<?php

declare(strict_types=1);

namespace Tests\Integration\AbTesting;

use App\Domain\AbTesting\Dto\AbTestEventDto;
use App\Infrastructure\AbTesting\Persistence\EloquentAbTestEventRepository;
use App\Models\AbTestAssignment;
use App\Models\AbTestEvent;
use Database\Factories\AbTestFactory;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EloquentAbTestEventRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentAbTestEventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentAbTestEventRepository;
    }

    public function test_records_event(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);

        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => Str::uuid()->toString(),
        ]);

        $dto = new AbTestEventDto(
            abTestAssignmentId: $assignment->id,
            event: 'signup',
        );

        $this->repository->record($dto);

        $this->assertDatabaseHas('ab_test_events', [
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'signup',
        ]);
    }

    public function test_ab_test_event_factory_creates_event_linked_to_assignment(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
        ]);

        $event = AbTestEvent::factory()->create([
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'purchase',
        ]);

        $this->assertSame($assignment->id, $event->assignment->id);
        $this->assertDatabaseHas('ab_test_events', [
            'id' => $event->id,
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'purchase',
        ]);
    }
}
