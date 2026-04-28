<?php

namespace App\Policies;

use App\Models\User;

class UsuarioPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($ability !== 'delete' && $user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false;
    }

    public function delete(User $authUser, User $user): bool
    {
        if (! $authUser->isAdmin()) {
            return false;
        }

        if ($authUser->id === $user->id) {
            return false;
        }

        return ! $user->ativo;
    }
}
