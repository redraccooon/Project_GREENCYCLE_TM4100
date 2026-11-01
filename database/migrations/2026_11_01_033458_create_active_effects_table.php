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
        Schema::create('active_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->cascadeOnDelete(); // Si se elimina el árbol, sus efectos también desaparecen
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE, EXPIRED, CONSUMED. Cadena simple, mismo enfoque que trees.status
            $table->timestamps();

            // "Efectos actualmente activos en este árbol" (API) y "efectos que ya han caducado" (tarea programada).
            $table->index(['tree_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_effects');
    }
};
