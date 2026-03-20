<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage;

use App\Domain\Storage\ValueObjects\StoredFilePath;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StoredFilePathTest extends TestCase
{
    public function test_normalizes_slashes_and_trims_outer_separators(): void
    {
        $path = StoredFilePath::fromString('/exports\\daily/report.csv/');

        $this->assertSame('exports/daily/report.csv', $path->value);
        $this->assertSame('exports/daily/report.csv', (string) $path);
    }

    public function test_rejects_empty_path(): void
    {
        $this->expectException(InvalidArgumentException::class);

        StoredFilePath::fromString(' / ');
    }

    public function test_rejects_parent_directory_traversal(): void
    {
        $this->expectException(InvalidArgumentException::class);

        StoredFilePath::fromString('../secrets.txt');
    }
}
