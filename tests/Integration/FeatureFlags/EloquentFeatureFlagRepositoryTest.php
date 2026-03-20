<?php

declare(strict_types=1);

namespace Tests\Integration\FeatureFlags;

use App\Domain\FeatureFlags\ReadModels\PaginatedFeatureFlags;
use App\Domain\FeatureFlags\ValueObjects\FeatureFlagListQuery;
use App\Infrastructure\FeatureFlags\Persistence\EloquentFeatureFlagRepository;
use App\Models\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentFeatureFlagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentFeatureFlagRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentFeatureFlagRepository();
    }

    public function test_paginates_feature_flags(): void
    {
        FeatureFlag::factory()->create(['key' => 'new-dashboard', 'name' => 'New Dashboard']);
        FeatureFlag::factory()->create(['key' => 'beta-export', 'name' => 'Beta Export']);

        $result = $this->repository->paginate(FeatureFlagListQuery::fromScalars(page: 1, perPage: 50));

        $this->assertInstanceOf(PaginatedFeatureFlags::class, $result);
        $this->assertSame(2, $result->total);
    }

    public function test_filters_flags_by_search_and_status(): void
    {
        FeatureFlag::factory()->create(['key' => 'new-dashboard', 'enabled' => true]);
        FeatureFlag::factory()->create(['key' => 'legacy-widget', 'enabled' => false]);

        $result = $this->repository->paginate(
            FeatureFlagListQuery::fromScalars(page: 1, perPage: 50, search: 'new', status: 'enabled'),
        );

        $this->assertCount(1, $result->items);
        $this->assertSame('new-dashboard', $result->items[0]->key);
    }

    public function test_finds_flag_by_key(): void
    {
        FeatureFlag::factory()->create(['key' => 'new-dashboard', 'enabled' => true]);

        $result = $this->repository->findByKey('new-dashboard');

        $this->assertNotNull($result);
        $this->assertSame('new-dashboard', $result->key);
        $this->assertTrue($result->enabled);
    }
}
