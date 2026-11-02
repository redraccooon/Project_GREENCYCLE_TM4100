<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique(); // El slug es el contrato interno estable que utiliza el backend (por ejemplo, para saber qué efecto aplicar)
            $table->string('name', 80);
            $table->text('description')->nullable();
            $table->integer('cost'); // Precio de la tienda en green-coins; siempre se debe leer de aquí, nunca de la solicitud
            $table->integer('effect_duration_minutes')->nullable(); // Cuánto tiempo dura el efecto una vez aplicado a un árbol
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
