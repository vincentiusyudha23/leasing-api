<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    protected static int $counter = 1;
    protected static int $userCounter = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => self::$userCounter++,
            'uniq_id' => 'NW-H-20-' . str_pad(self::$counter++, 5, '0', STR_PAD_LEFT)
        ];
    }
}
