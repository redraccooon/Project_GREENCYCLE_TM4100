<?php

namespace App\Enums;

enum TreeStatus: string
{
    //
    case Active = 'ACTIVE';
    case Mature = 'MATURE';
    case Harvested = 'HARVESTED';
    case Dead = 'DEAD';

    // Solo se puede cuidar un árbol ACTIVO; los árboles MATURE, HARVESTED y DEAD ya no pueden recibir cuidados
    public function canReceiveCare(): bool
    {
        return $this === self::Active;
    }
}
