<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Middleware\SetVisitorId;
use App\Models\User;
use Database\Factories\AbTestAssignmentFactory;
use Database\Factories\AbTestFactory;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->withoutMiddleware(EncryptCookies::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Registered::class);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_attaches_existing_guest_ab_assignments_to_new_user(): void
    {
        Event::fake([Registered::class]);
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        AbTestAssignmentFactory::new()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'visitor-uuid',
            'user_id' => null,
        ]);

        $this->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'visitor-uuid'])
            ->post(route('register.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertDatabaseHas('ab_test_assignments', [
            'ab_test_id' => $test->id,
            'visitor_id' => 'visitor-uuid',
            'user_id' => $user->id,
        ]);
    }
}
