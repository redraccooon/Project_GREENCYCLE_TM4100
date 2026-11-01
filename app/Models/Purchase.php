<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    // El cliente solo elige qué y cuántos. El precio_unitario y el total se calculan
    // en el servidor a partir del catálogo, por lo que se establecen con forceFill()
    protected $fillable = [
        'item_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total' => 'integer',
        ];
    }

    /**
     * El usuario que realizó la compra
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * El artículo que se compró
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
