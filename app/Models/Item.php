<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    //
    protected $fillable = [
        'slug',
        'name',
        'description',
        'cost',
        'effect_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'effect_duration_minutes' => 'integer',
        ];
    }

    /**
     * Cada fila del inventario que contenga este artículo
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Cada compra de este artículo
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Todos los efectos que este objeto ha aplicado a un árbol
     */
    public function activeEffects(): HasMany
    {
        return $this->hasMany(ActiveEffect::class);
    }
}
