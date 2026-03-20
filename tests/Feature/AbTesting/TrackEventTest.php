<?php

declare(strict_types=1);

namespace Tests\Feature\AbTesting;

use App\Http\Middleware\SetVisitorId;
use App\Models\User;
use Database\Factories\AbTestAssignmentFactory;
use Database\Factories\AbTestFactory;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TrackEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_ok_false_when_no_cookie(): void
    {
        $response = $this->postJson('/api/ab/event', [
            'test' => 'my-test',
            'event' => 'signup',
        ]);

        $response->assertOk()
            ->assertExactJson(['ok' => false]);
    }

    public function test_records_event_for_known_visitor(): void
    {
        $test = AbTestFactory::new()->create(['slug' => 'my-test']);
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        AbTestAssignmentFactory::new()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'test-visitor-uuid',
        ]);

        $response = $this->withCredentials()
            ->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'test-visitor-uuid'])
            ->postJson('/api/ab/event', [
                'test' => 'my-test',
                'event' => 'signup',
            ]);

        $response->assertOk()
            ->assertExactJson(['ok' => true]);

        $this->assertDatabaseHas('ab_test_events', ['event' => 'signup']);
    }

    public function test_validates_test_field_required(): void
    {
        $response = $this->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'test-visitor-uuid'])
            ->postJson('/api/ab/event', ['event' => 'signup']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['test']);
    }

    public function test_validates_event_field_required(): void
    {
        $response = $this->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'test-visitor-uuid'])
            ->postJson('/api/ab/event', ['test' => 'my-test']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['event']);
    }

    public function test_endpoint_is_public(): void
    {
        $response = $this->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'test-visitor-uuid'])
            ->postJson('/api/ab/event', [
                'test' => 'my-test',
                'event' => 'signup',
            ]);

        $response->assertOk();
    }

    public function test_records_event_for_authenticated_user_when_assignment_exists_under_another_visitor_id(): void
    {
        $user = User::factory()->create();
        $test = AbTestFactory::new()->create(['slug' => 'my-test']);
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        AbTestAssignmentFactory::new()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'old-visitor-uuid',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->withCredentials()
            ->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'new-visitor-uuid'])
            ->postJson('/api/ab/event', [
                'test' => 'my-test',
                'event' => 'signup',
            ]);

        $response->assertOk()
            ->assertExactJson(['ok' => true]);

        $this->assertDatabaseHas('ab_test_events', ['event' => 'signup']);
    }
}
