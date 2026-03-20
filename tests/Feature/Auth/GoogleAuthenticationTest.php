<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Middleware\SetVisitorId;
use App\Models\SocialAccount;
use App\Models\User;
use Database\Factories\AbTestAssignmentFactory;
use Database\Factories\AbTestFactory;
use Database\Factories\AbTestVariantFactory;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Mockery;
use Tests\TestCase;

final class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EncryptCookies::class);

        config()->set('services.google.client_id', 'google-client-id');
        config()->set('services.google.client_secret', 'google-client-secret');
        config()->set('services.google.redirect', 'http://127.0.0.1:8011/auth/google/callback');
    }

    public function test_login_and_register_pages_show_google_button_when_configured(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-testid="login-google-auth"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('data-testid="register-google-auth"', false);
    }

    public function test_google_button_is_hidden_when_google_auth_is_not_configured(): void
    {
        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);
        config()->set('services.google.redirect', null);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('data-testid="login-google-auth"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('data-testid="register-google-auth"', false);
    }

    public function test_google_redirect_route_redirects_to_provider(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_google_callback_creates_verified_user_and_links_social_account(): void
    {
        Event::fake([Registered::class]);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->fakeGoogleUser(
                id: 'google-123',
                email: 'google@example.com',
                name: 'Google User',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $user = User::query()->where('email', 'google@example.com')->firstOrFail();

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'google@example.com',
            'provider_name' => 'Google User',
        ]);
        Event::assertDispatched(Registered::class);
    }

    public function test_google_callback_links_existing_user_by_email_without_creating_duplicate_user(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'google@example.com',
        ]);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->fakeGoogleUser(
                id: 'google-123',
                email: 'google@example.com',
                name: 'Google User',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame(1, User::query()->count());
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
        ]);
    }

    public function test_google_callback_uses_existing_social_account_link(): void
    {
        $user = User::factory()->create([
            'email' => 'local@example.com',
        ]);

        SocialAccount::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'old@example.com',
            'provider_name' => 'Old Name',
            'provider_avatar' => 'https://example.com/old-avatar.png',
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->fakeGoogleUser(
                id: 'google-123',
                email: 'new@example.com',
                name: 'New Name',
                avatar: 'https://example.com/new-avatar.png',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'provider_email' => 'new@example.com',
            'provider_name' => 'New Name',
            'provider_avatar' => 'https://example.com/new-avatar.png',
        ]);
    }

    public function test_google_callback_attaches_existing_guest_ab_assignments(): void
    {
        $test = AbTestFactory::new()->create();
        $variant = AbTestVariantFactory::new()->create(['ab_test_id' => $test->id]);
        AbTestAssignmentFactory::new()->create([
            'ab_test_id' => $test->id,
            'ab_test_variant_id' => $variant->id,
            'visitor_id' => 'visitor-uuid',
            'user_id' => null,
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->fakeGoogleUser(
                id: 'google-123',
                email: 'google@example.com',
                name: 'Google User',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->withUnencryptedCookies([SetVisitorId::COOKIE_NAME => 'visitor-uuid'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'google@example.com')->firstOrFail();

        $this->assertDatabaseHas('ab_test_assignments', [
            'ab_test_id' => $test->id,
            'visitor_id' => 'visitor-uuid',
            'user_id' => $user->id,
        ]);
    }

    public function test_google_callback_redirects_back_to_login_when_provider_authentication_fails(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andThrow(new \RuntimeException('boom'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->from(route('login'))
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_google_routes_return_not_found_when_google_auth_is_not_configured(): void
    {
        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);
        config()->set('services.google.redirect', null);

        $this->get(route('auth.google.redirect'))->assertNotFound();
        $this->get(route('auth.google.callback'))->assertNotFound();
    }

    private function fakeGoogleUser(string $id, string $email, string $name, ?string $avatar = null): OAuthUser
    {
        return tap(new OAuthUser, function (OAuthUser $user) use ($id, $email, $name, $avatar): void {
            $user->setRaw([
                'sub' => $id,
                'email' => $email,
                'name' => $name,
                'picture' => $avatar,
            ])->map([
                'id' => $id,
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar,
            ]);
        });
    }
}
