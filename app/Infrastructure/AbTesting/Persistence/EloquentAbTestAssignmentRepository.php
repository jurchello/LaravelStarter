<?php

declare(strict_types=1);

namespace App\Infrastructure\AbTesting\Persistence;

use App\Domain\AbTesting\Dto\AbTestAssignmentDto;
use App\Domain\AbTesting\Entities\AbTestAssignment;
use App\Domain\AbTesting\Entities\AbTestVariant;
use App\Domain\AbTesting\Repositories\AbTestAssignmentRepository;
use App\Models\AbTestAssignment as AbTestAssignmentModel;

final class EloquentAbTestAssignmentRepository implements AbTestAssignmentRepository
{
    public function findByTestAndVisitor(int $abTestId, string $visitorId): ?AbTestAssignment
    {
        $model = AbTestAssignmentModel::with('variant')
            ->where('ab_test_id', $abTestId)
            ->where('visitor_id', $visitorId)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByTestAndUser(int $abTestId, int $userId): ?AbTestAssignment
    {
        $model = AbTestAssignmentModel::with('variant')
            ->where('ab_test_id', $abTestId)
            ->where('user_id', $userId)
            ->oldest('created_at')
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function create(AbTestAssignmentDto $dto): AbTestAssignment
    {
        $model = AbTestAssignmentModel::create([
            'ab_test_id' => $dto->abTestId,
            'ab_test_variant_id' => $dto->abTestVariantId,
            'visitor_id' => $dto->visitorId,
            'user_id' => $dto->userId,
        ]);

        $model->load('variant');

        return $this->toEntity($model);
    }

    public function attachUser(string $visitorId, int $userId): void
    {
        AbTestAssignmentModel::where('visitor_id', $visitorId)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }

    private function toEntity(AbTestAssignmentModel $model): AbTestAssignment
    {
        return new AbTestAssignment(
            id: $model->id,
            abTestId: $model->ab_test_id,
            abTestVariantId: $model->ab_test_variant_id,
            visitorId: $model->visitor_id,
            userId: $model->user_id,
            variant: new AbTestVariant(
                id: $model->variant->id,
                slug: $model->variant->slug,
                weight: $model->variant->weight,
            ),
        );
    }
}
