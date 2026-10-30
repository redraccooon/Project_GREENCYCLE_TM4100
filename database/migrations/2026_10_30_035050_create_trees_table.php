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
        Schema::create('trees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Si se elimina al usuario, también se eliminan sus árboles
            $table->foreignId('tree_type_id')->constrained()->restrictOnDelete(); // restrictOnDelete: un tipo de árbol que aún tenga árboles plantados no puede eliminarse del catálogo
            $table->string('nickname', 60)->nullable();
            $table->unsignedTinyInteger('level')->default(0);
            $table->unsignedTinyInteger('health')->default(100);
            $table->unsignedTinyInteger('progress')->default(0); // Las vidas se acumulan para el siguiente nivel; se reinician a 0 al subir de nivel
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE, MATURE, HARVESTED, DEAD.  Cadena de texto simple en lugar de una enumeración de la base de datos
            $table->timestamp('last_care_at')->nullable(); // Último cuidado manual realizado por el usuario
            $table->timestamp('next_care_at')->nullable(); // Tiempo de espera del lado del servidor: no se aceptan cuidados antes de este momento, nulo = ya se pueden brindar cuidados
            $table->timestamp('last_deterioration_check_at')->nullable(); // Reservado para la tarea de deterioro: indica el último intervalo de 24 horas que ya se ha procesado
            $table->timestamp('matured_at')->nullable();
            $table->timestamp('harvested_at')->nullable();
            $table->timestamp('died_at')->nullable();
            $table->timestamps();


            //
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trees');
    }
};
