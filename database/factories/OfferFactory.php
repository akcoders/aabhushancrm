<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        return ['title' => fake()->randomElement(['Wedding Season Privilege', 'Golden Anniversary', 'VIP Preview', 'Festive Sparkle']), 'type' => fake()->randomElement(['Discount', 'Making Charge Off', 'Gift', 'Cashback', 'Loyalty Bonus']), 'value' => fake()->numberBetween(5, 20), 'start_date' => now()->subDays(10), 'end_date' => now()->addMonths(2), 'customer_type' => fake()->randomElement(['All', 'Premium', 'VIP', 'HNI']), 'coupon_code' => strtoupper(fake()->unique()->bothify('JEWEL##??')), 'usage_limit' => 100, 'status' => 'Active'];
    }
}
