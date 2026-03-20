<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AbTestVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbTestVariant>
 */
class AbTestVariantFactory extends Factory
{
    protected $model = AbTestVariant::class;

    public function definition(): array
    {
        return [
            'ab_test_id' => AbTestFactory::new(),
            'name' => 'Variant A',
            'slug' => 'variant-a',
            'weight' => 50,
        ];
    }
}
