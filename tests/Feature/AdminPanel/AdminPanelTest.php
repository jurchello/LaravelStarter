<?php

declare(strict_types=1);

namespace Tests\Feature\AdminPanel;

use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Models\AbTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\DisablesCsrfForWebMutations;
use Tests\TestCase;

final class AdminPanelTest extends TestCase
{
    use DisablesCsrfForWebMutations;
    use RefreshDatabase;

    public function test_guest_gets_404(): void
    {
        $response = $this->get('/management/users');

        $response->assertNotFound();
    }

    public function test_non_admin_gets_404(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/management/users');

        $response->assertNotFound();
    }

    public function test_unverified_admin_gets_404(): void
    {
        $admin = User::factory()->unverified()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management/users');

        $response->assertNotFound();
    }

    public function test_admin_dashboard_page_renders_server_side_initial_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get('/management');

        $response->assertOk()
            ->assertViewIs('admin-panel.dashboard')
            ->assertSee('data-admin-page="dashboard"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-dashboard-endpoint="/management/api/dashboard"', false)
            ->assertSee('Total users')
            ->assertSee('Verified users')
            ->assertSee('Admins')
            ->assertSee('New this week');
    }

    public function test_admin_users_page_renders_server_side_initial_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'is_admin' => false,
            'name' => 'Alice Example',
            'email' => 'alice@example.com',
        ]);

        $response = $this->actingAs($admin)->get('/management/users');

        $response->assertOk()
            ->assertViewIs('admin-panel.users.index')
            ->assertSee('data-admin-page="users"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-users-endpoint="/management/api/users"', false)
            ->assertSee('data-users-suggestions-endpoint="/management/api/users/suggestions"', false)
            ->assertSee('data-users-impersonate-base=', false)
            ->assertSee('data-users-filter-section', false)
            ->assertSee('users-table-row', false)
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    public function test_admin_roles_page_link_is_present_in_sidebar(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management');

        $response->assertOk()
            ->assertSee('data-testid="admin-nav-roles"', false);
    }

    public function test_admin_ui_kit_page_renders(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management/ui-kit');

        $response->assertOk()
            ->assertViewIs('admin-panel.ui-kit')
            ->assertSee('Admin UI Kit')
            ->assertSee('data-testid="admin-ui-kit-page"', false);
    }

    public function test_admin_mail_previews_page_renders_in_testing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management/mail-previews');

        $response->assertOk()
            ->assertViewIs('admin-panel.mail-previews.index')
            ->assertSee('data-admin-page="mail-previews"', false)
            ->assertSee('data-testid="admin-mail-preview-verify-email"', false)
            ->assertSee('data-testid="admin-mail-preview-password-reset"', false);
    }

    public function test_admin_layout_contains_mail_preview_link_in_testing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management');

        $response->assertOk()
            ->assertSee('data-testid="admin-nav-mail-previews"', false);
    }

    public function test_admin_can_render_verify_email_preview(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management/mail-previews/verify-email');

        $response->assertOk()
            ->assertSee('Verify Email Address')
            ->assertSee('/verify-email/', false);
    }

    public function test_admin_can_render_password_reset_preview(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management/mail-previews/password-reset');

        $response->assertOk()
            ->assertSee('Reset Password')
            ->assertSee('preview-reset-token', false);
    }

    public function test_admin_layout_contains_api_docs_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/management');

        $response->assertOk()
            ->assertSee('data-testid="admin-nav-api-docs"', false)
            ->assertSee('href="'.route('docs.site.ui').'"', false)
            ->assertSee('href="'.route('docs.admin.ui').'"', false);
    }

    public function test_admin_ab_tests_page_renders_server_side_initial_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $test = AbTest::factory()->create([
            'name' => 'Homepage Hero',
            'slug' => 'homepage-hero',
            'status' => AbTestStatus::Draft,
            'traffic_percent' => 75,
        ]);

        $response = $this->actingAs($admin)->get('/management/ab-tests');

        $response->assertOk()
            ->assertViewIs('admin-panel.ab-tests.index')
            ->assertSee('data-admin-page="ab-tests"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-ab-tests-endpoint="/management/api/ab-tests"', false)
            ->assertSee('data-ab-tests-suggestions-endpoint="/management/api/ab-tests/suggestions"', false)
            ->assertSee('ab-tests-table-row', false)
            ->assertSee($test->name)
            ->assertSee($test->slug);
    }

    public function test_admin_dashboard_api_returns_envelope(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(4)->create();

        $response = $this->actingAs($admin)->getJson('/management/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        ['label', 'value', 'tone'],
                    ],
                ],
                'meta',
                'errors',
            ]);
    }

    public function test_admin_users_api_returns_paginated_envelope(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(55)->create();

        $response = $this->actingAs($admin)->getJson('/management/api/users?page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        ['id', 'name', 'email', 'isAdmin', 'isSuperadmin', 'roles', 'registeredAt'],
                    ],
                    'roleFilters',
                    'assignableRoles',
                ],
                'meta' => ['page', 'perPage', 'total', 'totalPages'],
                'errors',
            ])
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.perPage', 50);
    }

    public function test_admin_users_api_can_sort_by_name_ascending(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Nestor Admin']);
        User::factory()->create(['name' => 'Zulu User']);
        User::factory()->create(['name' => 'Alpha User']);

        $response = $this->actingAs($admin)->getJson('/management/api/users?sort=name&direction=asc');

        $response->assertOk()
            ->assertJsonPath('data.items.0.name', 'Alpha User')
            ->assertJsonPath('data.items.1.name', 'Nestor Admin');
    }

    public function test_admin_users_api_can_filter_by_search_and_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Admin User']);
        $alice = User::factory()->create(['is_admin' => false, 'name' => 'Alice Admin', 'email' => 'alice@example.com']);
        User::factory()->create(['is_admin' => false, 'name' => 'Bob Member', 'email' => 'bob@example.com']);

        Role::create(['name' => 'Manager']);
        $alice->assignRole('Manager');

        $response = $this->actingAs($admin)->getJson('/management/api/users?search=alice&role=Manager');

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', 'Alice Admin')
            ->assertJsonPath('data.items.0.roles.0', 'Manager');
    }

    public function test_admin_users_api_marks_superadmin_separately_from_roles(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Nestor Admin']);

        $response = $this->actingAs($admin)->getJson('/management/api/users?search=nestor');

        $response->assertOk()
            ->assertJsonPath('data.items.0.isSuperadmin', true)
            ->assertJsonPath('data.items.0.roles', []);
    }

    public function test_admin_can_update_user_roles_via_api(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Developer']);

        $response = $this->actingAs($admin)->patchJson("/management/api/users/{$user->id}/roles", [
            'roles' => ['Manager', 'Developer'],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.roles.0', 'Developer')
            ->assertJsonPath('data.user.roles.1', 'Manager');

        $this->assertSame(['Developer', 'Manager'], $user->fresh()->roles->pluck('name')->sort()->values()->all());
    }

    public function test_admin_user_suggestions_api_returns_name_and_email_matches(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['name' => 'Alice Admin', 'email' => 'alice@example.com']);

        $response = $this->actingAs($admin)->getJson('/management/api/users/suggestions?query=ali');

        $response->assertOk()
            ->assertJsonPath('data.items.0', 'Alice Admin')
            ->assertJsonPath('data.items.1', 'alice@example.com');
    }

    public function test_admin_ab_tests_api_returns_paginated_envelope(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        AbTest::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/management/api/ab-tests');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        ['id', 'name', 'slug', 'status', 'trafficPercent', 'variantsCount'],
                    ],
                ],
                'meta' => ['page', 'perPage', 'total', 'totalPages'],
                'errors',
            ]);
    }

    public function test_admin_ab_tests_api_can_filter_by_search_and_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        AbTest::factory()->create([
            'name' => 'Homepage Hero',
            'slug' => 'homepage-hero',
            'status' => AbTestStatus::Active,
        ]);
        AbTest::factory()->inactive()->create([
            'name' => 'Pricing Layout',
            'slug' => 'pricing-layout',
        ]);

        $response = $this->actingAs($admin)->getJson('/management/api/ab-tests?search=home&status=active');

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.slug', 'homepage-hero')
            ->assertJsonPath('data.items.0.status', 'active');
    }

    public function test_admin_ab_tests_api_can_sort_by_name_descending(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        AbTest::factory()->create(['name' => 'Alpha Test', 'slug' => 'alpha-test']);
        AbTest::factory()->create(['name' => 'Zulu Test', 'slug' => 'zulu-test']);

        $response = $this->actingAs($admin)->getJson('/management/api/ab-tests?sort=name&direction=desc');

        $response->assertOk()
            ->assertJsonPath('data.items.0.name', 'Zulu Test');
    }

    public function test_admin_ab_test_suggestions_api_returns_name_and_slug_matches(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        AbTest::factory()->create([
            'name' => 'Homepage Hero',
            'slug' => 'homepage-hero',
        ]);

        $response = $this->actingAs($admin)->getJson('/management/api/ab-tests/suggestions?query=home');

        $response->assertOk()
            ->assertJsonPath('data.items.0', 'Homepage Hero')
            ->assertJsonPath('data.items.1', 'homepage-hero');
    }
}
