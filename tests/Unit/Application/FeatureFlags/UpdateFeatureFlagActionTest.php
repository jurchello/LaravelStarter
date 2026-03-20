<?php

declare(strict_types=1);

namespace Tests\Unit\Application\FeatureFlags;

use App\Application\FeatureFlags\Exceptions\FeatureFlagNotFound;
use App\Application\FeatureFlags\UpdateFeatureFlagAction;
use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Domain\FeatureFlags\Dto\FeatureFlagData;
use App\Domain\FeatureFlags\Entities\FeatureFlag;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateFeatureFlagActionTest extends TestCase
{
    private FeatureFlagRepository&MockInterface $flags;
    private FeatureFlagRuntime&MockInterface $runtime;
    private UpdateFeatureFlagAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flags = Mockery::mock(FeatureFlagRepository::class);
        $this->runtime = Mockery::mock(FeatureFlagRuntime::class);
        $this->action = new UpdateFeatureFlagAction($this->flags, $this->runtime);
    }

    public function test_updates_flag_and_purges_previous_and_current_keys(): void
    {
        $data = new FeatureFlagData('new-dashboard-v2', 'New Dashboard', null, true, 50);
        $before = new FeatureFlag(1, 'new-dashboard', 'New Dashboard', null, true, 25);
        $after = new FeatureFlag(1, 'new-dashboard-v2', 'New Dashboard', null, true, 50);

        $this->flags->shouldReceive('findById')->once()->with(1)->andReturn($before);
        $this->flags->shouldReceive('update')->once()->with(1, $data)->andReturn($after);
        $this->runtime->shouldReceive('purge')->once()->with(['new-dashboard', 'new-dashboard-v2']);

        self::assertSame($after, $this->action->execute(1, $data));
    }

    public function test_throws_when_flag_is_missing(): void
    {
        $data = new FeatureFlagData('new-dashboard-v2', 'New Dashboard', null, true, 50);

        $this->flags->shouldReceive('findById')->once()->with(1)->andReturn(null);
        $this->flags->shouldNotReceive('update');
        $this->runtime->shouldNotReceive('purge');

        $this->expectException(FeatureFlagNotFound::class);

        $this->action->execute(1, $data);
    }

    public function test_rethrows_repository_model_not_found_as_feature_flag_not_found(): void
    {
        $data = new FeatureFlagData('new-dashboard-v2', 'New Dashboard', null, true, 50);
        $before = new FeatureFlag(1, 'new-dashboard', 'New Dashboard', null, true, 25);

        $this->flags->shouldReceive('findById')->once()->with(1)->andReturn($before);
        $this->flags->shouldReceive('update')->once()->with(1, $data)->andThrow(new ModelNotFoundException());
        $this->runtime->shouldNotReceive('purge');

        $this->expectException(FeatureFlagNotFound::class);

        $this->action->execute(1, $data);
    }
}
