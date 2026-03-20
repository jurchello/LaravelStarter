<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Repositories;

use App\Domain\AbTesting\Entities\AbTest;
use App\Domain\AbTesting\ReadModels\PaginatedAbTestAssignments;
use App\Domain\AbTesting\ReadModels\PaginatedAbTestEvents;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Domain\AbTesting\ReadModels\PaginatedAbTests;
use App\Domain\AbTesting\Dto\AbTestData;
use App\Domain\AbTesting\Dto\AbTestVariantData;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Domain\AbTesting\ValueObjects\AbTestListQuery;

interface AbTestRepository
{
    public function findActiveBySlug(string $slug): ?AbTest;

    public function findManagementView(int $id): ?AbTestManagementView;

    public function paginateAssignments(int $abTestId, int $page = 1, int $perPage = 50): ?PaginatedAbTestAssignments;

    public function paginateEvents(int $abTestId, int $page = 1, int $perPage = 50): ?PaginatedAbTestEvents;

    public function paginate(AbTestListQuery $query): PaginatedAbTests;

    public function createManagementView(AbTestData $data): AbTestManagementView;

    public function updateManagementView(int $id, AbTestData $data): ?AbTestManagementView;

    public function deleteManagementView(int $id): bool;

    public function updateStatus(int $id, AbTestStatus $status): ?AbTestManagementView;

    public function createVariant(int $abTestId, AbTestVariantData $data): ?AbTestManagementView;

    public function updateVariant(int $abTestId, int $variantId, AbTestVariantData $data): ?AbTestManagementView;

    public function deleteVariant(int $abTestId, int $variantId): ?AbTestManagementView;

    /**
     * @return array<int, string>
     */
    public function suggest(string $query, int $limit = 8): array;
}
