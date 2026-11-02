<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    // Inicia el catálogo de la tienda con los artículos de las especificaciones del proyecto. 
    // La función `updateOrCreate`, con el slug como clave, permite ejecutarla más de una vez de manera segura.
    public function run(): void
    {
        $items = [
            [
                'slug' => 'acelerador',
                'name' => 'Acelerador',
                'description' => 'Reduce a 30 minutos el cooldown de los cuidados manuales durante 2 horas.',
                'cost' => 5,
                'effect_duration_minutes' => 120,
            ],
            [
                'slug' => 'riego-automatico',
                'name' => 'Riego automático',
                'description' => 'Aplica cuidados automáticos durante 6 horas.',
                'cost' => 8,
                'effect_duration_minutes' => 360,
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
