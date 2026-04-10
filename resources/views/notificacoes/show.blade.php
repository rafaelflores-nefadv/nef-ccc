@extends('layouts.app')

@section('title', 'Notificação')

@section('content')
    @php
        $tipo = (string) data_get($notificacao->data, 'type', 'info');
        $titulo = (string) data_get($notificacao->data, 'title', 'Notificação');
        $mensagem = (string) data_get($notificacao->data, 'message', '');
        $urlRelacionada = data_get($notificacao->data, 'url');

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

    <div class="py-2">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Detalhe da notificação</h2>
                <a href="{{ route('notificacoes.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Voltar</a>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="space-y-4 p-6 text-slate-900">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $tipoClasses }}">
                            {{ $tipoLabel }}
                        </span>
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                            Lida em {{ optional($notificacao->read_at)->format('d/m/Y H:i') ?? '-' }}
                        </span>
                    </div>

                    <h3 class="text-xl font-semibold text-slate-900">{{ $titulo }}</h3>

                    <p class="whitespace-pre-line text-sm leading-6 text-slate-700">{{ $mensagem !== '' ? $mensagem : 'Sem conteúdo para esta notificação.' }}</p>

                    <div class="grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 sm:grid-cols-2">
                        <p><strong>Recebida em:</strong> {{ optional($notificacao->created_at)->format('d/m/Y H:i') ?? '-' }}</p>
                        <p><strong>Atualizada em:</strong> {{ optional($notificacao->updated_at)->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>

                    @if (filled($urlRelacionada))
                        <div>
                            <a href="{{ $urlRelacionada }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 6H18m0 0v4.5m0-4.5L10.5 13.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 7.5A1.5 1.5 0 017.5 6h3m-4.5 3v7.5A1.5 1.5 0 007.5 18h7.5A1.5 1.5 0 0016.5 16.5v-3" />
                                </svg>
                                Acessar link relacionado
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
