<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //
    protected $fillable = ['name', 'description', 'effect_type', 'price'];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
