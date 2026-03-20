<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Models\AbTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbTest>
 */
class AbTestFactory extends Factory
{
    protected $model = AbTest::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => fake()->unique()->slug(2),
            'status' => AbTestStatus::Active,
            'traffic_percent' => 100,
            'distribution_mode' => AbTestDistributionMode::Manual,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AbTestStatus::Draft,
        ]);
    }
}
