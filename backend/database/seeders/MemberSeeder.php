<?php
namespace Database\Seeders;

use App\Models\Member;
use App\Models\PaymentMode;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure at least one payment mode exists
        if (! PaymentMode::exists()) {
            PaymentMode::create(['name' => 'Cash']);
        }

        // Seed 50 members
        Member::factory()
            ->count(50)
            ->afterCreating(function ($member) {
                // $plan = $member->plan;

                // if (! $plan) {
                //     return;
                // }

                // $planPrice = $plan->price ?? 1000;

                //                                                                      // Apply a fake discount
                // $discount      = fake()->numberBetween(0, (int) ($planPrice * 0.3)); // up to 30% discount
                // $afterDiscount = $planPrice - $discount;

                // // Generate a paid amount up to the discounted price
                // $paidAmount = fake()->numberBetween(0, $afterDiscount);

                // $member->payments()->create([
                //     'payment_mode_id'  => PaymentMode::inRandomOrder()->value('id'),
                //     'payment_mode_ref' => Str::upper(Str::random(10)),
                //     'total'            => $planPrice,
                //     'after_discount'   => $afterDiscount,
                //     'paid_amount'      => $paidAmount,
                //     'balance'          => $afterDiscount - $paidAmount,
                //     'plan_id'          => $plan->id,
                // ]);
            })
            ->create();
    }
}
