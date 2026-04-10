@extends('layouts.app')

@section('title', 'Notificações')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Central de notificações</h2>
                    <p class="text-sm text-slate-600">Acompanhe os avisos internos enviados para o seu usuário.</p>
                </div>

                @if ($naoLidasCount > 0)
                    <form method="POST" action="{{ route('notificacoes.markAllAsRead') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-slate-800">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Marcar todas como lidas
                        </button>
                    </form>
                @endif
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="divide-y divide-gray-200">
                    @forelse ($notificacoes as $notificacao)
                        @php
                            $tipo = (string) data_get($notificacao->data, 'type', 'info');
                            $titulo = (string) data_get($notificacao->data, 'title', 'Notificação');
                            $mensagem = (string) data_get($notificacao->data, 'message', '');
                            $naoLida = $notificacao->read_at === null;

                            $tipoClasses = match ($tipo) {
                                'success' => 'bg-emerald-100 text-emerald-700',
                                'warning' => 'bg-amber-100 text-amber-700',
                                'danger' => 'bg-rose-100 text-rose-700',
                                default => 'bg-blue-100 text-blue-700',
                            };

                            $tipoLabel = match ($tipo) {
                                'success' => 'Sucesso',
                                'warning' => 'Alerta',
                                'danger' => 'Erro',
                                default => 'Informação',
                            };
                        @endphp

                        <div class="p-4 sm:p-5 {{ $naoLida ? 'bg-blue-50/60' : 'bg-white' }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $tipoClasses }}">
                                            {{ $tipoLabel }}
                                        </span>

                                        @if ($naoLida)
                                            <span class="inline-flex rounded-full bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white">
                                                Não lida
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                Lida
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-base font-semibold text-slate-900">{{ $titulo }}</h3>
                                    <p class="text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($mensagem, 180) }}</p>
                                    <p class="text-xs text-slate-500">{{ optional($notificacao->created_at)->format('d/m/Y H:i') }}</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('notificacoes.show', $notificacao->id) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 text-sm text-indigo-700 transition hover:bg-indigo-100">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
                                            <circle cx="12" cy="12" r="3" stroke-width="1.5" />
                                        </svg>
                                        Abrir
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <svg class="mx-auto h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9a6 6 0 00-12 0v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.564 1.08 5.454 1.31m5.715 0a24.255 24.255 0 01-5.715 0m5.715 0a3 3 0 11-5.715 0" />
                            </svg>
                            <p class="mt-3 text-sm text-slate-500">Você ainda não recebeu notificações.</p>
                        </div>
                    @endforelse
                </div>

                @if ($notificacoes->hasPages())
                    <div class="border-t border-gray-200 p-4">
                        {{ $notificacoes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
