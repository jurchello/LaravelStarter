<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AbTestAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AbTestAssignment>
 */
class AbTestAssignmentFactory extends Factory
{
    protected $model = AbTestAssignment::class;

    public function definition(): array
    {
        return [
            'ab_test_id' => AbTestFactory::new(),
            'ab_test_variant_id' => AbTestVariantFactory::new(),
            'visitor_id' => Str::uuid()->toString(),
            'user_id' => null,
        ];
    }
}