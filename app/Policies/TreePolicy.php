<<?php

namespace App\Policies;

use App\Models\Tree;
use App\Models\User;

class TreePolicy
{
    // Solo el propietario puede ver los detalles de un árbol
    public function view(User $user, Tree $tree): bool
    {
        return $this->owns($user, $tree);
    }

    // Solo el propietario puede cuidar un árbol. 
    // Las reglas estatales (DEAD, MATURE, cooldown) se verifican en el controlador, 
    // ya que estas responden con un código 409 en lugar de un 403.
    public function care(User $user, Tree $tree): bool
    {
        return $this->owns($user, $tree);
    }

    private function owns(User $user, Tree $tree): bool
    {
        return $user->id === $tree->user_id;
    }
}