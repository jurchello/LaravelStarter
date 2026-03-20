<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Infrastructure\Auth\Notifications\QueuedResetPasswordNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status', __(
            Password::RESET_LINK_SENT,
        ));

        Notification::assertSentTo($user, QueuedResetPasswordNotification::class, function (QueuedResetPasswordNotification $notification): bool {
            $this->assertSame('email', $notification->queue);

            return true;
        });
    }

    public function test_reset_password_request_uses_toast_payload_instead_of_inline_status_panel(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->followingRedirects()->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertSee('data-toast-payloads', false)
            ->assertDontSee('site-status-panel', false);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $token = Password::broker()->createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));

        $response->assertOk();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $token = null;

        Notification::assertSentTo($user, QueuedResetPasswordNotification::class, function (QueuedResetPasswordNotification $notification) use (&$token): bool {
            $token = $notification->token;
            $this->assertSame('email', $notification->queue);

            return true;
        });

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
