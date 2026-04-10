<?php

namespace App\View\Components;

use App\Models\ConfiguracaoGeral;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * @var array{badge_text: string, title: string, description: string}
     */
    public array $loginBranding;

    public function __construct()
    {
        $this->loginBranding = $this->carregarBrandingLogin();
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest', [
            'loginBranding' => $this->loginBranding,
        ]);
    }

    /**
     * @return array{badge_text: string, title: string, description: string}
     */
    protected function carregarBrandingLogin(): array
    {
        $fallback = [
            'badge_text' => 'Sistema interno',
            'title' => config('app.name', 'Sistema'),
            'description' => 'Plataforma de gestao e acompanhamento com foco em produtividade, controle e seguranca de acesso.',
        ];

        if (! Schema::hasTable('configuracoes_gerais')) {
            return $fallback;
        }

        if (! Schema::hasColumns('configuracoes_gerais', ['login_badge_text', 'login_title', 'login_description'])) {
            return $fallback;
        }

        $configuracao = ConfiguracaoGeral::query()
            ->select(['nome_sistema', 'login_badge_text', 'login_title', 'login_description'])
            ->find(1);

        if (! $configuracao) {
            return $fallback;
        }

        $badgeText = Str::of((string) $configuracao->login_badge_text)->trim()->value();
        $nomeSistema = Str::of((string) $configuracao->nome_sistema)->trim()->value();
        $title = Str::of((string) $configuracao->login_title)->trim()->value();
        $description = Str::of((string) $configuracao->login_description)->trim()->value();
        $fallbackTitle = $nomeSistema !== '' ? $nomeSistema : $fallback['title'];

        return [
            'badge_text' => $badgeText !== '' ? $badgeText : $fallback['badge_text'],
            'title' => $title !== '' ? $title : $fallbackTitle,
            'description' => $description !== '' ? $description : $fallback['description'],
        ];
    }
}
