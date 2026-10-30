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

        // One fresh tree and one almost mature tree, handy for demoing the MATURE transition.
        Tree::factory()->for($demo)->for($basic)->create(['nickname' => 'Robles']);
        Tree::factory()->for($demo)->for($basic)->create([
            'nickname' => 'Casi maduro',
            'level' => 9,
            'progress' => 4,
        ]);
    }
}
