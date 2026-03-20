<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use App\Domain\User\ValueObjects\UserListQuery;
use Tests\TestCase;

final class UserListQueryTest extends TestCase
{
    public function test_normalizes_invalid_input_to_safe_defaults(): void
    {
        $query = UserListQuery::fromScalars(
            page: 0,
            perPage: 0,
            sortBy: 'unknown',
            direction: 'sideways',
            search: '   ',
            role: 'manager',
        );

        $this->assertSame(1, $query->page);
        $this->assertSame(1, $query->perPage);
        $this->assertSame('registeredAt', $query->sortBy);
        $this->assertSame('desc', $query->direction);
        $this->assertNull($query->search);
        $this->assertSame('manager', $query->role);
    }
}
