<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\I18n\TranslationLoader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

final class SiteApiExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_api_unexpected_errors_use_stable_json_contract(): void
    {
        $this->mock(TranslationLoader::class)
            ->shouldReceive('load')
            ->with('en')
            ->once()
            ->andThrow(new RuntimeException('boom'));

        $response = $this->getJson('/api/i18n');

        $response->assertStatus(500)
            ->assertExactJson([
                'message' => 'An unexpected error occurred.',
            ]);
    }
}
