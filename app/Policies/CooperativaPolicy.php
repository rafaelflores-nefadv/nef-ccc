<?php

namespace App\Policies;

use App\Models\Cooperativa;
use App\Models\User;

class CooperativaPolicy
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

    public function view(User $user, Cooperativa $cooperativa): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Cooperativa $cooperativa): bool
    {
        return false;
    }
}
