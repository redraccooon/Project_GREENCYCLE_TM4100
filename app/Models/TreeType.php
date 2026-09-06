<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreeType extends Model
{
    //
    protected $fillable = ['name', 'description', 'base_growth_time_seconds', 'image_path'];

    public function trees()
    {
        return $this->hasMany(Tree::class);
    }
}
