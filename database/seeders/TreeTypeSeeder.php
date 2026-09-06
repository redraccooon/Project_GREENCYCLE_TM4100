<?php

namespace Database\Seeders;

use App\Models\TreeType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TreeType::insert([
            ['name' => 'Roble', 'base_growth_time_seconds' => 3600, 'description' => 'Crecimiento estándar.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Guanacaste', 'base_growth_time_seconds' => 7200, 'description' => 'Crecimiento lento, más resistente.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bambú', 'base_growth_time_seconds' => 1800, 'description' => 'Crecimiento rápido.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
