<?php

declare(strict_types=1);

namespace App\Domain\AbTesting\Enums;

enum AbTestDistributionMode: string
{
    case Manual = 'manual';
    case Equal = 'equal';
}
