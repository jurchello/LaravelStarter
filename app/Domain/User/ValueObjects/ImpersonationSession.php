<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

final readonly class ImpersonationSession
{
    public const IMPERSONATOR_ID = 'impersonator_id';
    public const IMPERSONATOR_NAME = 'impersonator_name';

    public function __construct(
        public int $impersonatorId,
        public string $impersonatorName,
    ) {}

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            self::IMPERSONATOR_ID => $this->impersonatorId,
            self::IMPERSONATOR_NAME => $this->impersonatorName,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return [
            self::IMPERSONATOR_ID,
            self::IMPERSONATOR_NAME,
        ];
    }
}
