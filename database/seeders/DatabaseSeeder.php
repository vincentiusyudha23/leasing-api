<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Device;
use App\Models\LeasingPlan;
use App\Models\ActivationCode;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(5)->create();

        Device::factory(5)->create();

        ActivationCode::factory(5)->create();

        LeasingPlan::factory(5)->create();
    }
}
