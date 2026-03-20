<?php

declare(strict_types=1);

namespace Tests\Unit\Application\FeatureFlags;

use App\Application\FeatureFlags\CreateFeatureFlagAction;
use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Domain\FeatureFlags\Dto\FeatureFlagData;
use App\Domain\FeatureFlags\Entities\FeatureFlag;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateFeatureFlagActionTest extends TestCase
{
    private FeatureFlagRepository&MockInterface $flags;
    private FeatureFlagRuntime&MockInterface $runtime;
    private CreateFeatureFlagAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flags = Mockery::mock(FeatureFlagRepository::class);
        $this->runtime = Mockery::mock(FeatureFlagRuntime::class);
        $this->action = new CreateFeatureFlagAction($this->flags, $this->runtime);
    }

    public function test_creates_flag_and_purges_runtime_cache_for_created_key(): void
    {
        $data = new FeatureFlagData('new-dashboard', 'New Dashboard', null, true, 25);
        $flag = new FeatureFlag(1, 'new-dashboard', 'New Dashboard', null, true, 25);

        $this->flags->shouldReceive('create')->once()->with($data)->andReturn($flag);
        $this->runtime->shouldReceive('purge')->once()->with('new-dashboard');

        self::assertSame($flag, $this->action->execute($data));
    }
}
