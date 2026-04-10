<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Papel;

class PapelPolicy
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
        return false;
    }

    public function view(User $user, Papel $papel): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Papel $papel): bool
    {
        return false;
    }
}

