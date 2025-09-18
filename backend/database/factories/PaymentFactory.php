<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentMode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_mode_id'  => PaymentMode::inRandomOrder()->value('id') ?? 1,
            'payment_mode_ref' => Str::upper(Str::random(10)),
            'paid_amount'      => $this->faker->randomFloat(2, 100, 1000),
            'balance'          => 0, // Will be updated in seeder
        ];
    }
}
