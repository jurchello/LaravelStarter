<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'enabled',
        'rollout_percent',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'rollout_percent' => 'integer',
        ];
    }
}
