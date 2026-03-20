<?php

declare(strict_types=1);

namespace Tests\Feature\AdminPanel;

use App\Events\AdminPanel\FeatureFlagsChanged;
use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Pennant\Feature;
use Tests\Concerns\DisablesCsrfForWebMutations;
use Tests\TestCase;

final class AdminFeatureFlagManagementTest extends TestCase
{
    use DisablesCsrfForWebMutations;
    use RefreshDatabase;

    public function test_admin_feature_flags_page_renders_server_side_initial_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $flag = FeatureFlag::factory()->create(['key' => 'new-dashboard', 'name' => 'New Dashboard']);

        $response = $this->actingAs($admin)->get('/management/feature-flags');

        $response->assertOk()
            ->assertViewIs('admin-panel.feature-flags.index')
            ->assertSee('data-admin-page="feature-flags"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-feature-flags-endpoint="/management/api/feature-flags"', false)
            ->assertSee('data-feature-flags-suggestions-endpoint="/management/api/feature-flags/suggestions"', false)
            ->assertSee('feature-flags-table-row', false)
            ->assertSee($flag->key)
            ->assertSee($flag->name);
    }

    public function test_admin_feature_flags_api_returns_paginated_envelope(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        FeatureFlag::factory()->count(2)->create();

        $response = $this->actingAs($admin)->getJson('/management/api/feature-flags');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        ['id', 'key', 'name', 'description', 'enabled', 'rolloutPercent'],
                    ],
                ],
                'meta' => ['page', 'perPage', 'total', 'totalPages'],
                'errors',
            ]);
    }

    public function test_admin_can_filter_feature_flags_by_search_and_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        FeatureFlag::factory()->create(['key' => 'new-dashboard', 'enabled' => true]);
        FeatureFlag::factory()->create(['key' => 'legacy-widget', 'enabled' => false]);

        $response = $this->actingAs($admin)->getJson('/management/api/feature-flags?search=new&status=enabled');

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.key', 'new-dashboard');
    }

    public function test_admin_can_create_update_and_delete_feature_flag(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Event::fake([FeatureFlagsChanged::class]);

        $created = $this->actingAs($admin)->postJson('/management/api/feature-flags', [
            'key' => 'new-dashboard',
            'name' => 'New Dashboard',
            'description' => 'Gradual rollout for the new dashboard.',
            'enabled' => true,
            'rolloutPercent' => 25,
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.flag.key', 'new-dashboard')
            ->assertJsonPath('data.flag.rolloutPercent', 25);

        $flagId = $created->json('data.flag.id');

        $updated = $this->actingAs($admin)->putJson("/management/api/feature-flags/{$flagId}", [
            'key' => 'new-dashboard',
            'name' => 'Updated Dashboard',
            'description' => 'Updated description.',
            'enabled' => false,
            'rolloutPercent' => 0,
        ]);

        $updated->assertOk()
            ->assertJsonPath('data.flag.name', 'Updated Dashboard')
            ->assertJsonPath('data.flag.enabled', false);

        $deleted = $this->actingAs($admin)->deleteJson("/management/api/feature-flags/{$flagId}");

        $deleted->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing('feature_flags', ['id' => $flagId]);
        Event::assertDispatched(FeatureFlagsChanged::class, fn (FeatureFlagsChanged $event): bool => $event->action === 'created');
        Event::assertDispatched(FeatureFlagsChanged::class, fn (FeatureFlagsChanged $event): bool => $event->action === 'updated');
        Event::assertDispatched(FeatureFlagsChanged::class, fn (FeatureFlagsChanged $event): bool => $event->action === 'deleted');
    }

    public function test_admin_can_authorize_feature_flags_broadcast_channel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/broadcasting/auth', [
            'channel_name' => 'private-admin.feature-flags',
            'socket_id' => '1234.5678',
        ]);

        $response->assertOk();
    }

    public function test_admin_feature_flag_suggestions_api_returns_matching_keys(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        FeatureFlag::factory()->create(['key' => 'new-dashboard']);

        $response = $this->actingAs($admin)->getJson('/management/api/feature-flags/suggestions?query=new');

        $response->assertOk()
            ->assertJsonPath('data.items.0', 'new-dashboard');
    }

    public function test_admin_can_create_feature_flag_that_is_evaluated_by_pennant(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->postJson('/management/api/feature-flags', [
            'key' => 'new-dashboard',
            'name' => 'New Dashboard',
            'description' => 'Gradual rollout for the new dashboard.',
            'enabled' => true,
            'rolloutPercent' => 100,
        ])->assertCreated();

        self::assertTrue(Feature::for('user-1')->active('new-dashboard'));
        $this->assertDatabaseHas('features', [
            'name' => 'new-dashboard',
            'scope' => 'user-1',
            'value' => 'true',
        ]);
    }

    public function test_updating_feature_flag_purges_pennant_state_and_recalculates_runtime_value(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $created = $this->actingAs($admin)->postJson('/management/api/feature-flags', [
            'key' => 'new-dashboard',
            'name' => 'New Dashboard',
            'description' => 'Gradual rollout for the new dashboard.',
            'enabled' => true,
            'rolloutPercent' => 100,
        ])->assertCreated();

        $flagId = (int) $created->json('data.flag.id');

        self::assertTrue(Feature::for('user-1')->active('new-dashboard'));
        $this->assertDatabaseHas('features', [
            'name' => 'new-dashboard',
            'scope' => 'user-1',
        ]);

        $this->actingAs($admin)->putJson("/management/api/feature-flags/{$flagId}", [
            'key' => 'new-dashboard',
            'name' => 'New Dashboard',
            'description' => 'Disabled dashboard.',
            'enabled' => false,
            'rolloutPercent' => 0,
        ])->assertOk();

        $this->assertDatabaseMissing('features', [
            'name' => 'new-dashboard',
            'scope' => 'user-1',
        ]);
        self::assertFalse(Feature::for('user-1')->active('new-dashboard'));
    }
}
