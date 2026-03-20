<?php

declare(strict_types=1);

namespace App\Infrastructure\AbTesting\Persistence;

use App\Domain\AbTesting\Dto\AbTestData;
use App\Domain\AbTesting\Dto\AbTestVariantData;
use App\Domain\AbTesting\Entities\AbTest;
use App\Domain\AbTesting\Entities\AbTestVariant;
use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\ReadModels\AbTestAnalytics;
use App\Domain\AbTesting\ReadModels\AbTestAssignmentListItem;
use App\Domain\AbTesting\ReadModels\AbTestEventListItem;
use App\Domain\AbTesting\ReadModels\AbTestListItem;
use App\Domain\AbTesting\ReadModels\AbTestManagementVariant;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\ReadModels\AbTestRecentAssignment;
use App\Domain\AbTesting\ReadModels\AbTestRecentEvent;
use App\Domain\AbTesting\ReadModels\PaginatedAbTestAssignments;
use App\Domain\AbTesting\ReadModels\PaginatedAbTestEvents;
use App\Domain\AbTesting\ReadModels\PaginatedAbTests;
use App\Domain\AbTesting\Repositories\AbTestRepository;
use App\Domain\AbTesting\ValueObjects\AbTestListQuery;
use App\Models\AbTest as AbTestModel;
use App\Models\AbTestAssignment;
use App\Models\AbTestEvent;
use App\Models\AbTestVariant as AbTestVariantModel;
use Illuminate\Support\Collection;

final class EloquentAbTestRepository implements AbTestRepository
{
    public function findActiveBySlug(string $slug): ?AbTest
    {
        $model = AbTestModel::with('variants')
            ->where('slug', $slug)
            ->where('status', AbTestStatus::Active)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findManagementView(int $id): ?AbTestManagementView
    {
        $model = AbTestModel::query()
            ->with([
                'variants' => fn ($query) => $query->orderByDesc('weight')->orderBy('id'),
                'assignments.variant',
                'assignments.events',
            ])
            ->find($id);

        if ($model === null) {
            return null;
        }

        return $this->toManagementView($model);
    }

    public function paginateAssignments(int $abTestId, int $page = 1, int $perPage = 50): ?PaginatedAbTestAssignments
    {
        if (! AbTestModel::query()->whereKey($abTestId)->exists()) {
            return null;
        }

        $paginator = AbTestAssignment::query()
            ->with('variant')
            ->where('ab_test_id', $abTestId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return new PaginatedAbTestAssignments(
            items: $paginator->getCollection()
                ->map(static fn (AbTestAssignment $assignment): AbTestAssignmentListItem => new AbTestAssignmentListItem(
                    id: $assignment->id,
                    visitorId: $assignment->visitor_id,
                    userId: $assignment->user_id,
                    variantName: $assignment->variant->name,
                    variantSlug: $assignment->variant->slug,
                    createdAt: $assignment->created_at->toIso8601String(),
                ))
                ->all(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }

    public function paginateEvents(int $abTestId, int $page = 1, int $perPage = 50): ?PaginatedAbTestEvents
    {
        if (! AbTestModel::query()->whereKey($abTestId)->exists()) {
            return null;
        }

        $paginator = AbTestEvent::query()
            ->with('assignment.variant')
            ->whereHas('assignment', static fn ($query) => $query->where('ab_test_id', $abTestId))
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return new PaginatedAbTestEvents(
            items: $paginator->getCollection()
                ->map(static fn (AbTestEvent $event): AbTestEventListItem => new AbTestEventListItem(
                    id: $event->id,
                    event: $event->event,
                    variantName: $event->assignment->variant->name,
                    variantSlug: $event->assignment->variant->slug,
                    visitorId: $event->assignment->visitor_id,
                    createdAt: $event->created_at->toIso8601String(),
                ))
                ->all(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }

    public function paginate(AbTestListQuery $query): PaginatedAbTests
    {
        $sortableColumns = [
            'name' => 'name',
            'slug' => 'slug',
            'status' => 'status',
            'trafficPercent' => 'traffic_percent',
            'variantsCount' => 'variants_count',
        ];

        $column = $sortableColumns[$query->sortBy] ?? $sortableColumns['name'];

        $paginator = AbTestModel::query()
            ->withCount('variants')
            ->when($query->search, function ($builder, string $term): void {
                $builder->where(function ($innerQuery) use ($term): void {
                    $like = '%'.$term.'%';

                    $innerQuery
                        ->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                });
            })
            ->when($query->status !== 'all', fn ($builder) => $builder->where('status', $query->status))
            ->orderBy($column, $query->direction)
            ->orderBy('id')
            ->paginate($query->perPage, ['*'], 'page', $query->page);

        return new PaginatedAbTests(
            items: $paginator->getCollection()
                ->map(static fn (AbTestModel $test): AbTestListItem => new AbTestListItem(
                    id: $test->id,
                    name: $test->name,
                    slug: $test->slug,
                    status: $test->status->value,
                    trafficPercent: $test->traffic_percent,
                    variantsCount: $test->variants_count,
                ))
                ->all(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }

    public function createManagementView(AbTestData $data): AbTestManagementView
    {
        $model = AbTestModel::query()->create($this->mapAbTestData($data) + [
            'status' => AbTestStatus::Draft->value,
        ]);

        return $this->findManagementViewOrFail($model->id);
    }

    public function updateManagementView(int $id, AbTestData $data): ?AbTestManagementView
    {
        $model = AbTestModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        $model->fill($this->mapAbTestData($data));
        $model->save();

        return $this->findManagementViewOrFail($id);
    }

    public function deleteManagementView(int $id): bool
    {
        $model = AbTestModel::query()->find($id);

        if ($model === null) {
            return false;
        }

        $model->delete();

        return true;
    }

    public function updateStatus(int $id, AbTestStatus $status): ?AbTestManagementView
    {
        $model = AbTestModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        $model->status = $status;
        $model->save();

        return $this->findManagementViewOrFail($id);
    }

    public function createVariant(int $abTestId, AbTestVariantData $data): ?AbTestManagementView
    {
        $model = AbTestModel::query()->find($abTestId);

        if ($model === null) {
            return null;
        }

        $model->variants()->create($this->mapVariantData($data));

        return $this->findManagementViewOrFail($abTestId);
    }

    public function updateVariant(int $abTestId, int $variantId, AbTestVariantData $data): ?AbTestManagementView
    {
        $variant = AbTestVariantModel::query()
            ->where('ab_test_id', $abTestId)
            ->find($variantId);

        if ($variant === null) {
            return null;
        }

        $variant->fill($this->mapVariantData($data));
        $variant->save();

        return $this->findManagementViewOrFail($abTestId);
    }

    public function deleteVariant(int $abTestId, int $variantId): ?AbTestManagementView
    {
        $variant = AbTestVariantModel::query()
            ->where('ab_test_id', $abTestId)
            ->find($variantId);

        if ($variant === null) {
            return null;
        }

        $variant->delete();

        return $this->findManagementViewOrFail($abTestId);
    }

    /**
     * @return array{name: string, slug: string, traffic_percent: int, distribution_mode: string}
     */
    private function mapAbTestData(AbTestData $data): array
    {
        return [
            'name' => $data->name,
            'slug' => $data->slug,
            'traffic_percent' => $data->trafficPercent,
            'distribution_mode' => $data->distributionMode->value,
        ];
    }

    /**
     * @return array{name: string, slug: string, weight: int}
     */
    private function mapVariantData(AbTestVariantData $data): array
    {
        return [
            'name' => $data->name,
            'slug' => $data->slug,
            'weight' => $data->weight,
        ];
    }

    public function suggest(string $query, int $limit = 8): array
    {
        $term = trim($query);

        if ($term === '') {
            return [];
        }

        $like = '%'.$term.'%';

        return AbTestModel::query()
            ->where(function ($builder) use ($like): void {
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->flatMap(static fn (AbTestModel $test): array => [$test->name, $test->slug])
            ->unique()
            ->values()
            ->all();
    }

    private function findManagementViewOrFail(int $id): AbTestManagementView
    {
        /** @var AbTestManagementView $view */
        $view = $this->findManagementView($id);

        return $view;
    }

    private function toEntity(AbTestModel $model): AbTest
    {
        return new AbTest(
            id: $model->id,
            slug: $model->slug,
            trafficPercent: $model->traffic_percent,
            distributionMode: $model->distribution_mode ?? AbTestDistributionMode::Manual,
            variants: $model->variants
                ->map(fn (AbTestVariantModel $variant): AbTestVariant => new AbTestVariant(
                    id: $variant->id,
                    slug: $variant->slug,
                    weight: $variant->weight,
                ))
                ->all(),
        );
    }

    private function toManagementView(AbTestModel $model): AbTestManagementView
    {
        /** @var Collection<int, AbTestAssignment> $assignments */
        $assignments = $model->assignments;
        /** @var Collection<int, AbTestVariantModel> $variants */
        $variants = $model->variants;

        $assignmentsByVariantId = $assignments
            ->groupBy('ab_test_variant_id')
            ->map(static fn (Collection $items): int => $items->count());

        $eventsByName = $assignments
            ->flatMap(static fn (AbTestAssignment $assignment): Collection => $assignment->events)
            ->groupBy('event')
            ->map(static fn (Collection $items): int => $items->count())
            ->sortKeys()
            ->all();

        $recentAssignments = $assignments
            ->sortByDesc('created_at')
            ->take(10)
            ->map(static fn (AbTestAssignment $assignment): AbTestRecentAssignment => new AbTestRecentAssignment(
                id: $assignment->id,
                visitorId: $assignment->visitor_id,
                userId: $assignment->user_id,
                variantName: $assignment->variant->name,
                variantSlug: $assignment->variant->slug,
                createdAt: $assignment->created_at->toIso8601String(),
            ))
            ->values()
            ->all();

        $recentEvents = $assignments
            ->flatMap(static fn (AbTestAssignment $assignment): Collection => $assignment->events->map(
                static fn (AbTestEvent $event): array => [$assignment, $event],
            ))
            ->sortByDesc(static fn (array $pair): string => (string) $pair[1]->created_at)
            ->take(10)
            ->map(static function (array $pair): AbTestRecentEvent {
                /** @var AbTestAssignment $assignment */
                $assignment = $pair[0];
                /** @var AbTestEvent $event */
                $event = $pair[1];

                return new AbTestRecentEvent(
                    id: $event->id,
                    event: $event->event,
                    variantName: $assignment->variant->name,
                    variantSlug: $assignment->variant->slug,
                    visitorId: $assignment->visitor_id,
                    createdAt: $event->created_at->toIso8601String(),
                );
            })
            ->values()
            ->all();

        return new AbTestManagementView(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            status: $model->status->value,
            trafficPercent: $model->traffic_percent,
            distributionMode: $model->distribution_mode ?? AbTestDistributionMode::Manual,
            variants: $variants
                ->map(static fn (AbTestVariantModel $variant): AbTestManagementVariant => new AbTestManagementVariant(
                    id: $variant->id,
                    name: $variant->name,
                    slug: $variant->slug,
                    weight: $variant->weight,
                    assignmentsCount: $assignmentsByVariantId->get($variant->id, 0),
                ))
                ->values()
                ->all(),
            analytics: new AbTestAnalytics(
                assignmentsCount: $assignments->count(),
                identifiedAssignmentsCount: $assignments->filter(
                    static fn (AbTestAssignment $assignment): bool => $assignment->user_id !== null,
                )->count(),
                eventsByName: $eventsByName,
            ),
            recentAssignments: $recentAssignments,
            recentEvents: $recentEvents,
        );
    }
}
