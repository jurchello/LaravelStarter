<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Middleware\SetVisitorId;
use App\Models\User;
use Database\Factories\AbTestAssignmentFactory;
use Database\Factories\AbTestFactory;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->withoutMiddleware(EncryptCookies::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_the_login_screen_with_remember_checkbox_value(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_failed_login_uses_toast_payload_instead_of_inline_error_markup(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('login'))
            ->followingRedirects()
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $response->assertOk()
            ->assertSee('data-toast-payloads', false)
            ->assertDontSee('site-error', false);
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_login_attaches_existing_guest_ab_assignments_to_user(): void
    {
        $user = User::factory()->create();
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        AbTestAssignmentFactory::new()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'visitor-uuid',
            'user_id' => null,
        ]);

        $this->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'visitor-uuid'])
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('ab_test_assignments', [
            'ab_test_id' => $test->id,
            'visitor_id' => 'visitor-uuid',
            'user_id' => $user->id,
        ]);
    }
}
