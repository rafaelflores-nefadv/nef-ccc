<?php

namespace App\Policies;

use App\Models\FeriadoSuspensao;
use App\Models\User;
use App\Support\ControleAcesso;

class FeriadoSuspensaoPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return ControleAcesso::usuarioTemPermissao($user, 'feriados.visualizar');
    }

    public function view(User $user, FeriadoSuspensao $feriadoSuspensao): bool
    {
        return ControleAcesso::usuarioTemPermissao($user, 'feriados.visualizar');
    }

    public function create(User $user): bool
    {
        return ControleAcesso::usuarioTemPermissao($user, 'feriados.gerenciar');
    }

    public function update(User $user, FeriadoSuspensao $feriadoSuspensao): bool
    {
        return ControleAcesso::usuarioTemPermissao($user, 'feriados.gerenciar');
    }

    public function delete(User $user, FeriadoSuspensao $feriadoSuspensao): bool
    {
        return ControleAcesso::usuarioTemPermissao($user, 'feriados.gerenciar');
    }
}
