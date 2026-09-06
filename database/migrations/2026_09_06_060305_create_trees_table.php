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

            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Dueño del árbol. onDelete cascade: si se borra el usuario de prueba, no quedan árboles huérfanos.

            $table->foreignId('tree_type_id')->constrained()->restrictOnDelete(); // Especie. restrict: no se puede borrar un tree_type si hay árboles activos de esa especie.

            $table->string('nickname'); // único dato "cosmético" que el cliente puede definir, me lo recomendo la IA y me parecio divertido ponerle nombre a los arboles.

            $table->enum('status', ['ACTIVE', 'MATURE', 'DEAD', 'HARVESTED'])->default('ACTIVE');
            // Máquina de estados explícita. Se prefiere ENUM sobre boolean(is_dead) porque hay más de dos estados posibles 
            // y el ENUM documenta el dominio directamente en el esquema.

            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedTinyInteger('health')->default(100);
            $table->timestamp('planted_at')->useCurrent();

            $table->timestamp('last_care_at')->nullable();
             // Cooldown de cuidado manual (regado, abonado, etc.). Nullable: un árbol recién plantado nunca ha sido cuidado. 
             // Lo actualiza el usuario vía API, NUNCA se acepta este valor directamente del body de la request.

            $table->timestamp('last_deterioration_check_at')->useCurrent();
            // Última vez que el Job programado (Scheduler) evaluó deterioro.
            // useCurrent() para que el primer chequeo tenga una base consistente y el Job pueda calcular tiempo 
            // transcurrido eventos distintos de tiempo sin null-checks constantes.

            $table->timestamps();
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
