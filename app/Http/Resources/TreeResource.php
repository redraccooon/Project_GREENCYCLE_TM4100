<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'level' => $this->level,
            'health' => $this->health,
            'progress' => $this->progress,
            'status' => $this->status->value,
            'can_care' => $this->canBeCaredNow(), // Se calcula en el servidor, por lo que la interfaz solo tiene que leerlo
            'last_care_at' => $this->last_care_at?->toIso8601ZuluString(),
            'next_care_at' => $this->next_care_at?->toIso8601ZuluString(),
            'matured_at' => $this->matured_at?->toIso8601ZuluString(),
            'tree_type' => $this->whenLoaded('treeType', fn () => [ // whenLoaded evita una consulta adicional cuando el controlador no ha cargado el tipo de forma anticipada
                'id' => $this->treeType->id,
                'name' => $this->treeType->name,
                'slug' => $this->treeType->slug,
                'cares_per_level' => $this->treeType->cares_per_level,
            ]),
            'created_at' => $this->created_at?->toIso8601ZuluString(), //toIso8601ZuluString() genera fechas con el formato: "2026-10-18T09:22:00Z"
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
