<?php

namespace Database\Seeders;

use App\Models\HourlyVisit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HourlyVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(0, 23) as $hour) {
            HourlyVisit::updateOrCreate(
                ['hour' => $hour],
                ['visitors' => fake()->numberBetween(0, 60)]
            );
        }
    }
}
