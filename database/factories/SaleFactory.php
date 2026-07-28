<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(45000, 650000);

        return ['invoice_number' => 'INV-'.fake()->unique()->numerify('######'), 'sale_date' => fake()->dateTimeBetween('-10 months', 'now'), 'subtotal' => $total, 'tax' => round($total * .03, 2), 'final_amount' => round($total * 1.03, 2), 'paid_amount' => round($total * 1.03, 2), 'payment_status' => 'Paid', 'commission_amount' => round($total * .01, 2)];
    }
}
