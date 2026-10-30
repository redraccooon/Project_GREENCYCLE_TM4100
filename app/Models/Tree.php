<?php

namespace App\Models;

use App\Enums\TreeStatus;
use Database\Factories\TreeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeType extends Model
{
    /** @use HasFactory<\Database\Factories\TreeTypeFactory> */
    use HasFactory;

    // Solo lo que el cliente puede elegir al plantar. El nivel, la salud, el progreso, 
    // el estado y cada marca de tiempo forman parte del estado del juego: son establecidos 
    // por la lógica del servidor mediante `forceFill()`, nunca a partir de una solicitud
    protected $fillable = [
        'tree_type_id',
        'nickname',
    ];

    // Refleja los valores predeterminados de la base de datos para que una instancia recién creada ya los tenga en PHP
    protected $attributes = [
        'level' => 0,
        'health' => 100,
        'progress' => 0,
        'status' => 'ACTIVE',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'level' => 'integer',
            'health' => 'integer',
            'progress' => 'integer',
            'status' => TreeStatus::class,
            'last_care_at' => 'datetime',
            'next_care_at' => 'datetime',
            'last_deterioration_check_at' => 'datetime',
            'matured_at' => 'datetime',
            'harvested_at' => 'datetime',
            'died_at' => 'datetime',
        ];
    }

    // El user propietario de este árbol
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // El tipo de catálogo del que se extrajo este árbol
    public function treeType(): BelongsTo
    {
        return $this->belongsTo(TreeType::class);
    }

    // Es cierto mientras el tiempo de espera del lado del servidor siga en curso
    public function isOnCooldown(): bool
    {
        return $this->next_care_at !== null && now()->lt($this->next_care_at);
    }

    // Indicador predefinido para el frontend, para que nunca tenga que volver a compilar las reglas por sí mismo
    public function canBeCaredNow(): bool
    {
        return $this->status->canReceiveCare() && ! $this->isOnCooldown();
    }
}
