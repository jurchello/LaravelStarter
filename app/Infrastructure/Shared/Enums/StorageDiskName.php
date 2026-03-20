<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Enums;

enum StorageDiskName: string
{
    case Local = 'local';
    case Public = 'public';
    case S3 = 's3';
}
