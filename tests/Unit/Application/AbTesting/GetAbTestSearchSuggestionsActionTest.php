<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AbTesting;

use App\Application\AbTesting\GetAbTestSearchSuggestionsAction;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetAbTestSearchSuggestionsActionTest extends TestCase
{
    private AbTestRepository&MockInterface $tests;
    private GetAbTestSearchSuggestionsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tests = Mockery::mock(AbTestRepository::class);
        $this->action = new GetAbTestSearchSuggestionsAction($this->tests);
    }

    public function test_delegates_suggestions_to_repository(): void
    {
        $this->tests->shouldReceive('suggest')
            ->with('home', 8)
            ->once()
            ->andReturn(['Homepage Hero', 'homepage-hero']);

        $this->assertSame(['Homepage Hero', 'homepage-hero'], $this->action->execute('home'));
    }
}
