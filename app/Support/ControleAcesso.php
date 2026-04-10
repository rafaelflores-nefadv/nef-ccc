<?php

namespace App\Support;

use App\Models\User;

class ControleAcesso
{
    public static function usuarioEhAdmin(User $user): bool
    {
        return $user->isAdmin();
    }

    public static function usuarioTemPermissao(User $user, string $slug): bool
    {
        if (self::usuarioEhAdmin($user)) {
            return true;
        }

        $slug = trim($slug);

        if ($slug === '') {
            return false;
        }

        if (! $user->ativo) {
            return false;
        }

        $user->loadMissing('papeis.permissoes:id,slug');

        $permissoesUsuario = $user->papeis
            ->flatMap(fn ($papel) => $papel->permissoes->pluck('slug'))
            ->unique()
            ->values()
            ->all();

        if ($permissoesUsuario === []) {
            $permissoesUsuario = self::permissoesFallbackPerfil($user);
        }

        return collect($permissoesUsuario)
            ->contains($slug);
    }

    /**
     * @param  array<int, string>  $slugs
     */
    public static function usuarioTemAlgumaPermissao(User $user, array $slugs): bool
    {
        if (self::usuarioEhAdmin($user)) {
            return true;
        }

        $slugsValidos = array_values(array_filter(array_map('trim', $slugs)));

        if ($slugsValidos === []) {
            return false;
        }

        if (! $user->ativo) {
            return false;
        }

        $user->loadMissing('papeis.permissoes:id,slug');

        $permissoesUsuario = $user->papeis
            ->flatMap(fn ($papel) => $papel->permissoes->pluck('slug'))
            ->unique()
            ->values()
            ->all();

        if ($permissoesUsuario === []) {
            $permissoesUsuario = self::permissoesFallbackPerfil($user);
        }

        return count(array_intersect($permissoesUsuario, $slugsValidos)) > 0;
    }

    /**
     * @return array<int, string>
     */
    protected static function permissoesFallbackPerfil(User $user): array
    {
        return match ($user->perfil) {
            User::PERFIL_GESTOR => [
                'dashboard.visualizar',
                'casos.visualizar',
                'casos.criar',
                'casos.editar',
                'andamentos.visualizar',
                'andamentos.criar',
                'relatorios.visualizar',
                'relatorios.exportar',
            ],
            User::PERFIL_OPERACIONAL => [
                'dashboard.visualizar',
                'casos.visualizar',
                'andamentos.visualizar',
                'andamentos.criar',
            ],
            default => [],
        };
    }
}
