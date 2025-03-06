<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeasingPlan>
 */
class LeasingPlanFactory extends Factory
{
    protected static int $counter = 1;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Leasing ' . self::$counter++,
            'max_training_session' => fake()->optional(0.7)->numberBetween(100, 500),
            'max_date' => fake()->optional(0.7)->dateTimeBetween('+1 days', '+2 days')
        ];
    }
}
