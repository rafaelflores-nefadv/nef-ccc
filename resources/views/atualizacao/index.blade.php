@extends('layouts.app')

@section('title', 'Atualização')

@php
    $statusClasses = [
        'pendente' => 'bg-amber-100 text-amber-800',
        'executando' => 'bg-blue-100 text-blue-800',
        'sucesso' => 'bg-emerald-100 text-emerald-800',
        'erro' => 'bg-red-100 text-red-800',
    ];

    $statusLabel = $execucao?->status ? ucfirst($execucao->status) : '-';
    $statusClass = $statusClasses[$execucao->status ?? 'pendente'] ?? 'bg-slate-200 text-slate-700';
    $logsIniciais = $logs ?? collect();
@endphp

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h1 class="text-lg font-semibold text-slate-900">Acompanhamento da Atualização</h1>
                    <p class="mt-1 text-sm text-slate-500">Monitoramento em tempo real da sincronização do robô com o módulo de casos.</p>
                </div>

                @if (! $execucao)
                    <div class="p-6">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            Nenhuma execução registrada até o momento.
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</p>
                            <div class="mt-2 flex items-center gap-3">
                                <span id="statusBadge" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                <span id="statusText" class="text-sm text-slate-700">{{ $execucao->mensagem_status ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Relatório</p>
                            <p id="relatorioId" class="mt-2 text-sm text-slate-700">{{ $execucao->relatorio_id ?? '-' }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Arquivo</p>
                            <p id="arquivoOrigem" class="mt-2 break-all text-sm text-slate-700">{{ $execucao->arquivo_origem ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="px-6 pb-2">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Progresso</span>
                            <span id="percentualText" class="font-semibold text-slate-900">{{ number_format((float) $execucao->percentual, 2, ',', '.') }}%</span>
                        </div>
                        <div class="h-3 w-full rounded-full bg-slate-200">
                            <div id="barraProgresso" class="h-3 rounded-full bg-blue-600 transition-all duration-500" style="width: {{ min(max((float) $execucao->percentual, 0), 100) }}%;"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-6 pt-4 md:grid-cols-4 lg:grid-cols-7">
                        <div class="rounded-lg border border-slate-200 p-3 text-center">
                            <p class="text-xs uppercase text-slate-500">Total</p>
                            <p id="totalLinhas" class="mt-1 text-lg font-semibold text-slate-900">{{ (int) $execucao->total_linhas }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 text-center">
                            <p class="text-xs uppercase text-slate-500">Processadas</p>
                            <p id="linhasProcessadas" class="mt-1 text-lg font-semibold text-slate-900">{{ (int) $execucao->linhas_processadas }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 text-center">
                            <p class="text-xs uppercase text-slate-500">Inseridas</p>
                            <p id="linhasInseridas" class="mt-1 text-lg font-semibold text-emerald-700">{{ (int) $execucao->linhas_inseridas }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 text-center">
                            <p class="text-xs uppercase text-slate-500">Atualizadas</p>
                            <p id="linhasAtualizadas" class="mt-1 text-lg font-semibold text-blue-700">{{ (int) $execucao->linhas_atualizadas }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 text-center">
                            <p class="text-xs uppercase text-slate-500">Ignoradas</p>
                            <p id="linhasIgnoradas" class="mt-1 text-lg font-semibold text-amber-700">{{ (int) $execucao->linhas_ignoradas }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 text-center">
                            <p class="text-xs uppercase text-slate-500">Erros</p>
                            <p id="linhasErro" class="mt-1 text-lg font-semibold text-red-700">{{ (int) $execucao->linhas_com_erro }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 text-center">
                            <p class="text-xs uppercase text-slate-500">Execução</p>
                            <p id="execucaoId" class="mt-1 text-lg font-semibold text-slate-900">#{{ $execucao->id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 border-t border-gray-200 p-6 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-500">Iniciado em</p>
                            <p id="iniciadoEm" class="mt-1 text-sm text-slate-700">{{ optional($execucao->iniciado_em)->format('d/m/Y H:i:s') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-slate-500">Finalizado em</p>
                            <p id="finalizadoEm" class="mt-1 text-sm text-slate-700">{{ optional($execucao->finalizado_em)->format('d/m/Y H:i:s') ?? '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if ($execucao)
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Logs da Execução</h2>
                    </div>
                    <div class="max-h-[28rem] overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="sticky top-0 bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Data/Hora</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nível</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Mensagem</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Contexto</th>
                                </tr>
                            </thead>
                            <tbody id="logsBody" class="divide-y divide-gray-200 bg-white">
                                @forelse ($logsIniciais as $log)
                                    <tr>
                                        <td class="px-4 py-2 text-xs text-slate-600">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                        <td class="px-4 py-2 text-xs font-semibold uppercase text-slate-700">{{ $log->nivel }}</td>
                                        <td class="px-4 py-2 text-sm text-slate-800">{{ $log->mensagem }}</td>
                                        <td class="px-4 py-2 text-xs text-slate-600">
                                            @if ($log->contexto_json)
                                                <pre class="max-w-xl overflow-auto whitespace-pre-wrap break-words rounded bg-slate-50 p-2">{{ json_encode($log->contexto_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Ainda não há logs para esta execução.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($execucao)
        <script>
            (() => {
                const executionId = {{ (int) $execucao->id }};
                let lastLogId = {{ (int) ($logsIniciais->last()->id ?? 0) }};

                const statusBadge = document.getElementById('statusBadge');
                const statusText = document.getElementById('statusText');
                const relatorioId = document.getElementById('relatorioId');
                const arquivoOrigem = document.getElementById('arquivoOrigem');
                const percentualText = document.getElementById('percentualText');
                const barraProgresso = document.getElementById('barraProgresso');
                const totalLinhas = document.getElementById('totalLinhas');
                const linhasProcessadas = document.getElementById('linhasProcessadas');
                const linhasInseridas = document.getElementById('linhasInseridas');
                const linhasAtualizadas = document.getElementById('linhasAtualizadas');
                const linhasIgnoradas = document.getElementById('linhasIgnoradas');
                const linhasErro = document.getElementById('linhasErro');
                const iniciadoEm = document.getElementById('iniciadoEm');
                const finalizadoEm = document.getElementById('finalizadoEm');
                const logsBody = document.getElementById('logsBody');

                const statusClassMap = {
                    pendente: 'bg-amber-100 text-amber-800',
                    executando: 'bg-blue-100 text-blue-800',
                    sucesso: 'bg-emerald-100 text-emerald-800',
                    erro: 'bg-red-100 text-red-800',
                };

                function setStatusBadge(status) {
                    const classes = ['bg-amber-100', 'text-amber-800', 'bg-blue-100', 'text-blue-800', 'bg-emerald-100', 'text-emerald-800', 'bg-red-100', 'text-red-800', 'bg-slate-200', 'text-slate-700'];
                    statusBadge.classList.remove(...classes);

                    const targetClass = statusClassMap[status] ?? 'bg-slate-200 text-slate-700';
                    statusBadge.classList.add(...targetClass.split(' '));
                    statusBadge.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : '-';
                }

                function renderExecucao(execucao) {
                    if (!execucao) {
                        return;
                    }

                    setStatusBadge(execucao.status);
                    statusText.textContent = execucao.mensagem_status ?? '-';
                    relatorioId.textContent = execucao.relatorio_id ?? '-';
                    arquivoOrigem.textContent = execucao.arquivo_origem ?? '-';
                    percentualText.textContent = `${Number(execucao.percentual ?? 0).toFixed(2)}%`;
                    barraProgresso.style.width = `${Math.max(0, Math.min(100, Number(execucao.percentual ?? 0)))}%`;
                    totalLinhas.textContent = execucao.total_linhas ?? 0;
                    linhasProcessadas.textContent = execucao.linhas_processadas ?? 0;
                    linhasInseridas.textContent = execucao.linhas_inseridas ?? 0;
                    linhasAtualizadas.textContent = execucao.linhas_atualizadas ?? 0;
                    linhasIgnoradas.textContent = execucao.linhas_ignoradas ?? 0;
                    linhasErro.textContent = execucao.linhas_com_erro ?? 0;
                    iniciadoEm.textContent = execucao.iniciado_em_formatado ?? '-';
                    finalizadoEm.textContent = execucao.finalizado_em_formatado ?? '-';
                }

                function renderLog(log) {
                    const escapeHtml = (value) => String(value)
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');

                    const tr = document.createElement('tr');
                    const contexto = log.contexto_json
                        ? `<pre class="max-w-xl overflow-auto whitespace-pre-wrap break-words rounded bg-slate-50 p-2">${escapeHtml(JSON.stringify(log.contexto_json))}</pre>`
                        : '-';

                    tr.innerHTML = `
                        <td class="px-4 py-2 text-xs text-slate-600">${escapeHtml(log.created_at_formatado ?? '-')}</td>
                        <td class="px-4 py-2 text-xs font-semibold uppercase text-slate-700">${escapeHtml(log.nivel ?? '-')}</td>
                        <td class="px-4 py-2 text-sm text-slate-800">${escapeHtml(log.mensagem ?? '-')}</td>
                        <td class="px-4 py-2 text-xs text-slate-600">${contexto}</td>
                    `;
                    return tr;
                }

                async function pollStatus() {
                    const response = await fetch(`{{ url('/atualizacao/status') }}/${executionId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) {
                        return;
                    }
                    const data = await response.json();
                    renderExecucao(data.execucao);
                }

                async function pollLogs() {
                    const response = await fetch(`{{ url('/atualizacao/logs') }}/${executionId}?after_id=${lastLogId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) {
                        return;
                    }
                    const data = await response.json();
                    const logs = Array.isArray(data.logs) ? data.logs : [];
                    if (logs.length === 0) {
                        return;
                    }

                    const emptyRow = logsBody.querySelector('td[colspan="4"]');
                    if (emptyRow) {
                        emptyRow.closest('tr')?.remove();
                    }

                    logs.forEach((log) => {
                        logsBody.appendChild(renderLog(log));
                        if (Number(log.id) > lastLogId) {
                            lastLogId = Number(log.id);
                        }
                    });
                    logsBody.parentElement?.scrollTo({ top: logsBody.parentElement.scrollHeight, behavior: 'smooth' });
                }

                async function poll() {
                    try {
                        await pollStatus();
                        await pollLogs();
                    } catch (error) {
                        console.error('Falha ao atualizar acompanhamento:', error);
                    }
                }

                setInterval(poll, 5000);
            })();
        </script>
    @endif
@endsection
