<?php

namespace App\Models;

use Database\Factories\TreeTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreeType extends Model
{
    /** @use HasFactory<TreeTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cares_per_level',
        'harvest_reward',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'cares_per_level' => 'integer',
            'harvest_reward' => 'integer',
            'cost' => 'integer',
        ];
    }

    // Cada árbol que se plante de esta especie
    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }
}