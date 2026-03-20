<?php

declare(strict_types=1);

namespace App\Application\AbTesting;

use App\Domain\AbTesting\Repositories\AbTestRepository;

final readonly class GetAbTestSearchSuggestionsAction
{
    public function __construct(
        private AbTestRepository $tests,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(string $query, int $limit = 8): array
    {
        return $this->tests->suggest($query, $limit);
    }
}
