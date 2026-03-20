<?php

declare(strict_types=1);

namespace App\Domain\AccessControl\Dto;

final readonly class RoleData
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromScalars(string $name): self
    {
        return new self(name: trim($name));
    }
}
