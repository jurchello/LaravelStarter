<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\Exceptions;

use RuntimeException;

final class StorageOperationFailed extends RuntimeException
{
    public static function write(): self
    {
        return new self('Failed to write file to storage.');
    }

    public static function read(): self
    {
        return new self('Failed to read file from storage.');
    }
}
