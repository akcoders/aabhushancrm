<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return ['name' => fake()->name(), 'mobile' => fake()->unique()->numerify('9#########'), 'email' => fake()->unique()->safeEmail(), 'source' => fake()->randomElement(['Walk-in', 'Instagram', 'Facebook', 'WhatsApp', 'Website', 'Referral', 'Exhibition', 'Event', 'Phone Call']), 'status' => fake()->randomElement(['New', 'Contacted', 'Interested', 'Follow-up', 'Converted', 'Lost']), 'priority' => fake()->randomElement(['Hot', 'Warm', 'Cold']), 'budget_min' => fake()->numberBetween(25000, 100000), 'budget_max' => fake()->numberBetween(150000, 800000), 'occasion' => fake()->randomElement(['Wedding', 'Anniversary', 'Festival', 'Gift']), 'product_interests' => fake()->randomElements(['Bridal jewellery', 'Gold jewellery', 'Diamond jewellery', 'Polki', 'Kundan', 'Custom design'], 2), 'tags' => fake()->randomElements(['Bridal', 'High intent', 'Repeat visitor', 'Festive'], 2)];
    }
}
