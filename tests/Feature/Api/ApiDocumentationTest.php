<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApiDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_i18n_endpoint_returns_payload(): void
    {
        $response = $this->getJson('/api/i18n');

        $response->assertOk()
            ->assertJsonStructure([
                'locale',
                'dictionary',
            ]);
    }

    public function test_verified_admin_can_view_site_api_docs_json(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('docs.site.document'));

        $response->assertOk();

        $content = $response->getContent();

        self::assertIsString($content);
        self::assertStringContainsString('/i18n', $content);
    }

    public function test_verified_admin_can_view_admin_api_docs_json(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('docs.admin.document'));

        $response->assertOk();

        $content = $response->getContent();

        self::assertIsString($content);
        self::assertStringContainsString('/users', $content);
        self::assertStringContainsString('/ab-tests', $content);
    }

    public function test_guest_cannot_view_site_api_docs(): void
    {
        $response = $this->get(route('docs.site.ui'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_admin_api_docs(): void
    {
        $response = $this->get(route('docs.admin.ui'));

        $response->assertForbidden();
    }

    public function test_verified_user_without_docs_permissions_cannot_view_docs(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('docs.site.ui'))
            ->assertForbidden();
    }
}
