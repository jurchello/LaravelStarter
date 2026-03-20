<?php

declare(strict_types=1);

namespace App\Domain\Storage\ValueObjects;

use InvalidArgumentException;

final readonly class StoredFilePath
{
    private function __construct(
        public string $value,
    ) {}

    public static function fromString(string $value): self
    {
        $normalized = trim($value);
        $normalized = trim(str_replace('\\', '/', $normalized), '/');
        $normalized = trim($normalized);

        if ($normalized === '') {
            throw new InvalidArgumentException('Stored file path cannot be empty.');
        }

        if (str_contains($normalized, '..')) {
            throw new InvalidArgumentException('Stored file path cannot traverse parent directories.');
        }

        return new self($normalized);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
