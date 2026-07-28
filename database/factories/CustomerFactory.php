<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return ['customer_code' => 'CUS-'.fake()->unique()->numerify('######'), 'name' => fake()->name(), 'mobile' => fake()->unique()->numerify('8#########'), 'email' => fake()->safeEmail(), 'birthday' => fake()->dateTimeBetween('-60 years', '-20 years'), 'anniversary' => fake()->optional()->dateTimeBetween('-30 years', 'now'), 'city' => fake()->randomElement(['Jaipur', 'Mumbai', 'Delhi', 'Ahmedabad', 'Surat']), 'category' => fake()->randomElement(['Normal', 'Premium', 'VIP', 'HNI']), 'product_interests' => fake()->randomElements(['Gold jewellery', 'Diamond jewellery', 'Bridal jewellery', 'Kundan'], 2)];
    }
}
