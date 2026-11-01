<?php

namespace Database\Seeders;

use App\Models\Tree;
use App\Models\TreeType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(TreeTypeSeeder::class);

        // Cuenta de demostración para la presentación (contraseña: «password» de UserFactory)
        $demo = User::factory()->create([
            'name' => 'Demo GreenCycle',
            'email' => 'demo@greencycle.test',
        ]);

        $basic = TreeType::where('slug', 'semilla-basica')->first();

        // Tree #1: recién plantadas
        Tree::factory()->for($demo)->for($basic)->create();

        // Tree #2: A un paso de MATURE, ideal para mostrar la transición
        Tree::factory()->for($demo)->for($basic)->create([
            'level' => 9,
            'progress' => 4,
        ]);
    }
}
