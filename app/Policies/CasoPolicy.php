<?php

namespace App\Policies;

use App\Models\Caso;
use App\Models\User;
use App\Support\ControleAcesso;
use App\Support\EscopoCooperativa;

class CasoPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (EscopoCooperativa::isAdmin($user)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->cooperativa_id !== null
            && ControleAcesso::usuarioTemPermissao($user, 'casos.visualizar');
    }

    public function view(User $user, Caso $caso): bool
    {
        return $user->cooperativa_id === $caso->cooperativa_id
            && ControleAcesso::usuarioTemPermissao($user, 'casos.visualizar');
    }

    public function create(User $user): bool
    {
        return $user->cooperativa_id !== null
            && ControleAcesso::usuarioTemPermissao($user, 'casos.criar');
    }

    public function update(User $user, Caso $caso): bool
    {
        return $user->cooperativa_id === $caso->cooperativa_id
            && ControleAcesso::usuarioTemPermissao($user, 'casos.editar');
    }

    public function delete(User $user, Caso $caso): bool
    {
        return $user->cooperativa_id === $caso->cooperativa_id
            && ControleAcesso::usuarioTemPermissao($user, 'casos.excluir');
    }
}
