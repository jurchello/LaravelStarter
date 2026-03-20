<?php

declare(strict_types=1);

namespace Tests\Feature\I18n;

use App\Domain\I18n\TranslationLoader;
use Tests\TestCase;

final class GetTranslationsTest extends TestCase
{
    public function test_returns_translations_for_current_locale(): void
    {
        $this->mock(TranslationLoader::class)
            ->shouldReceive('load')
            ->with('en')
            ->andReturn(['locale' => 'en', 'dictionary' => ['validation.required' => 'The field is required.']]);

        $response = $this->getJson('/api/i18n');

        $response->assertOk()
            ->assertJson([
                'locale' => 'en',
                'dictionary' => ['validation.required' => 'The field is required.'],
            ]);
    }

    public function test_endpoint_is_public(): void
    {
        $this->mock(TranslationLoader::class)
            ->shouldReceive('load')
            ->andReturn(['locale' => 'en', 'dictionary' => []]);

        $this->getJson('/api/i18n')->assertOk();
    }
}
