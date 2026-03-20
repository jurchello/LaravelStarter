<?php

declare(strict_types=1);

namespace Tests\Feature\FeatureFlags;

use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Http\Middleware\SetVisitorId;
use App\Models\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Pennant\Feature;
use Tests\TestCase;

final class PennantScopeResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/feature-flags/pennant-scope-probe', function () {
            return response()->json([
                'cookie' => request()->cookie(SetVisitorId::COOKIE_NAME),
                'active' => Feature::active('guest-rollout'),
            ]);
        });
    }

    public function test_guest_feature_checks_use_visitor_id_cookie_as_pennant_scope(): void
    {
        FeatureFlag::factory()->create([
            'key' => 'guest-rollout',
            'enabled' => true,
            'rollout_percent' => 100,
        ]);

        $this->app->make(FeatureFlagRuntime::class)->purge('guest-rollout');

        $response = $this->withUnencryptedCookies([
            SetVisitorId::COOKIE_NAME => 'visitor-123',
        ])->getJson('/feature-flags/pennant-scope-probe');

        $response->assertOk()
            ->assertJsonPath('active', true);

        $visitorId = $response->json('cookie');

        self::assertIsString($visitorId);
        self::assertNotSame('', $visitorId);

        $this->assertDatabaseHas('features', [
            'name' => 'guest-rollout',
            'scope' => $visitorId,
            'value' => 'true',
        ]);

        $this->assertDatabaseMissing('features', [
            'name' => 'guest-rollout',
            'scope' => '__laravel_null',
        ]);
    }
}
