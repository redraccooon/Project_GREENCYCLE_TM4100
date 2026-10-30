<?php

namespace App\Http\Controllers\Api;

use App\Enums\TreeStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TreeResource;
use App\Models\Tree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class TreeCareController extends Controller
{
    // Aplica una acción manual al árbol indicado, siguiendo las reglas de negocio en el orden estricto siguiente: 
    // propiedad (403) → estado terminal (409) → período de espera (409) → progreso / nivel / estado
    public function store(Tree $tree): JsonResponse
    {
        // 1. Propiedad. Lanza una excepción AuthorizationException, que Laravel traduce como 403 Forbidden
        Gate::authorize('care', $tree);

        // Todo lo demás se ejecuta dentro de una transacción con la fila bloqueada, por lo que dos solicitudes simultáneas
        // (doble clic, reintento) no pueden pasar ambas la verificación de tiempo de espera y agregar progreso dos veces
        return DB::transaction(function () use ($tree) {
            $tree = Tree::query()->lockForUpdate()->findOrFail($tree->id);

            // 2. Estados finales: un árbol DEAD, MATURE o HARVESTED no puede ser cuidado
            if (! $tree->status->canReceiveCare()) {
                return response()->json([
                    'error' => 'El árbol no puede recibir cuidados en su estado actual.',
                    'code' => 'TREE_NOT_CAREABLE',
                    'status' => $tree->status->value,
                ], Response::HTTP_CONFLICT);
            }

            // 3. Tiempo de recarga, que siempre se mide según el reloj del servidor
            if ($tree->isOnCooldown()) {
                return response()->json([
                    'error' => 'El árbol se encuentra en período de enfriamiento.',
                    'code' => 'TREE_ON_COOLDOWN',
                    'next_care_at' => $tree->next_care_at->toIso8601ZuluString(),
                ], Response::HTTP_CONFLICT);
            }

            // 4. Reglas de negocio
            $this->applyCare($tree);

            return response()->json([
                'message' => 'Cuidado aplicado con éxito',
                'tree' => new TreeResource($tree->load('treeType')),
            ], Response::HTTP_OK);
        });
    }

    // Agrega progreso y salud, sube de nivel cuando se alcancen suficientes puntos de atención y reinicia el tiempo de recarga
    private function applyCare(Tree $tree): void
    {
        // Proviene del catálogo (5 para la semilla básica), nunca de la solicitud
        $caresPerLevel = $tree->treeType->cares_per_level;
        $maxLevel = config('greencycle.max_level');

        $progress = $tree->progress + 1;
        $level = $tree->level;

        if ($progress >= $caresPerLevel) {
            $level++;
            $progress = 0;
        }

        $reachedMaturity = $level >= $maxLevel;

        // forceFill omite $fillable a propósito: estas columnas solo las escribe esta
        // lógica interna del juego, nunca directamente desde una solicitud.
        $tree->forceFill([
            'progress' => $progress,
            'level' => min($level, $maxLevel),
            'health' => min($tree->health + config('greencycle.health_per_care'), config('greencycle.max_health')),
            'status' => $reachedMaturity ? TreeStatus::Mature : TreeStatus::Active,
            'matured_at' => $reachedMaturity ? now() : $tree->matured_at,
            'last_care_at' => now(),
            'next_care_at' => now()->addMinutes(config('greencycle.care_cooldown_minutes')),
        ])->save();
    }
    /**
     * Lógica de ejecución del método de cuidado de árboles (en orden):
     * 
     * 1. Autorización: Gate::authorize('care', $tree) invoca TreePolicy::care. Si falla, retorna un 403 automático.
     * 2. Estado terminal: Retorna un 409 con el código estable 'TREE_NOT_CAREABLE'.
     * 3. Cooldown: Retorna un 409 con 'next_care_at' según el ejemplo de la guía. Como ocurre antes 
     *    de applyCare, un intento anticipado no altera ningún dato.
     * 4. Aplicación de reglas (applyCare):
     *    - Incrementa +1 al progreso.
     *    - Incrementa +20 a la salud (con un tope máximo de 100).
     *    - Al completar los cuidados requeridos del nivel: sube +1 nivel y reinicia el progreso a 0.
     *    - Al alcanzar el nivel 10: cambia el estado a MATURE.
     *    - Reinicia el temporizador de cooldown.
     * 6. Configuración dinámica: Los cuidados por nivel provienen de `tree_types.cares_per_level`, 
     *    lo que permite soportar semillas especiales (3 cuidados) en el Sprint 3 sin modificar este código.
     * 7. Concurrencia (DB::transaction + lockForUpdate): Ante un doble clic, la segunda petición 
     *    espera a que la primera termine; al leer la fila, el cooldown ya está activo. 
     *    En tests con SQLite, los bloqueos se ignoran de forma segura sin causar errores.
     */
}