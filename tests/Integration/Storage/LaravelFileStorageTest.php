<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use App\Domain\Storage\Repositories\FileStorage;
use App\Domain\Storage\ValueObjects\StoredFilePath;
use App\Infrastructure\Storage\LaravelFileStorage;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class LaravelFileStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_writes_reads_checks_and_deletes_files(): void
    {
        Storage::fake('storage-test');

        config()->set('storage.file_storage_disk', 'storage-test');

        $storage = new LaravelFileStorage(
            $this->app->make(FilesystemFactory::class),
            'storage-test',
        );

        $path = StoredFilePath::fromString('exports/report.txt');
        $storedFile = $storage->put($path, 'hello world');

        $this->assertSame('storage-test', $storedFile->disk);
        $this->assertSame(11, $storedFile->bytes);
        $this->assertTrue($storage->exists($path));
        $this->assertSame('hello world', $storage->read($path));

        $storage->delete($path);

        $this->assertFalse($storage->exists($path));
    }

    public function test_container_binding_returns_laravel_file_storage(): void
    {
        Storage::fake('storage-test');

        config()->set('storage.file_storage_disk', 'storage-test');

        $storage = $this->app->make(FileStorage::class);

        $this->assertInstanceOf(LaravelFileStorage::class, $storage);
    }
}
