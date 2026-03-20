<?php

declare(strict_types=1);

namespace Tests\Feature\AdminPanel;

use App\Domain\User\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

final class AdminApiExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_api_validation_errors_use_envelope_contract(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/management/api/roles', []);

        $response->assertUnprocessable()
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta', [])
            ->assertJsonPath('errors.0', 'The name field is required.');
    }

    public function test_admin_api_not_found_errors_use_envelope_contract(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->getJson('/management/api/ab-tests/999999');

        $response->assertNotFound()
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta', [])
            ->assertJsonPath('errors.0', 'Resource not found.');
    }

    public function test_admin_api_unexpected_errors_use_envelope_contract(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->mock(UserRepository::class)
            ->shouldReceive('paginate')
            ->once()
            ->andThrow(new RuntimeException('boom'));

        $response = $this->actingAs($admin)->getJson('/management/api/users');

        $response->assertStatus(500)
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta', [])
            ->assertJsonPath('errors.0', 'An unexpected error occurred.');
    }
}
