<?php

namespace App\Policies;

use App\Models\AndamentoCaso;
use App\Models\Caso;
use App\Models\User;
use App\Support\ControleAcesso;
use App\Support\EscopoCooperativa;

class AndamentoCasoPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (EscopoCooperativa::isAdmin($user)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user, Caso $caso): bool
    {
        return EscopoCooperativa::temCooperativa($user)
            && EscopoCooperativa::usuarioPertenceCooperativa($user, (int) $caso->cooperativa_id)
            && ControleAcesso::usuarioTemPermissao($user, 'andamentos.visualizar');
    }

    public function view(User $user, AndamentoCaso $andamentoCaso): bool
    {
        return EscopoCooperativa::temCooperativa($user)
            && EscopoCooperativa::usuarioPertenceCooperativa($user, (int) $andamentoCaso->caso->cooperativa_id)
            && ControleAcesso::usuarioTemPermissao($user, 'andamentos.visualizar');
    }

    public function create(User $user, Caso $caso): bool
    {
        return EscopoCooperativa::temCooperativa($user)
            && EscopoCooperativa::usuarioPertenceCooperativa($user, (int) $caso->cooperativa_id)
            && ControleAcesso::usuarioTemPermissao($user, 'andamentos.criar');
    }
}
