<?php

namespace Database\Factories;

use App\Enums\TreeStatus;
use App\Models\Tree;
use App\Models\TreeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tree>
 */
class TreeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tree_type_id' => TreeType::factory(),
            'nickname' => fake()->optional()->firstName(),
            'level' => 0,
            'health' => 100,
            'progress' => 0,
            'status' => TreeStatus::Active,
            'next_care_at' => null,
        ];
    }

    // Un árbol cuya salud ya había llegado a 0
    public function dead(): static
    {
        return $this->state(fn () => [
            'health' => 0,
            'status' => TreeStatus::Dead,
            'died_at' => now(),
        ]);
    }

    // Un árbol que ya alcanzó el nivel máximo
    public function mature(): static
    {
        return $this->state(fn () => [
            'level' => 10,
            'progress' => 0,
            'status' => TreeStatus::Mature,
            'matured_at' => now(),
        ]);
    }
}
