<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    /// El user_id siempre se establece mediante $user->inventories(); la cantidad solo cambia mediante
    // la lógica de compra/uso, por lo que ninguno de los dos proviene de la entrada sin procesar de la solicitud
    protected $fillable = [
        'item_id',
    ];

    protected $attributes = [
        'quantity' => 0,
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'quantity' => 'integer',
        ];
    }

    /**
     * El usuario propietario de esta fila del inventario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * El artículo del catálogo almacenado en esta fila
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
