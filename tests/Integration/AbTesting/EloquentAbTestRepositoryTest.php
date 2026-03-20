<?php

declare(strict_types=1);

namespace Tests\Integration\AbTesting;

use App\Domain\AbTesting\Dto\AbTestData;
use App\Domain\AbTesting\Dto\AbTestVariantData;
use App\Domain\AbTesting\Entities\AbTest;
use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\ValueObjects\AbTestListQuery;
use App\Infrastructure\AbTesting\Persistence\EloquentAbTestRepository;
use App\Models\AbTest as AbTestModel;
use App\Models\AbTestAssignment;
use App\Models\AbTestEvent;
use App\Models\User;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentAbTestRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentAbTestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentAbTestRepository;
    }

    public function test_finds_active_test_by_slug(): void
    {
        AbTestModel::factory()->create([
            'slug' => 'my-test',
            'status' => AbTestStatus::Active,
        ]);

        $result = $this->repository->findActiveBySlug('my-test');

        $this->assertInstanceOf(AbTest::class, $result);
        $this->assertNotNull($result);
        $this->assertSame('my-test', $result->slug);
    }

    public function test_returns_null_for_draft_test(): void
    {
        AbTestModel::factory()->inactive()->create([
            'slug' => 'draft-test',
        ]);

        $result = $this->repository->findActiveBySlug('draft-test');

        $this->assertNull($result);
    }

    public function test_returns_null_when_slug_not_found(): void
    {
        $result = $this->repository->findActiveBySlug('nonexistent');

        $this->assertNull($result);
    }

    public function test_eager_loads_variants(): void
    {
        $test = AbTestModel::factory()->create([
            'slug' => 'variant-test',
            'status' => AbTestStatus::Active,
        ]);

        AbTestVariantFactory::new()->create([
            'ab_test_id' => $test->id,
            'slug' => 'variant-a',
        ]);
        AbTestVariantFactory::new()->create([
            'ab_test_id' => $test->id,
            'slug' => 'variant-b',
        ]);

        $result = $this->repository->findActiveBySlug('variant-test');

        $this->assertNotNull($result);
        $this->assertInstanceOf(AbTest::class, $result);
        $this->assertCount(2, $result->variants);
        $this->assertSame('variant-a', $result->variants[0]->slug);
        $this->assertSame('variant-b', $result->variants[1]->slug);
    }

    public function test_paginates_ab_tests_for_admin_listing(): void
    {
        AbTestModel::factory()->create(['name' => 'Homepage Hero', 'slug' => 'homepage-hero', 'status' => AbTestStatus::Active]);
        AbTestModel::factory()->inactive()->create(['name' => 'Pricing Layout', 'slug' => 'pricing-layout']);

        $result = $this->repository->paginate(AbTestListQuery::fromScalars(
            search: 'home',
            status: 'active',
        ));

        $this->assertCount(1, $result->items);
        $this->assertSame('Homepage Hero', $result->items[0]->name);
        $this->assertSame('active', $result->items[0]->status);
    }

    public function test_returns_search_suggestions_for_name_and_slug(): void
    {
        AbTestModel::factory()->create(['name' => 'Homepage Hero', 'slug' => 'homepage-hero']);

        $result = $this->repository->suggest('home');

        $this->assertSame(['Homepage Hero', 'homepage-hero'], $result);
    }

    public function test_returns_management_view_with_analytics(): void
    {
        $test = AbTestModel::factory()->create([
            'name' => 'Homepage Hero',
            'slug' => 'homepage-hero',
            'status' => AbTestStatus::Active,
            'traffic_percent' => 75,
        ]);
        $variant = AbTestVariantFactory::new()->create([
            'ab_test_id' => $test->id,
            'name' => 'Control',
            'slug' => 'control',
            'weight' => 100,
        ]);
        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'user_id' => User::factory()->create()->id,
            'visitor_id' => 'visitor-123',
        ]);
        AbTestEvent::factory()->create([
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'signup',
        ]);

        $result = $this->repository->findManagementView($test->id);

        $this->assertNotNull($result);
        $this->assertSame('homepage-hero', $result->slug);
        $this->assertCount(1, $result->variants);
        $this->assertSame(1, $result->analytics->assignmentsCount);
        $this->assertSame(1, $result->analytics->identifiedAssignmentsCount);
        $this->assertSame(1, $result->analytics->eventsByName['signup']);
        $this->assertSame('visitor-123', $result->recentAssignments[0]->visitorId);
        $this->assertSame('signup', $result->recentEvents[0]->event);
    }

    public function test_creates_updates_and_deletes_management_view(): void
    {
        $created = $this->repository->createManagementView(new AbTestData(
            name: 'Checkout Flow',
            slug: 'checkout-flow',
            trafficPercent: 60,
            distributionMode: AbTestDistributionMode::Manual,
        ));

        $this->assertSame('draft', $created->status);
        $this->assertDatabaseHas('ab_tests', [
            'id' => $created->id,
            'slug' => 'checkout-flow',
            'traffic_percent' => 60,
        ]);

        $updated = $this->repository->updateManagementView($created->id, new AbTestData(
            name: 'Checkout Flow B',
            slug: 'checkout-flow-b',
            trafficPercent: 80,
            distributionMode: AbTestDistributionMode::Equal,
        ));

        $this->assertNotNull($updated);
        $this->assertSame('checkout-flow-b', $updated->slug);
        $this->assertSame(AbTestDistributionMode::Equal, $updated->distributionMode);

        $this->assertTrue($this->repository->deleteManagementView($created->id));
        $this->assertDatabaseMissing('ab_tests', ['id' => $created->id]);
    }

    public function test_updates_status_and_manages_variants(): void
    {
        $test = AbTestModel::factory()->inactive()->create();

        $createdVariant = $this->repository->createVariant($test->id, new AbTestVariantData(
            name: 'Control',
            slug: 'control',
            weight: 100,
        ));

        $this->assertNotNull($createdVariant);
        $this->assertCount(1, $createdVariant->variants);

        $variantId = $createdVariant->variants[0]->id;

        $updatedVariant = $this->repository->updateVariant($test->id, $variantId, new AbTestVariantData(
            name: 'Treatment',
            slug: 'treatment',
            weight: 70,
        ));

        $this->assertNotNull($updatedVariant);
        $this->assertSame('treatment', $updatedVariant->variants[0]->slug);

        $updatedStatus = $this->repository->updateStatus($test->id, AbTestStatus::Paused);

        $this->assertNotNull($updatedStatus);
        $this->assertSame('paused', $updatedStatus->status);

        $afterDelete = $this->repository->deleteVariant($test->id, $variantId);

        $this->assertNotNull($afterDelete);
        $this->assertCount(0, $afterDelete->variants);
    }

    public function test_paginates_assignments_and_events_for_management_subpages(): void
    {
        $test = AbTestModel::factory()->create();
        $variant = AbTestVariantFactory::new()->create([
            'ab_test_id' => $test->id,
            'slug' => 'control',
            'name' => 'Control',
        ]);
        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'visitor-001',
        ]);
        AbTestEvent::factory()->create([
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'signup',
        ]);

        $assignments = $this->repository->paginateAssignments($test->id, page: 1, perPage: 25);
        $events = $this->repository->paginateEvents($test->id, page: 1, perPage: 25);

        $this->assertNotNull($assignments);
        $this->assertNotNull($events);
        $this->assertSame('visitor-001', $assignments->items[0]->visitorId);
        $this->assertSame('signup', $events->items[0]->event);
    }
}
