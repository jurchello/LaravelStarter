<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited(): void
    {
        Config::set('rate_limits.auth.login.max_attempts', 1);

        $user = User::factory()->create();

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'));

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_admin_search_api_is_rate_limited(): void
    {
        Config::set('rate_limits.admin.search.max_attempts', 1);

        $admin = User::factory()->create(['is_admin' => true]);
        Role::create(['name' => 'Admin']);

        $this->actingAs($admin)
            ->getJson('/management/api/roles/suggestions?query=ad')
            ->assertOk();

        $this->actingAs($admin)
            ->getJson('/management/api/roles/suggestions?query=ad')
            ->assertTooManyRequests();
    }
}
