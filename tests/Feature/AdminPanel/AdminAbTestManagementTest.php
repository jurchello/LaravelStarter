<?php

declare(strict_types=1);

namespace Tests\Feature\AdminPanel;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Models\AbTest;
use App\Models\AbTestAssignment;
use App\Models\AbTestEvent;
use App\Models\AbTestVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DisablesCsrfForWebMutations;
use Tests\TestCase;

final class AdminAbTestManagementTest extends TestCase
{
    use DisablesCsrfForWebMutations;
    use RefreshDatabase;

    public function test_admin_ab_test_create_page_renders_server_side_initial_state(): void
    {
        $response = $this->actingAs($this->admin())->get('/management/ab-tests/create');

        $response->assertOk()
            ->assertViewIs('admin-panel.ab-tests.create')
            ->assertSee('data-admin-page="ab-test-create"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-ab-test-create-endpoint="/management/api/ab-tests"', false)
            ->assertSee('data-ab-test-input="split-evenly"', false)
            ->assertSee('Create AB Test')
            ->assertSee('Create test');
    }

    public function test_admin_ab_test_management_page_renders_server_side_initial_state(): void
    {
        $test = AbTest::factory()->create([
            'name' => 'Homepage Hero',
            'slug' => 'homepage-hero',
            'status' => AbTestStatus::Active,
            'traffic_percent' => 75,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'name' => 'Control',
            'slug' => 'control',
            'weight' => 100,
        ]);

        $response = $this->actingAs($this->admin())->get("/management/ab-tests/{$test->id}");

        $response->assertOk()
            ->assertViewIs('admin-panel.ab-tests.show')
            ->assertSee('data-admin-page="ab-test-management"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-ab-test-endpoint="/management/api/ab-tests/'.$test->id.'"', false)
            ->assertSee('data-ab-test-variants-endpoint="/management/api/ab-tests/'.$test->id.'/variants"', false)
            ->assertSee('data-ab-test-input="split-evenly"', false)
            ->assertSee($test->name)
            ->assertSee($test->slug)
            ->assertSee('Control')
            ->assertSee('Save changes');
    }

    public function test_admin_ab_test_assignments_page_renders_server_side_initial_state(): void
    {
        $test = AbTest::factory()->create();
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'name' => 'Control',
            'slug' => 'control',
        ]);
        AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'visitor-001',
        ]);

        $response = $this->actingAs($this->admin())->get("/management/ab-tests/{$test->id}/assignments");

        $response->assertOk()
            ->assertViewIs('admin-panel.ab-tests.assignments')
            ->assertSee('data-admin-page="ab-test-assignments"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-ab-test-assignments-endpoint="/management/api/ab-tests/'.$test->id.'/assignments"', false)
            ->assertSee('visitor-001')
            ->assertSee('Control');
    }

    public function test_admin_ab_test_events_page_renders_server_side_initial_state(): void
    {
        $test = AbTest::factory()->create();
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'name' => 'Control',
            'slug' => 'control',
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

        $response = $this->actingAs($this->admin())->get("/management/ab-tests/{$test->id}/events");

        $response->assertOk()
            ->assertViewIs('admin-panel.ab-tests.events')
            ->assertSee('data-admin-page="ab-test-events"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-ab-test-events-endpoint="/management/api/ab-tests/'.$test->id.'/events"', false)
            ->assertSee('signup')
            ->assertSee('visitor-001');
    }

    public function test_admin_ab_test_analytics_page_renders_server_side_initial_state(): void
    {
        $test = AbTest::factory()->create([
            'status' => AbTestStatus::Active,
            'traffic_percent' => 100,
        ]);
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'name' => 'Control',
            'slug' => 'control',
            'weight' => 100,
        ]);
        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'visitor-001',
        ]);
        AbTestEvent::factory()->create([
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'purchase',
        ]);

        $response = $this->actingAs($this->admin())->get("/management/ab-tests/{$test->id}/analytics");

        $response->assertOk()
            ->assertViewIs('admin-panel.ab-tests.analytics')
            ->assertSee('data-admin-page="ab-test-analytics"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-ab-test-analytics-endpoint="/management/api/ab-tests/'.$test->id.'/analytics"', false)
            ->assertSee('purchase')
            ->assertSee('100%')
            ->assertSee('Control');
    }

    public function test_admin_ab_test_management_api_returns_detail_envelope(): void
    {
        $test = AbTest::factory()->create([
            'name' => 'Homepage Hero',
            'slug' => 'homepage-hero',
            'status' => AbTestStatus::Active,
            'traffic_percent' => 75,
        ]);
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'name' => 'Control',
            'slug' => 'control',
            'weight' => 100,
        ]);
        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'user_id' => User::factory()->create()->id,
            'visitor_id' => 'visitor-001',
        ]);
        AbTestEvent::factory()->create([
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'signup',
        ]);

        $response = $this->actingAs($this->admin())->getJson("/management/api/ab-tests/{$test->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $test->id)
            ->assertJsonPath('data.slug', 'homepage-hero')
            ->assertJsonPath('data.trafficPercent', 75)
            ->assertJsonPath('data.distributionMode', 'manual')
            ->assertJsonPath('data.variants.0.slug', 'control')
            ->assertJsonPath('data.analytics.assignmentsCount', 1)
            ->assertJsonPath('data.analytics.identifiedAssignmentsCount', 1)
            ->assertJsonPath('data.analytics.eventsByName.signup', 1)
            ->assertJsonPath('data.recentAssignments.0.visitorId', 'visitor-001')
            ->assertJsonPath('data.recentEvents.0.event', 'signup');
    }

    public function test_admin_can_create_ab_test(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/management/api/ab-tests', [
            'name' => 'Checkout Flow',
            'slug' => 'checkout-flow',
            'trafficPercent' => 80,
            'distributionMode' => 'manual',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Checkout Flow')
            ->assertJsonPath('data.slug', 'checkout-flow')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.trafficPercent', 80)
            ->assertJsonPath('data.distributionMode', 'manual');

        $this->assertDatabaseHas('ab_tests', [
            'name' => 'Checkout Flow',
            'slug' => 'checkout-flow',
            'status' => AbTestStatus::Draft->value,
            'traffic_percent' => 80,
            'distribution_mode' => 'manual',
        ]);
    }

    public function test_admin_can_create_ab_test_with_auto_generated_slug_from_cyrillic_name(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/management/api/ab-tests', [
            'name' => 'Тестова фіча',
            'trafficPercent' => 80,
            'distributionMode' => 'manual',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'testova-ficha');

        $this->assertDatabaseHas('ab_tests', [
            'name' => 'Тестова фіча',
            'slug' => 'testova-ficha',
        ]);
    }

    public function test_admin_can_update_ab_test(): void
    {
        $test = AbTest::factory()->inactive()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'traffic_percent' => 30,
        ]);

        $response = $this->actingAs($this->admin())->putJson("/management/api/ab-tests/{$test->id}", [
            'name' => 'New Name',
            'trafficPercent' => 55,
            'distributionMode' => 'manual',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.slug', 'old-name')
            ->assertJsonPath('data.trafficPercent', 55);

        $this->assertDatabaseHas('ab_tests', [
            'id' => $test->id,
            'name' => 'New Name',
            'slug' => 'old-name',
            'traffic_percent' => 55,
        ]);
    }

    public function test_admin_cannot_update_active_ab_test_to_invalid_configuration(): void
    {
        $test = AbTest::factory()->create([
            'status' => AbTestStatus::Active,
            'traffic_percent' => 50,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 100,
        ]);

        $response = $this->actingAs($this->admin())->putJson("/management/api/ab-tests/{$test->id}", [
            'name' => 'Broken Test',
            'slug' => $test->slug,
            'trafficPercent' => 0,
            'distributionMode' => 'manual',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.0', 'Active tests must use traffic between 1 and 100 percent.');

        $this->assertDatabaseHas('ab_tests', [
            'id' => $test->id,
            'traffic_percent' => 50,
            'slug' => $test->slug,
        ]);
    }

    public function test_admin_can_delete_ab_test(): void
    {
        $test = AbTest::factory()->create();

        $response = $this->actingAs($this->admin())->deleteJson("/management/api/ab-tests/{$test->id}");

        $response->assertOk()
            ->assertExactJson([
                'data' => null,
                'meta' => [],
                'errors' => [],
            ]);

        $this->assertDatabaseMissing('ab_tests', ['id' => $test->id]);
    }

    public function test_admin_can_activate_ab_test_when_configuration_is_valid(): void
    {
        $test = AbTest::factory()->inactive()->create();
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 100,
        ]);

        $response = $this->actingAs($this->admin())->patchJson("/management/api/ab-tests/{$test->id}/status", [
            'status' => 'active',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('ab_tests', [
            'id' => $test->id,
            'status' => AbTestStatus::Active->value,
        ]);
    }

    public function test_admin_cannot_activate_ab_test_without_variants(): void
    {
        $test = AbTest::factory()->inactive()->create();

        $response = $this->actingAs($this->admin())->patchJson("/management/api/ab-tests/{$test->id}/status", [
            'status' => 'active',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.0', 'Active tests must have at least one variant.');

        $this->assertDatabaseHas('ab_tests', [
            'id' => $test->id,
            'status' => AbTestStatus::Draft->value,
        ]);
    }

    public function test_admin_cannot_activate_ab_test_when_variant_weights_do_not_total_one_hundred(): void
    {
        $test = AbTest::factory()->inactive()->create();
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 40,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'slug' => 'variant-b',
            'name' => 'Variant B',
            'weight' => 40,
        ]);

        $response = $this->actingAs($this->admin())->patchJson("/management/api/ab-tests/{$test->id}/status", [
            'status' => 'active',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.0', 'Active tests must have variant weights totaling exactly 100.');
    }

    public function test_admin_can_activate_equal_split_ab_test_even_when_weights_do_not_total_one_hundred(): void
    {
        $test = AbTest::factory()->inactive()->create([
            'distribution_mode' => AbTestDistributionMode::Equal,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 33,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'slug' => 'variant-b',
            'name' => 'Variant B',
            'weight' => 33,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'slug' => 'variant-c',
            'name' => 'Variant C',
            'weight' => 33,
        ]);

        $response = $this->actingAs($this->admin())->patchJson("/management/api/ab-tests/{$test->id}/status", [
            'status' => 'active',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.distributionMode', 'equal');
    }

    public function test_admin_cannot_change_test_slug_after_creation(): void
    {
        $test = AbTest::factory()->create([
            'status' => AbTestStatus::Active,
            'slug' => 'original-slug',
            'traffic_percent' => 100,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 100,
        ]);

        $response = $this->actingAs($this->admin())->putJson("/management/api/ab-tests/{$test->id}", [
            'name' => 'Homepage Hero Updated',
            'slug' => 'new-slug',
            'trafficPercent' => 100,
            'distributionMode' => 'manual',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.0', 'Test slug cannot change after creation.');

        $this->assertDatabaseHas('ab_tests', [
            'id' => $test->id,
            'slug' => 'original-slug',
        ]);
    }

    public function test_admin_cannot_change_variant_slug_after_draft(): void
    {
        $test = AbTest::factory()->create([
            'status' => AbTestStatus::Active,
        ]);
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'slug' => 'control',
            'weight' => 100,
        ]);

        $response = $this->actingAs($this->admin())->putJson("/management/api/ab-tests/{$test->id}/variants/{$variant->id}", [
            'name' => 'Control Updated',
            'slug' => 'control-v2',
            'weight' => 100,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.0', 'Variant slug cannot change after the draft state.');
    }

    public function test_admin_cannot_change_finished_test_status(): void
    {
        $test = AbTest::factory()->create([
            'status' => AbTestStatus::Finished,
        ]);
        AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 100,
        ]);

        $response = $this->actingAs($this->admin())->patchJson("/management/api/ab-tests/{$test->id}/status", [
            'status' => 'active',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.0', 'Finished tests cannot change status.');
    }

    public function test_admin_can_create_update_and_delete_variants(): void
    {
        $test = AbTest::factory()->inactive()->create();

        $createResponse = $this->actingAs($this->admin())->postJson("/management/api/ab-tests/{$test->id}/variants", [
            'name' => 'Control',
            'slug' => 'control',
            'weight' => 60,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.variants.0.slug', 'control');

        $variantId = AbTestVariant::query()->where('ab_test_id', $test->id)->value('id');

        $updateResponse = $this->actingAs($this->admin())->putJson("/management/api/ab-tests/{$test->id}/variants/{$variantId}", [
            'name' => 'Variant B',
            'slug' => 'variant-b',
            'weight' => 80,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.variants.0.slug', 'variant-b')
            ->assertJsonPath('data.variants.0.weight', 80);

        $deleteResponse = $this->actingAs($this->admin())->deleteJson("/management/api/ab-tests/{$test->id}/variants/{$variantId}");

        $deleteResponse->assertOk()
            ->assertJsonCount(0, 'data.variants');

        $this->assertDatabaseMissing('ab_test_variants', ['id' => $variantId]);
    }

    public function test_admin_cannot_delete_last_variant_from_active_test(): void
    {
        $test = AbTest::factory()->create([
            'status' => AbTestStatus::Active,
        ]);
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 100,
        ]);

        $response = $this->actingAs($this->admin())->deleteJson("/management/api/ab-tests/{$test->id}/variants/{$variant->id}");

        $response->assertUnprocessable()
            ->assertJsonPath('errors.0', 'Active tests must have at least one variant.');

        $this->assertDatabaseHas('ab_test_variants', ['id' => $variant->id]);
    }

    public function test_admin_ab_test_assignments_api_returns_paginated_envelope(): void
    {
        $test = AbTest::factory()->create();
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
        ]);
        AbTestAssignment::factory()->count(3)->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
        ]);

        $response = $this->actingAs($this->admin())->getJson("/management/api/ab-tests/{$test->id}/assignments");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        ['id', 'visitorId', 'userId', 'variantName', 'variantSlug', 'createdAt'],
                    ],
                ],
                'meta' => ['page', 'perPage', 'total', 'totalPages'],
                'errors',
            ]);
    }

    public function test_admin_ab_test_events_api_returns_paginated_envelope(): void
    {
        $test = AbTest::factory()->create();
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
        ]);
        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
        ]);
        AbTestEvent::factory()->count(2)->create([
            'ab_test_assignment_id' => $assignment->id,
        ]);

        $response = $this->actingAs($this->admin())->getJson("/management/api/ab-tests/{$test->id}/events");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        ['id', 'event', 'variantName', 'variantSlug', 'visitorId', 'createdAt'],
                    ],
                ],
                'meta' => ['page', 'perPage', 'total', 'totalPages'],
                'errors',
            ]);
    }

    public function test_admin_ab_test_analytics_api_returns_envelope(): void
    {
        $test = AbTest::factory()->create([
            'status' => AbTestStatus::Active,
            'traffic_percent' => 100,
        ]);
        $variant = AbTestVariant::factory()->create([
            'ab_test_id' => $test->id,
            'weight' => 100,
        ]);
        $assignment = AbTestAssignment::factory()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
        ]);
        AbTestEvent::factory()->create([
            'ab_test_assignment_id' => $assignment->id,
            'event' => 'purchase',
        ]);

        $response = $this->actingAs($this->admin())->getJson("/management/api/ab-tests/{$test->id}/analytics");

        $response->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.analytics.assignmentsCount', 1)
            ->assertJsonPath('data.analytics.eventsByName.purchase', 1);
    }

    public function test_admin_ab_test_audience_estimate_api_returns_envelope(): void
    {
        $admin = $this->admin();
        User::factory()->count(9)->create();

        $response = $this->actingAs($admin)->getJson('/management/api/ab-tests/audience-estimate?trafficPercent=30');

        $response->assertOk()
            ->assertJsonPath('data.audienceSize', 10)
            ->assertJsonPath('data.trafficPercent', 30)
            ->assertJsonPath('data.estimatedPeople', 3);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}
