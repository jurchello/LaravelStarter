<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AbTesting;

use App\Application\AbTesting\GetAbTestAudienceEstimateAction;
use App\Domain\User\Repositories\UserRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetAbTestAudienceEstimateActionTest extends TestCase
{
    private UserRepository&MockInterface $users;

    private GetAbTestAudienceEstimateAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = Mockery::mock(UserRepository::class);
        $this->action = new GetAbTestAudienceEstimateAction($this->users);
    }

    public function test_calculates_estimated_people_from_registered_user_count(): void
    {
        $this->users->shouldReceive('countAudience')
            ->once()
            ->andReturn(120);

        $this->assertSame([
            'audienceSize' => 120,
            'trafficPercent' => 30,
            'estimatedPeople' => 36,
        ], $this->action->execute(30));
    }
}
