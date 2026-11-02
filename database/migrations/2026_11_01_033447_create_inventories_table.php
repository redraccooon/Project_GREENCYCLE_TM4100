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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Si se elimina al usuario, también se elimina su inventario
            $table->foreignId('item_id')->constrained()->restrictOnDelete(); // restrictOnDelete: un artículo que aún pertenezca a alguien no puede eliminarse del catálogo
            $table->integer('quantity')->default(0);
            $table->timestamps();

            // Una fila por usuario y artículo: al volver a comprar, se incrementa la cantidad en lugar de agregar una nueva fila
            // También funciona como índice para las consultas de "mi inventario", ya que el user_id aparece primero
            $table->unique(['user_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
