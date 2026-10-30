<?php

namespace Database\Factories;

use App\Models\TreeType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TreeType>
 */
class TreeTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'cares_per_level' => 5,
            'harvest_reward' => 10,
            'price' => 0,
            'is_active' => true,
        ];
    }
}
