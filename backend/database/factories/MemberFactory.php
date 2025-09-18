<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        $plan = Plan::inRandomOrder()->first();

        $startDate = $this->faker->dateTimeBetween('-2 months', 'now');

        return [
            'title'           => $this->faker->randomElement(['Mr.', 'Ms.', 'Mrs.']),
            'system_user_id'  => $this->faker->unique()->numberBetween(1000, 999999),
            'first_name'      => $this->faker->firstName,
            'last_name'       => $this->faker->lastName,
            'phone_number'    => '05' . $this->faker->numerify('########'),
            'whatsapp_number' => '05' . $this->faker->numerify('########'),
            'profile_picture' => null,
        ];
    }
}
