<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\AbTesting;

use App\Domain\AbTesting\ValueObjects\AbTestListQuery;
use Tests\TestCase;

final class AbTestListQueryTest extends TestCase
{
    public function test_normalizes_invalid_values_to_safe_defaults(): void
    {
        $query = AbTestListQuery::fromScalars(
            page: 0,
            perPage: 0,
            sortBy: 'unknown',
            direction: 'sideways',
            search: '   ',
            status: 'whatever',
        );

        $this->assertSame(1, $query->page);
        $this->assertSame(1, $query->perPage);
        $this->assertSame('name', $query->sortBy);
        $this->assertSame('asc', $query->direction);
        $this->assertNull($query->search);
        $this->assertSame('all', $query->status);
    }
}
