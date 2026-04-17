@php
    $routeName = request()->route()?->getName();
    $usuario = Auth::user();
    $isAdmin = $usuario?->isAdmin() === true;

    $podeDashboard = $isAdmin || ($usuario && \App\Support\ControleAcesso::usuarioTemPermissao($usuario, 'dashboard.visualizar'));
    $podeCadastroCasos = $isAdmin || ($usuario && \App\Support\ControleAcesso::usuarioTemAlgumaPermissao($usuario, ['casos.criar', 'casos.editar']));
    $podeConsultaCasos = $isAdmin || ($usuario && \App\Support\ControleAcesso::usuarioTemPermissao($usuario, 'casos.visualizar'));
    $podeRelatorios = $isAdmin || ($usuario && \App\Support\ControleAcesso::usuarioTemPermissao($usuario, 'relatorios.visualizar'));
    $podeAtualizacao = $isAdmin || ($usuario && \App\Support\ControleAcesso::usuarioTemAlgumaPermissao($usuario, ['feriados.visualizar', 'feriados.gerenciar']));

    $menu = [];

    if ($podeDashboard) {
        $menu[] = [
            'label' => 'Painel',
            'route' => 'dashboard',
            'active' => str_starts_with($routeName ?? '', 'dashboard'),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125A1.125 1.125 0 005.625 21h4.5v-6.75h3.75V21h4.5a1.125 1.125 0 001.125-1.125V9.75" />',
        ];
    }

    if ($podeCadastroCasos) {
        $menu[] = [
            'label' => 'Cadastro',
            'route' => 'casos.create',
            'active' => in_array($routeName, ['casos.create', 'casos.edit'], true),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 3.487a2.121 2.121 0 113 3L7.5 18.85l-4.5 1.5 1.5-4.5 12.362-12.363z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5.25l3 3" />',
        ];
    }

    if ($podeAtualizacao) {
        $menu[] = [
            'label' => 'Atualização',
            'route' => 'atualizacao.index',
            'active' => str_starts_with($routeName ?? '', 'atualizacao'),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.023 9.348h4.992V4.356" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.985 19.644v-4.992h4.992" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 9.75a7.5 7.5 0 0112.677-5.302l3.838 3.838M19.5 14.25a7.5 7.5 0 01-12.677 5.302l-3.838-3.838" />',
        ];
    }

    if ($podeConsultaCasos) {
        $menu[] = [
            'label' => 'Consulta',
            'route' => 'casos.index',
            'active' => str_starts_with($routeName ?? '', 'casos.index') || str_starts_with($routeName ?? '', 'casos.show') || str_starts_with($routeName ?? '', 'casos.andamentos'),
            'icon' => '<circle cx="11" cy="11" r="7" stroke-width="1.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 20l-3.5-3.5" />',
        ];
    }

    if ($podeRelatorios) {
        $menu[] = [
            'label' => 'Relatórios',
            'route' => 'relatorios.index',
            'active' => str_starts_with($routeName ?? '', 'relatorios'),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 3v18h16.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 15.75v-3m4.5 3V8.25m4.5 7.5V5.25" />',
        ];
    }

    if ($isAdmin) {
        $menu[] = [
            'label' => 'Usuários',
            'route' => 'usuarios.index',
            'active' => str_starts_with($routeName ?? '', 'usuarios'),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.75a3 3 0 00-3-3h-6a3 3 0 00-3 3M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM20.25 10.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM20.25 18.75v-.75a2.25 2.25 0 00-2.25-2.25h-.75" />',
        ];

        $menu[] = [
            'label' => 'Cooperativas',
            'route' => 'cooperativas.index',
            'active' => str_starts_with($routeName ?? '', 'cooperativas'),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 8.25l8.25-4.5 8.25 4.5M4.5 9.75v7.5m15-7.5v7.5M7.5 18.75h9M9.75 9.75v6m4.5-6v6" />',
        ];

        $menu[] = [
            'label' => 'Papéis e Acessos',
            'route' => 'papeis.index',
            'active' => str_starts_with($routeName ?? '', 'papeis'),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 6.75h2.25A2.25 2.25 0 0121 9v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18V9a2.25 2.25 0 012.25-2.25H7.5M9 6.75h6M9 11.25h6m-6 4.5h3" />',
        ];

        $menu[] = [
            'label' => 'Configurações',
            'route' => 'configuracoes.index',
            'active' => str_starts_with($routeName ?? '', 'configuracoes'),
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.272a1.125 1.125 0 00.84.903l1.202.3c.53.133.883.635.83 1.18l-.12 1.285a1.125 1.125 0 00.273.84l.865.973c.367.413.367 1.03 0 1.443l-.865.973a1.125 1.125 0 00-.273.84l.12 1.285a1.125 1.125 0 01-.83 1.18l-1.202.3a1.125 1.125 0 00-.84.903l-.213 1.272c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.272a1.125 1.125 0 00-.84-.903l-1.202-.3a1.125 1.125 0 01-.83-1.18l.12-1.285a1.125 1.125 0 00-.273-.84l-.865-.973a1.125 1.125 0 010-1.443l.865-.973a1.125 1.125 0 00.273-.84l-.12-1.285a1.125 1.125 0 01.83-1.18l1.202-.3a1.125 1.125 0 00.84-.903l.213-1.272zM12 15.75A3.75 3.75 0 1012 8.25a3.75 3.75 0 000 7.5z" />',
        ];
    }

    $ordemMenu = [
        'dashboard' => 1,
        'atualizacao.index' => 2,
        'casos.create' => 3,
        'casos.index' => 4,
        'relatorios.index' => 5,
        'usuarios.index' => 6,
        'cooperativas.index' => 7,
        'papeis.index' => 8,
        'configuracoes.index' => 9,
    ];

    foreach ($menu as &$item) {
        if (($item['route'] ?? null) === 'casos.create') {
            $item['label'] = 'Cadastro';
        }
    }
    unset($item);

    usort($menu, function (array $a, array $b) use ($ordemMenu): int {
        $ordemA = $ordemMenu[$a['route']] ?? 999;
        $ordemB = $ordemMenu[$b['route']] ?? 999;

        return $ordemA <=> $ordemB;
    });
@endphp

<div class="nf-sidebar fixed inset-y-0 left-0 z-40 w-64 transform text-slate-100 transition-transform duration-200 ease-out md:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="nf-sidebar-divider flex h-16 items-center border-b px-6">
        <a href="{{ route('dashboard') }}" class="nf-sidebar-title text-sm font-semibold tracking-wide">
            {{ $nomeSistema ?? config('app.name', 'Sistema') }}
        </a>
    </div>

    <nav class="space-y-1 p-4">
        @foreach ($menu as $item)
            <a href="{{ route($item['route']) }}" class="nf-sidebar-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all {{ $item['active'] ? 'nf-sidebar-link-active' : '' }}">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    {!! $item['icon'] !!}
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>

<div class="fixed inset-0 z-30 bg-slate-950/40 md:hidden" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" x-cloak></div>
