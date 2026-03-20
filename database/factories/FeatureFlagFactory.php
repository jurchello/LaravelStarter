<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FeatureFlag>
 */
final class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition(): array
    {
        $key = 'flag-'.Str::lower($this->faker->unique()->lexify('??????'));

        return [
            'key' => $key,
            'name' => Str::headline($key),
            'description' => $this->faker->sentence(),
            'enabled' => false,
            'rollout_percent' => 0,
        ];
    }
}
