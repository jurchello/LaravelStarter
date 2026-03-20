<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SitePageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_server_side_initial_state(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('welcome')
            ->assertSee('data-site-page="welcome"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-testid="site-welcome-page"', false);
    }

    public function test_dashboard_page_renders_server_side_initial_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('dashboard')
            ->assertSee('data-site-page="dashboard"', false)
            ->assertSee('data-page-state="ready"', false)
            ->assertSee('data-testid="site-dashboard-page"', false);
    }
}
