<?php

declare(strict_types=1);

namespace App\Domain\Storage\Repositories;

use App\Domain\Storage\Dto\StoredFileData;
use App\Domain\Storage\ValueObjects\StoredFilePath;

interface FileStorage
{
    public function put(StoredFilePath $path, string $contents): StoredFileData;

    public function exists(StoredFilePath $path): bool;

    public function read(StoredFilePath $path): string;

    public function delete(StoredFilePath $path): void;

    public function url(StoredFilePath $path): ?string;
}
