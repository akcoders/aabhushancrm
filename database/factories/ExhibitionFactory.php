<?php

namespace Database\Factories;

use App\Models\Exhibition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExhibitionFactory extends Factory
{
    protected $model = Exhibition::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', '+3 months');

        return ['name' => fake()->randomElement(['Royal Jewellery Expo', 'Bridal Edit', 'Luxury Gems Showcase']).' '.fake()->city(), 'location' => fake()->city(), 'start_date' => $start, 'end_date' => (clone $start)->modify('+3 days'), 'stall_number' => 'H-'.fake()->numberBetween(1, 50), 'expense' => fake()->numberBetween(50000, 300000), 'public_token' => Str::random(32), 'status' => 'active'];
    }
}
