<?php

declare(strict_types=1);

namespace App\Domain\Storage\Dto;

use App\Domain\Storage\ValueObjects\StoredFilePath;

final readonly class StoredFileData
{
    public function __construct(
        public StoredFilePath $path,
        public string $disk,
        public int $bytes,
        public ?string $url,
    ) {}
}
