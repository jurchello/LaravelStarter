<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AbTestAssignment;
use App\Models\AbTestEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbTestEvent>
 */
class AbTestEventFactory extends Factory
{
    protected $model = AbTestEvent::class;

    public function definition(): array
    {
        return [
            'ab_test_assignment_id' => AbTestAssignment::factory(),
            'event' => fake()->randomElement(['signup', 'purchase', 'cta_click']),
        ];
    }
}
