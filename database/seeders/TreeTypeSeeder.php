<?php

namespace Database\Seeders;

use App\Models\TreeType;
use Illuminate\Database\Seeder;

class TreeTypeSeeder extends Seeder
{
    /**
     * Inicia el catálogo de árboles. La función `updateOrCreate`, con el slug como clave, permite ejecutarla más de una vez de manera segura.
     */
    public function run(): void
    {
        //
        $types = [
            [
                'slug' => 'semilla-basica',
                'name' => 'Semilla básica',
                'description' => 'Árbol estándar: 5 cuidados por nivel.',
                'cares_per_level' => 5,
                'harvest_reward' => 10,
                'price' => 0,
            ],
            [
                'slug' => 'semilla-especial',
                'name' => 'Semilla especial',
                'description' => 'Crece más rápido: 3 cuidados por nivel, pero entrega menos monedas.',
                'cares_per_level' => 3,
                'harvest_reward' => 7,
                'price' => 15,
            ],
        ];

        foreach ($types as $type) {
            TreeType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
