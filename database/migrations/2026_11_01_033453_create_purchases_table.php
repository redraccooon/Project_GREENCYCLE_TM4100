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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete(); // restrictOnDelete mantiene el historial de compras en consonancia con el catálogo
            $table->integer('quantity')->default(1);
            $table->integer('unit_price'); // El precio se copió del catálogo en el momento de la compra, por lo que los cambios posteriores en el precio no modifican el historial
            $table->integer('total');
            $table->timestamps(); // created_at es la fecha de compra

            // PostgreSQL no indexa las claves externas por sí solo; esto se aplica a "mi historial de compras"
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
