<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Storage\Dto\StoredFileData;
use App\Domain\Storage\Repositories\FileStorage;
use App\Domain\Storage\ValueObjects\StoredFilePath;
use App\Infrastructure\Storage\Exceptions\StorageOperationFailed;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

final readonly class LaravelFileStorage implements FileStorage
{
    public function __construct(
        private FilesystemFactory $filesystems,
        private string $disk,
    ) {}

    public function put(StoredFilePath $path, string $contents): StoredFileData
    {
        $filesystem = $this->filesystem();
        $written = $filesystem->put((string) $path, $contents);

        if ($written !== true) {
            throw StorageOperationFailed::write();
        }

        return new StoredFileData(
            path: $path,
            disk: $this->disk,
            bytes: strlen($contents),
            url: $this->url($path),
        );
    }

    public function exists(StoredFilePath $path): bool
    {
        return $this->filesystem()->exists((string) $path);
    }

    public function read(StoredFilePath $path): string
    {
        $contents = $this->filesystem()->get((string) $path);

        if (! is_string($contents)) {
            throw StorageOperationFailed::read();
        }

        return $contents;
    }

    public function delete(StoredFilePath $path): void
    {
        $this->filesystem()->delete((string) $path);
    }

    public function url(StoredFilePath $path): ?string
    {
        $filesystem = $this->filesystem();

        try {
            return $filesystem->url((string) $path);
        } catch (\Throwable) {
            return null;
        }
    }

    private function filesystem(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return $this->filesystems->disk($this->disk);
    }
}
