<?php

declare(strict_types=1);

namespace Tests\Feature\AbTesting;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Http\Middleware\SetVisitorId;
use App\Models\User;
use Database\Factories\AbTestAssignmentFactory;
use Database\Factories\AbTestFactory;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AssignVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_variant_when_no_visitor_cookie(): void
    {
        $response = $this->getJson('/api/ab/assign/my-test');

        $response->assertOk()
            ->assertJson(['variant' => null]);
    }

    public function test_returns_variant_for_enrolled_visitor(): void
    {
        $test = AbTestFactory::new()->create(['slug' => 'my-test', 'traffic_percent' => 100]);
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id, 'weight' => 100]);

        $response = $this->withCredentials()
            ->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'test-visitor-uuid'])
            ->getJson('/api/ab/assign/my-test');

        $response->assertOk()
            ->assertJson(['variant' => $variant->slug]);
    }

    public function test_returns_null_for_inactive_test(): void
    {
        AbTestFactory::new()->inactive()->create(['slug' => 'inactive-test']);

        $response = $this->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'test-visitor-uuid'])
            ->getJson('/api/ab/assign/inactive-test');

        $response->assertOk()
            ->assertJson(['variant' => null]);
    }

    public function test_endpoint_is_public(): void
    {
        $response = $this->getJson('/api/ab/assign/any-test');

        $response->assertOk();
    }

    public function test_authenticated_user_reuses_assignment_from_another_visitor_id(): void
    {
        $user = User::factory()->create();
        $test = AbTestFactory::new()->create(['slug' => 'my-test', 'traffic_percent' => 100]);
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id, 'weight' => 100]);
        AbTestAssignmentFactory::new()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'old-visitor-uuid',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->withCredentials()
            ->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'new-visitor-uuid'])
            ->getJson('/api/ab/assign/my-test');

        $response->assertOk()
            ->assertJson(['variant' => $variant->slug]);

        $this->assertDatabaseCount('ab_test_assignments', 1);
    }

    public function test_equal_split_mode_can_assign_any_variant_without_weight_bias_rules(): void
    {
        $test = AbTestFactory::new()->create([
            'slug' => 'my-test',
            'traffic_percent' => 100,
            'distribution_mode' => AbTestDistributionMode::Equal,
        ]);
        AbTestVariantFactory::new()->create(['ab_test_id' => $test->id, 'slug' => 'variant-a', 'weight' => 33]);
        AbTestVariantFactory::new()->create(['ab_test_id' => $test->id, 'slug' => 'variant-b', 'weight' => 33]);
        AbTestVariantFactory::new()->create(['ab_test_id' => $test->id, 'slug' => 'variant-c', 'weight' => 33]);

        $response = $this->withCredentials()
            ->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'test-visitor-uuid'])
            ->getJson('/api/ab/assign/my-test');

        $response->assertOk();
        $this->assertContains($response->json('variant'), ['variant-a', 'variant-b', 'variant-c']);
    }
}
