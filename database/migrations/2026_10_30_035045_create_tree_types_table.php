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
        Schema::create('tree_types', function (Blueprint $table) {
            $table->id();$table->id();
            $table->string('name', 80);
            $table->string('slug', 100)->unique(); // slug es el nombre interno fijo que utiliza el backend; el nombre se puede cambiar libremente en la interfaz de usuario
            $table->text('description')->nullable(); 
            $table->unsignedTinyInteger('cares_per_level')->default(5); // Semilla básica = 5 cuidados por nivel, semilla especial = 3 
            $table->unsignedSmallInteger('harvest_reward')->default(10); // Se otorgan monedas verdes al talar un árbol MADURO 
            $table->unsignedSmallInteger('price')->default(0); // Precio en la tienda
            $table->boolean('is_active')->default(true); // Permite ocultar una especie en las nuevas plantaciones sin eliminar los árboles ya existentes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_types');
    }
};
