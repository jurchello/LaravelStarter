<?php

declare(strict_types=1);

namespace Tests\Unit\Application\FeatureFlags;

use App\Application\FeatureFlags\Exceptions\FeatureFlagNotFound;
use App\Application\FeatureFlags\DeleteFeatureFlagAction;
use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Domain\FeatureFlags\Entities\FeatureFlag;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class DeleteFeatureFlagActionTest extends TestCase
{
    private FeatureFlagRepository&MockInterface $flags;
    private FeatureFlagRuntime&MockInterface $runtime;
    private DeleteFeatureFlagAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flags = Mockery::mock(FeatureFlagRepository::class);
        $this->runtime = Mockery::mock(FeatureFlagRuntime::class);
        $this->action = new DeleteFeatureFlagAction($this->flags, $this->runtime);
    }

    public function test_deletes_flag_and_purges_deleted_key(): void
    {
        $flag = new FeatureFlag(1, 'new-dashboard', 'New Dashboard', null, true, 25);

        $this->flags->shouldReceive('findById')->once()->with(1)->andReturn($flag);
        $this->flags->shouldReceive('delete')->once()->with(1);
        $this->runtime->shouldReceive('purge')->once()->with('new-dashboard');

        $this->action->execute(1);
    }

    public function test_throws_when_flag_is_missing(): void
    {
        $this->flags->shouldReceive('findById')->once()->with(1)->andReturn(null);
        $this->flags->shouldNotReceive('delete');
        $this->runtime->shouldNotReceive('purge');

        $this->expectException(FeatureFlagNotFound::class);

        $this->action->execute(1);
    }

    public function test_rethrows_repository_model_not_found_as_feature_flag_not_found(): void
    {
        $flag = new FeatureFlag(1, 'new-dashboard', 'New Dashboard', null, true, 25);

        $this->flags->shouldReceive('findById')->once()->with(1)->andReturn($flag);
        $this->flags->shouldReceive('delete')->once()->with(1)->andThrow(new ModelNotFoundException());
        $this->runtime->shouldNotReceive('purge');

        $this->expectException(FeatureFlagNotFound::class);

        $this->action->execute(1);
    }
}
