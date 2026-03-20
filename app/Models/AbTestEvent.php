<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbTestEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['ab_test_assignment_id', 'event'];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AbTestAssignment::class, 'ab_test_assignment_id');
    }
}