<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AbTesting;

use App\Application\AbTesting\CreateAbTestAction;
use App\Application\AbTesting\CreateAbTestVariantAction;
use App\Application\AbTesting\DeleteAbTestAction;
use App\Application\AbTesting\DeleteAbTestVariantAction;
use App\Application\AbTesting\Exceptions\AbTestConfigurationInvalid;
use App\Application\AbTesting\Exceptions\AbTestNotFound;
use App\Application\AbTesting\Exceptions\AbTestVariantNotFound;
use App\Application\AbTesting\GetAbTestManagementViewAction;
use App\Application\AbTesting\UpdateAbTestAction;
use App\Application\AbTesting\UpdateAbTestStatusAction;
use App\Application\AbTesting\UpdateAbTestVariantAction;
use App\Domain\AbTesting\Dto\AbTestData;
use App\Domain\AbTesting\Dto\AbTestVariantData;
use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\ReadModels\AbTestAnalytics;
use App\Domain\AbTesting\ReadModels\AbTestManagementVariant;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\Services\AbTestActivationPolicy;
use App\Domain\AbTesting\Services\AbTestMutationPolicy;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

final class AbTestManagementActionsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AbTestRepository&MockInterface $tests;

    private AbTestMutationPolicy $mutationPolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tests = Mockery::mock(AbTestRepository::class);
        $this->mutationPolicy = new AbTestMutationPolicy;
    }

    public function test_get_management_view_returns_test(): void
    {
        $view = $this->view();
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn($view);

        $action = new GetAbTestManagementViewAction($this->tests);

        self::assertSame($view, $action->execute(10));
    }

    public function test_get_management_view_throws_when_test_is_missing(): void
    {
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(null);

        $action = new GetAbTestManagementViewAction($this->tests);

        $this->expectException(AbTestNotFound::class);

        $action->execute(10);
    }

    public function test_create_ab_test_delegates_to_repository(): void
    {
        $data = new AbTestData('Homepage Hero', 'homepage-hero', 80, AbTestDistributionMode::Manual);
        $view = $this->view();
        $this->tests->shouldReceive('createManagementView')->once()->with($data)->andReturn($view);

        $action = new CreateAbTestAction($this->tests);

        self::assertSame($view, $action->execute($data));
    }

    public function test_update_ab_test_rejects_invalid_active_configuration_before_returning(): void
    {
        $data = new AbTestData('Broken', 'broken', 0, AbTestDistributionMode::Manual);
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(status: 'active', trafficPercent: 50, variants: [$this->variant()]),
        );

        $action = new UpdateAbTestAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestConfigurationInvalid::class);

        $action->execute(10, $data);
    }

    public function test_delete_ab_test_throws_when_missing(): void
    {
        $this->tests->shouldReceive('deleteManagementView')->once()->with(10)->andReturn(false);

        $action = new DeleteAbTestAction($this->tests);

        $this->expectException(AbTestNotFound::class);

        $action->execute(10);
    }

    public function test_update_status_validates_activation_requirements(): void
    {
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(status: 'draft', variants: []),
        );

        $action = new UpdateAbTestStatusAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestConfigurationInvalid::class);

        $action->execute(10, AbTestStatus::Active);
    }

    public function test_create_variant_rejects_invalid_active_configuration(): void
    {
        $data = new AbTestVariantData('Control', 'control', 0);
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(status: 'active', variants: []),
        );

        $action = new CreateAbTestVariantAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestConfigurationInvalid::class);

        $action->execute(10, $data);
    }

    public function test_update_variant_checks_variant_existence_when_repository_returns_null(): void
    {
        $data = new AbTestVariantData('Control', 'control', 50);
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(variants: [$this->variant()]),
        );
        $this->tests->shouldReceive('updateVariant')->once()->with(10, 20, $data)->andReturn(null);

        $action = new UpdateAbTestVariantAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestVariantNotFound::class);

        $action->execute(10, 20, $data);
    }

    public function test_delete_variant_checks_variant_existence_when_repository_returns_null(): void
    {
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(variants: [$this->variant()]),
        );
        $this->tests->shouldReceive('deleteVariant')->once()->with(10, 20)->andReturn(null);

        $action = new DeleteAbTestVariantAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestVariantNotFound::class);

        $action->execute(10, 20);
    }

    public function test_update_ab_test_rejects_slug_change_after_creation(): void
    {
        $data = new AbTestData('Homepage Hero', 'renamed-slug', 100, AbTestDistributionMode::Manual);
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(status: 'active', variants: [$this->variant()], slug: 'original-slug'),
        );

        $action = new UpdateAbTestAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestConfigurationInvalid::class);
        $this->expectExceptionMessage('Test slug cannot change after creation.');

        $action->execute(10, $data);
    }

    public function test_update_variant_rejects_slug_change_after_draft(): void
    {
        $data = new AbTestVariantData('Control', 'renamed-slug', 100);
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(status: 'paused', variants: [$this->variant()]),
        );

        $action = new UpdateAbTestVariantAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestConfigurationInvalid::class);

        $action->execute(10, 20, $data);
    }

    public function test_update_status_rejects_invalid_transition_from_finished(): void
    {
        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn(
            $this->view(status: 'finished', variants: [$this->variant()]),
        );

        $action = new UpdateAbTestStatusAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        $this->expectException(AbTestConfigurationInvalid::class);

        $action->execute(10, AbTestStatus::Active);
    }

    public function test_equal_split_active_configuration_does_not_require_weights_to_total_one_hundred(): void
    {
        $data = new AbTestData('Homepage Hero', 'homepage-hero', 100, AbTestDistributionMode::Equal);
        $current = $this->view(status: 'active', trafficPercent: 100, variants: [$this->variant(33), $this->variant(33)]);
        $updated = $this->view(
            status: 'active',
            trafficPercent: 100,
            variants: [$this->variant(33), $this->variant(33)],
            distributionMode: AbTestDistributionMode::Equal,
        );

        $this->tests->shouldReceive('findManagementView')->once()->with(10)->andReturn($current);
        $this->tests->shouldReceive('updateManagementView')->once()->with(10, $data)->andReturn($updated);

        $action = new UpdateAbTestAction($this->tests, new AbTestActivationPolicy, $this->mutationPolicy);

        self::assertSame($updated, $action->execute(10, $data));
    }

    private function view(
        string $status = 'draft',
        int $trafficPercent = 50,
        array $variants = [],
        string $slug = 'homepage-hero',
        AbTestDistributionMode $distributionMode = AbTestDistributionMode::Manual,
    ): AbTestManagementView {
        return new AbTestManagementView(
            id: 10,
            name: 'Homepage Hero',
            slug: $slug,
            status: $status,
            trafficPercent: $trafficPercent,
            distributionMode: $distributionMode,
            variants: $variants,
            analytics: new AbTestAnalytics(
                assignmentsCount: 0,
                identifiedAssignmentsCount: 0,
                eventsByName: [],
            ),
            recentAssignments: [],
            recentEvents: [],
        );
    }

    private function variant(int $weight = 100): AbTestManagementVariant
    {
        return new AbTestManagementVariant(
            id: 20,
            name: 'Control',
            slug: 'control',
            weight: $weight,
            assignmentsCount: 0,
        );
    }
}
