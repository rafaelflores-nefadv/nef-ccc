<?php

namespace App\Support;

use App\Models\User;

class EscopoCooperativa
{
    public static function isAdmin(?User $user = null): bool
    {
        return self::usuario($user)?->perfil === User::PERFIL_ADMIN;
    }

    public static function cooperativaId(?User $user = null): ?int
    {
        $usuario = self::usuario($user);

        if (! $usuario || self::isAdmin($usuario)) {
            return null;
        }

        return $usuario->cooperativa_id;
    }

    protected static function usuario(?User $user = null): ?User
    {
        if ($user instanceof User) {
            return $user;
        }

        $authUser = auth()->user();

        return $authUser instanceof User ? $authUser : null;
    }
}
