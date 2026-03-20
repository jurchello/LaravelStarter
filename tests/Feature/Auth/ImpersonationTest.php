<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_verified_admin_can_impersonate_user_from_admin_panel(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.api.users.impersonate', $user));

        $response->assertOk()
            ->assertJsonPath('data.redirect.redirectTo', route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertSame($admin->id, session('impersonator_id'));
        $this->assertSame($admin->name, session('impersonator_name'));
    }

    public function test_impersonated_session_displays_full_width_banner_on_site_pages(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->withSession([
                'impersonator_id' => $admin->id,
                'impersonator_name' => $admin->name,
            ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('data-testid="impersonation-banner"', false)
            ->assertSee($admin->name)
            ->assertSee(route('impersonation.leave', absolute: false));
    }

    public function test_impersonated_user_can_return_to_original_admin_account(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $user = User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'impersonator_id' => $admin->id,
                'impersonator_name' => $admin->name,
            ])
            ->post(route('impersonation.leave'));

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonator_id'));
        $this->assertNull(session('impersonator_name'));
    }

    public function test_leave_impersonation_route_is_hidden_when_not_impersonating(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('impersonation.leave'));

        $response->assertNotFound();
    }
}
