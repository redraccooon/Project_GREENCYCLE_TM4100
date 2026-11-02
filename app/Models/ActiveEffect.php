<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveEffect extends Model
{
    // starts_at, expires_at and status se calculan en el servidor cuando se utiliza el elemento
    protected $fillable = [
        'item_id',
    ];

    protected $attributes = [
        'status' => 'ACTIVE',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * El árbol al que se aplica este efecto
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * El objeto que provocó este efecto
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
