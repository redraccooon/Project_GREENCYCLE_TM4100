<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    //
    // $guarded (no $fillable) en blanco de campos internos: aquí declaramos
    // explícitamente que TODO debe pasar por asignación manual o por
    // StoreTreeRequest::validated(), nunca por Model::create($request->all()).
    protected $guarded = ['id', 'status', 'level', 'health', 'last_care_at', 'last_deterioration_check_at'];

    protected function casts(): array
    {
        return [
            'planted_at' => 'datetime',
            'last_care_at' => 'datetime',
            'last_deterioration_check_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treeType()
    {
        return $this->belongsTo(TreeType::class);
    }
}
