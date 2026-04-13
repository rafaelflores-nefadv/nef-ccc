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
        $ids = self::cooperativaIds($user);

        return $ids[0] ?? null;
    }

    /**
     * @return array<int>
     */
    public static function cooperativaIds(?User $user = null): array
    {
        $usuario = self::usuario($user);

        if (! $usuario || self::isAdmin($usuario)) {
            return [];
        }

        return $usuario->cooperativasIds();
    }

    public static function temCooperativa(?User $user = null): bool
    {
        return self::cooperativaIds($user) !== [];
    }

    public static function usuarioPertenceCooperativa(User $user, ?int $cooperativaId): bool
    {
        if (self::isAdmin($user)) {
            return true;
        }

        if ($cooperativaId === null || $cooperativaId <= 0) {
            return false;
        }

        return in_array((int) $cooperativaId, self::cooperativaIds($user), true);
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
