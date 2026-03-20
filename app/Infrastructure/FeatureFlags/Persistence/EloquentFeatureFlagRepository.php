<?php

declare(strict_types=1);

namespace App\Infrastructure\FeatureFlags\Persistence;

use App\Domain\FeatureFlags\Dto\FeatureFlagData;
use App\Domain\FeatureFlags\Entities\FeatureFlag as DomainFeatureFlag;
use App\Domain\FeatureFlags\ReadModels\PaginatedFeatureFlags;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use App\Domain\FeatureFlags\ValueObjects\FeatureFlagListQuery;
use App\Models\FeatureFlag;

final class EloquentFeatureFlagRepository implements FeatureFlagRepository
{
    public function paginate(FeatureFlagListQuery $query): PaginatedFeatureFlags
    {
        $sortableColumns = [
            'id' => 'id',
            'key' => 'key',
            'name' => 'name',
            'enabled' => 'enabled',
            'rolloutPercent' => 'rollout_percent',
        ];

        $column = $sortableColumns[$query->sortBy] ?? 'name';

        $paginator = FeatureFlag::query()
            ->when($query->search, function ($builder, string $term): void {
                $builder->where(function ($innerQuery) use ($term): void {
                    $like = '%'.$term.'%';

                    $innerQuery
                        ->where('key', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when($query->status === 'enabled', fn ($builder) => $builder->where('enabled', true))
            ->when($query->status === 'disabled', fn ($builder) => $builder->where('enabled', false))
            ->orderBy($column, $query->direction)
            ->when($column !== 'id', fn ($builder) => $builder->orderBy('id'))
            ->paginate($query->perPage, ['*'], 'page', $query->page);

        return new PaginatedFeatureFlags(
            items: $paginator->getCollection()
                ->map(fn (FeatureFlag $flag): DomainFeatureFlag => $this->toEntity($flag))
                ->all(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }

    public function suggestKeys(string $query, int $limit = 8): array
    {
        $term = trim($query);

        if ($term === '') {
            return [];
        }

        return FeatureFlag::query()
            ->where('key', 'like', '%'.$term.'%')
            ->orderBy('key')
            ->limit($limit)
            ->pluck('key')
            ->all();
    }

    public function findById(int $id): ?DomainFeatureFlag
    {
        $flag = FeatureFlag::query()->find($id);

        return $flag ? $this->toEntity($flag) : null;
    }

    public function findByKey(string $key): ?DomainFeatureFlag
    {
        $flag = FeatureFlag::query()->where('key', $key)->first();

        return $flag ? $this->toEntity($flag) : null;
    }

    public function create(FeatureFlagData $data): DomainFeatureFlag
    {
        $flag = FeatureFlag::query()->create($this->mapData($data));

        return $this->toEntity($flag);
    }

    public function update(int $id, FeatureFlagData $data): DomainFeatureFlag
    {
        $flag = FeatureFlag::query()->findOrFail($id);
        $flag->fill($this->mapData($data));
        $flag->save();

        return $this->toEntity($flag);
    }

    public function delete(int $id): void
    {
        FeatureFlag::query()->findOrFail($id)->delete();
    }

    /**
     * @return array{key: string, name: string, description: ?string, enabled: bool, rollout_percent: int}
     */
    private function mapData(FeatureFlagData $data): array
    {
        return [
            'key' => $data->key,
            'name' => $data->name,
            'description' => $data->description,
            'enabled' => $data->enabled,
            'rollout_percent' => $data->rolloutPercent,
        ];
    }

    private function toEntity(FeatureFlag $flag): DomainFeatureFlag
    {
        return new DomainFeatureFlag(
            id: (int) $flag->id,
            key: $flag->key,
            name: $flag->name,
            description: $flag->description,
            enabled: (bool) $flag->enabled,
            rolloutPercent: (int) $flag->rollout_percent,
        );
    }
}
