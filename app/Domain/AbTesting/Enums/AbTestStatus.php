<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Enums;

enum AbTestStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case Finished = 'finished';
}