@php
    $pageTitle = trim($__env->yieldContent('title'));
    $userName = Auth::user()->name;
    $initial = mb_strtoupper(mb_substr($userName, 0, 1));
    $notificacoesRecentes = $headerNotificacoesRecentes ?? collect();
    $notificacoesNaoLidasCount = (int) ($headerNotificacoesNaoLidasCount ?? 0);
@endphp

<header class="fixed left-0 right-0 top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur md:left-64">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 p-2 text-slate-700 md:hidden"
                @click="sidebarOpen = true"
                aria-label="Abrir menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <h1 class="text-lg font-semibold text-slate-900">{{ $pageTitle !== '' ? $pageTitle : ($nomeSistema ?? config('app.name', 'Sistema')) }}</h1>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative" x-data="{ open: false }">
                <button
                    type="button"
                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50"
                    @click="open = !open"
                    aria-label="Abrir notificações"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9a6 6 0 00-12 0v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.564 1.08 5.454 1.31m5.715 0a24.255 24.255 0 01-5.715 0m5.715 0a3 3 0 11-5.715 0" />
                    </svg>

                    @if ($notificacoesNaoLidasCount > 0)
                        <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
                            {{ $notificacoesNaoLidasCount > 99 ? '99+' : $notificacoesNaoLidasCount }}
                        </span>
                    @endif
                </button>

                <div
                    class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                    x-show="open"
                    @click.outside="open = false"
                    x-transition
                    x-cloak
                >
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-800">Notificações</p>
                        @if ($notificacoesNaoLidasCount > 0)
                            <span class="text-xs font-medium text-slate-500">{{ $notificacoesNaoLidasCount }} não lida(s)</span>
                        @endif
                    </div>

                    <div class="max-h-80 overflow-y-auto">
                        @forelse ($notificacoesRecentes as $notificacao)
                            @php
                                $titulo = (string) data_get($notificacao->data, 'title', 'Notificação');
                                $mensagem = (string) data_get($notificacao->data, 'message', '');
                                $naoLida = $notificacao->read_at === null;
                            @endphp
                            <a
                                href="{{ route('notificacoes.show', $notificacao->id) }}"
                                class="block border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50 {{ $naoLida ? 'bg-blue-50/60' : 'bg-white' }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ $titulo }}</p>
                                        <p class="mt-1 text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($mensagem, 90) }}</p>
                                    </div>
                                    @if ($naoLida)
                                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500"></span>
                                    @endif
                                </div>
                                <p class="mt-2 text-[11px] text-slate-500">{{ optional($notificacao->created_at)->format('d/m/Y H:i') }}</p>
                            </a>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-slate-500">
                                Nenhuma notificação encontrada.
                            </div>
                        @endforelse
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3">
                        <a href="{{ route('notificacoes.index') }}" class="text-sm font-medium text-blue-700 transition hover:text-blue-800">
                            Ver todas
                        </a>
                        @if ($notificacoesNaoLidasCount > 0)
                            <form method="POST" action="{{ route('notificacoes.markAllAsRead') }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:text-slate-900">
                                    Marcar todas como lidas
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button
                    type="button"
                    class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                    @click="open = !open"
                >
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700">{{ $initial }}</span>
                    <span class="hidden sm:inline">{{ $userName }}</span>
                    <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 9l-3.75 3.75L8.25 9" />
                    </svg>
                </button>

                <div
                    class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                    x-show="open"
                    @click.outside="open = false"
                    x-transition
                    x-cloak
                >
                    <div class="border-b border-slate-100 px-4 py-3">
                        <div class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275" />
                            </svg>
                            {{ $userName }}
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6.75h3m0 0v3m0-3l-3.75 3.75M9 18h.008v.008H9V18zm3 0h.008v.008H12V18zm3 0h.008v.008H15V18z" />
                        </svg>
                        Perfil
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-rose-700 transition hover:bg-rose-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15l3-3m0 0l-3-3m3 3H3" />
                            </svg>
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
