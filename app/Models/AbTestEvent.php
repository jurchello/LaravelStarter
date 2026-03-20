<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $event
 * @property Carbon $created_at
 * @property AbTestAssignment $assignment
 */
class AbTestEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['ab_test_assignment_id', 'event'];

    /** @return BelongsTo<AbTestAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AbTestAssignment::class, 'ab_test_assignment_id');
    }
}
