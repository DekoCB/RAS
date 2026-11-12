<?php

namespace App\Policies;

use App\Models\Nota;
use App\Models\User;

class NotaPolicy
{
    public function view(User $user, Nota $nota): bool
    {
        return $user->can('nota.ver');
    }

    public function update(User $user, Nota $nota): bool
    {
        if ($nota->estado === 'cerrada') {
            return $user->can('nota.editar_fuera_plazo');
        }

        return $user->can('nota.editar');
    }
}
