<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        User::create([
            'name' => 'Admin GreenCycle',
            'email' => 'admin@greencycles.com',
            'password' => Hash::make('password123'),
            'green_coins' => 500,
        ]);


        User::create([
            'name' => 'Pedro Pérez',
            'email' => 'pedro@greencycles.com',
            'password' => Hash::make('password123'),
            'green_coins' => 100,
        ]);

        // Crear 10 usuarios aleatorios usando la Factory de Laravel
        User::factory(10)->create();
    }
}