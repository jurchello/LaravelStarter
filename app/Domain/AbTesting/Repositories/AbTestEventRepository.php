<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Repositories;

use App\Domain\AbTesting\Dto\AbTestEventDto;

interface AbTestEventRepository
{
    public function record(AbTestEventDto $dto): void;
}
