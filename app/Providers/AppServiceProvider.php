<?php

namespace App\Providers;

use App\Models\Caso;
use App\Models\AndamentoCaso;
use App\Models\Cooperativa;
use App\Models\ConfiguracaoEmail;
use App\Models\ConfiguracaoGeral;
use App\Models\ConfiguracaoNotificacao;
use App\Models\ConfiguracaoProvedorMensagem;
use App\Models\FeriadoSuspensao;
use App\Models\Papel;
use App\Models\ProvedorMensagem;
use App\Models\User;
use App\Policies\AndamentoCasoPolicy;
use App\Policies\CasoPolicy;
use App\Policies\CooperativaPolicy;
use App\Policies\ConfiguracaoPolicy;
use App\Policies\FeriadoSuspensaoPolicy;
use App\Policies\PapelPolicy;
use App\Policies\UsuarioPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\View as BladeView;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Caso::class, CasoPolicy::class);
        Gate::policy(AndamentoCaso::class, AndamentoCasoPolicy::class);
        Gate::policy(FeriadoSuspensao::class, FeriadoSuspensaoPolicy::class);
        Gate::policy(ConfiguracaoGeral::class, ConfiguracaoPolicy::class);
        Gate::policy(ConfiguracaoEmail::class, ConfiguracaoPolicy::class);
        Gate::policy(ConfiguracaoNotificacao::class, ConfiguracaoPolicy::class);
        Gate::policy(ProvedorMensagem::class, ConfiguracaoPolicy::class);
        Gate::policy(ConfiguracaoProvedorMensagem::class, ConfiguracaoPolicy::class);
        Gate::policy(Cooperativa::class, CooperativaPolicy::class);
        Gate::policy(Papel::class, PapelPolicy::class);
        Gate::policy(User::class, UsuarioPolicy::class);

        View::share('nomeSistema', $this->resolverNomeSistema());
        View::share('logoSistemaUrl', $this->resolverLogoSistemaUrl());

        View::composer('layouts.header', function (BladeView $view): void {
            $usuario = Auth::user();

            if (! $usuario || ! Schema::hasTable('notifications')) {
                $view->with('headerNotificacoesRecentes', collect());
                $view->with('headerNotificacoesNaoLidasCount', 0);

                return;
            }

            $view->with('headerNotificacoesRecentes', $usuario->notifications()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get());

            $view->with('headerNotificacoesNaoLidasCount', $usuario->unreadNotifications()->count());
        });
    }

    protected function resolverNomeSistema(): string
    {
        $fallback = (string) config('app.name', 'Sistema');

        try {
            if (! Schema::hasTable('configuracoes_gerais') || ! Schema::hasColumn('configuracoes_gerais', 'nome_sistema')) {
                return $fallback;
            }

            $nomeSistema = ConfiguracaoGeral::query()
                ->whereKey(1)
                ->value('nome_sistema');

            $nomeSistemaNormalizado = Str::of((string) $nomeSistema)->trim()->value();

            return $nomeSistemaNormalizado !== '' ? $nomeSistemaNormalizado : $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    protected function resolverLogoSistemaUrl(): ?string
    {
        $fallbackLogo = public_path('images/logo-nef.jpg');

        try {
            if (! Schema::hasTable('configuracoes_gerais') || ! Schema::hasColumn('configuracoes_gerais', 'logo_path')) {
                return file_exists($fallbackLogo) ? asset('images/logo-nef.jpg') : null;
            }

            $logoPath = ConfiguracaoGeral::query()
                ->whereKey(1)
                ->value('logo_path');

            $logoPathNormalizado = Str::of((string) $logoPath)->trim()->value();

            if ($logoPathNormalizado === '') {
                return file_exists($fallbackLogo) ? asset('images/logo-nef.jpg') : null;
            }

            if (Str::startsWith($logoPathNormalizado, ['http://', 'https://', '/'])) {
                return $logoPathNormalizado;
            }

            return Storage::url($logoPathNormalizado);
        } catch (Throwable) {
            return file_exists($fallbackLogo) ? asset('images/logo-nef.jpg') : null;
        }
    }
}
