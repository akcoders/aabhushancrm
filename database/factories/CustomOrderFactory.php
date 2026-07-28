<?php

namespace Database\Factories;

use App\Models\CustomOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomOrderFactory extends Factory
{
    protected $model = CustomOrder::class;

    public function definition(): array
    {
        return ['order_number' => 'ORD-'.fake()->unique()->numerify('######'), 'jewellery_type' => fake()->randomElement(['Necklace', 'Ring', 'Bangles', 'Pendant', 'Bridal Set']), 'metal_type' => fake()->randomElement(['Gold', 'Rose Gold', 'Platinum']), 'purity' => fake()->randomElement(['18K', '22K', '24K']), 'approx_weight' => fake()->randomFloat(3, 4, 90), 'estimated_amount' => fake()->numberBetween(80000, 900000), 'advance_payment' => fake()->numberBetween(10000, 100000), 'due_date' => fake()->dateTimeBetween('now', '+90 days'), 'status' => fake()->randomElement(['New', 'Designing', 'Approved', 'In Production', 'Ready', 'Delivered']), 'approval_status' => fake()->randomElement(['Pending', 'Approved'])];
    }
}
