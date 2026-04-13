@extends('layouts.app')

@section('title', 'Relatórios')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                Aplique os filtros desejados e clique em <strong>Exportar Excel</strong> para gerar o relatório.
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('relatorios.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        @if ($isAdmin)
                            <div>
                                <label for="cooperativa_id" class="block text-sm font-medium text-gray-700">Cooperativa</label>
                                <select name="cooperativa_id" id="cooperativa_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <option value="">Todas</option>
                                    @foreach ($cooperativas as $cooperativa)
                                        <option value="{{ $cooperativa->id }}" @selected((string) ($filtros['cooperativa_id'] ?? '') === (string) $cooperativa->id)>{{ $cooperativa->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="md:col-span-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                O relatório será gerado apenas com os dados das suas cooperativas vinculadas.
                            </div>
                        @endif

                        <div>
                            <label for="codigo_caso" class="block text-sm font-medium text-gray-700">Código do caso</label>
                            <input type="text" name="codigo_caso" id="codigo_caso" value="{{ $filtros['codigo_caso'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="numero_protocolo" class="block text-sm font-medium text-gray-700">Número de protocolo</label>
                            <input type="text" name="numero_protocolo" id="numero_protocolo" value="{{ $filtros['numero_protocolo'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="numero_prenotacao" class="block text-sm font-medium text-gray-700">Número de prenotação</label>
                            <input type="text" name="numero_prenotacao" id="numero_prenotacao" value="{{ $filtros['numero_prenotacao'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="contrato" class="block text-sm font-medium text-gray-700">Contrato</label>
                            <input type="text" name="contrato" id="contrato" value="{{ $filtros['contrato'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
                            <input type="text" name="nome" id="nome" value="{{ $filtros['nome'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="comarca" class="block text-sm font-medium text-gray-700">Comarca</label>
                            <input type="text" name="comarca" id="comarca" value="{{ $filtros['comarca'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="uf" class="block text-sm font-medium text-gray-700">UF</label>
                            <input type="text" name="uf" id="uf" maxlength="2" value="{{ $filtros['uf'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 uppercase shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="tipo_status_id" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="tipo_status_id" id="tipo_status_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($tiposStatus as $status)
                                    <option value="{{ $status->id }}" @selected((string) ($filtros['tipo_status_id'] ?? '') === (string) $status->id)>{{ $status->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tipo_substatus_id" class="block text-sm font-medium text-gray-700">Substatus</label>
                            <select name="tipo_substatus_id" id="tipo_substatus_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($tiposSubstatus as $substatus)
                                    <option value="{{ $substatus->id }}" @selected((string) ($filtros['tipo_substatus_id'] ?? '') === (string) $substatus->id)>{{ $substatus->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="responsavel_id" class="block text-sm font-medium text-gray-700">Responsável</label>
                            <select name="responsavel_id" id="responsavel_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($responsaveis as $responsavel)
                                    <option value="{{ $responsavel->id }}" @selected((string) ($filtros['responsavel_id'] ?? '') === (string) $responsavel->id)>{{ $responsavel->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="arquivado" class="block text-sm font-medium text-gray-700">Arquivado</label>
                            <select name="arquivado" id="arquivado" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="0" @selected(($filtros['arquivado'] ?? '') === '0')>Não</option>
                                <option value="1" @selected(($filtros['arquivado'] ?? '') === '1')>Sim</option>
                            </select>
                        </div>

                        <div>
                            <label for="data_prazo_inicial" class="block text-sm font-medium text-gray-700">Data de prazo inicial</label>
                            <input type="date" name="data_prazo_inicial" id="data_prazo_inicial" value="{{ $filtros['data_prazo_inicial'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="data_prazo_final" class="block text-sm font-medium text-gray-700">Data de prazo final</label>
                            <input type="date" name="data_prazo_final" id="data_prazo_final" value="{{ $filtros['data_prazo_final'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="data_cadastro_inicial" class="block text-sm font-medium text-gray-700">Data de cadastro inicial</label>
                            <input type="date" name="data_cadastro_inicial" id="data_cadastro_inicial" value="{{ $filtros['data_cadastro_inicial'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="data_cadastro_final" class="block text-sm font-medium text-gray-700">Data de cadastro final</label>
                            <input type="date" name="data_cadastro_final" id="data_cadastro_final" value="{{ $filtros['data_cadastro_final'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="flex items-center gap-3 md:col-span-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke-width="1.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 20l-3.5-3.5" /></svg>
                                Buscar
                            </button>
                            <button type="submit" formaction="{{ route('relatorios.exportar.excel') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v12m0 0l4.5-4.5M12 15l-4.5-4.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 15.75v2.625A2.625 2.625 0 006.375 21h11.25a2.625 2.625 0 002.625-2.625V15.75" /></svg>
                                Exportar Excel
                            </button>
                            <a href="{{ route('relatorios.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpar filtros</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
