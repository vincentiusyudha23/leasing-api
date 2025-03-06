<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivationCode>
 */
class ActivationCodeFactory extends Factory
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
            'code' => 'XB' . str_pad(self::$counter++, 5, '0', STR_PAD_LEFT)
        ];
    }
}
