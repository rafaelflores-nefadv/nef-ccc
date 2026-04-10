@extends('layouts.app')

@section('title', 'Consulta')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-end gap-4">
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    Prazo distribuição: {{ $diasPrazoConfigurado }} dia(s)
                </span>
                <a href="{{ route('casos.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Adicionar caso
                </a>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6">
                    <form method="GET" action="{{ route('casos.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
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

                        @if ($isAdmin)
                            <div>
                                <label for="cooperativa_id" class="block text-sm font-medium text-gray-700">Cooperativa</label>
                                <select name="cooperativa_id" id="cooperativa_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <option value="">Todas</option>
                                    @foreach ($cooperativas as $cooperativa)
                                        <option value="{{ $cooperativa->id }}" @selected((string) ($filtros['cooperativa_id'] ?? '') === (string) $cooperativa->id)>
                                            {{ $cooperativa->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label for="tipo_status_id" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="tipo_status_id" id="tipo_status_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($tiposStatus as $status)
                                    <option value="{{ $status->id }}" @selected((string) ($filtros['tipo_status_id'] ?? '') === (string) $status->id)>
                                        {{ $status->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tipo_substatus_id" class="block text-sm font-medium text-gray-700">Substatus</label>
                            <select name="tipo_substatus_id" id="tipo_substatus_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($tiposSubstatus as $substatus)
                                    <option value="{{ $substatus->id }}" @selected((string) ($filtros['tipo_substatus_id'] ?? '') === (string) $substatus->id)>
                                        {{ $substatus->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status_prazo_distribuicao" class="block text-sm font-medium text-gray-700">Prazo distribuição</label>
                            <select name="status_prazo_distribuicao" id="status_prazo_distribuicao" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($statusPrazoOpcoes as $valor => $label)
                                    <option value="{{ $valor }}" @selected((string) ($filtros['status_prazo_distribuicao'] ?? '') === (string) $valor)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-3 md:col-span-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" stroke-width="1.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 20l-3.5-3.5" />
                                </svg>
                                Buscar
                            </button>
                            <a href="{{ route('casos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpar filtros</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Código</th>
                                @if ($isAdmin)
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cooperativa</th>
                                @endif
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Protocolo</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Prenotação</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Contrato</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nome</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Comarca</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Substatus</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Distribuição</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Limite</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Prazo distribuição</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Dias</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Prazo manual</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($casos as $caso)
                                @php
                                    $statusPrazo = $caso->status_prazo_distribuicao;
                                    $badgeStatusPrazo = match ($statusPrazo) {
                                        'dentro_do_prazo' => 'bg-emerald-100 text-emerald-700',
                                        'igual_ao_prazo' => 'bg-amber-100 text-amber-700',
                                        'prazo_vencido' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };

                                    $labelStatusPrazo = match ($statusPrazo) {
                                        'dentro_do_prazo' => 'Dentro do prazo',
                                        'igual_ao_prazo' => 'Igual ao prazo',
                                        'prazo_vencido' => 'Passou do prazo',
                                        default => 'Sem distribuição',
                                    };

                                    $descricaoDias = match ($statusPrazo) {
                                        'dentro_do_prazo' => ($caso->dias_restantes_prazo ?? 0).' dia(s) restantes',
                                        'igual_ao_prazo' => 'Vence hoje',
                                        'prazo_vencido' => ($caso->dias_atraso_prazo ?? 0).' dia(s) em atraso',
                                        default => '-',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $caso->codigo_caso }}</td>
                                    @if ($isAdmin)
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->cooperativa?->nome ?? '-' }}</td>
                                    @endif
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->numero_protocolo ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->numero_prenotacao ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->contrato }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->nome ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->comarca ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->tipoStatus?->nome ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->tipoSubstatus?->nome ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ optional($caso->distribuicao)->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ optional($caso->data_limite_prazo)->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeStatusPrazo }}">
                                            {{ $labelStatusPrazo }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $descricaoDias }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ optional($caso->data_prazo)->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('casos.show', $caso) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 text-indigo-700 transition hover:bg-indigo-100">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" /><circle cx="12" cy="12" r="3" stroke-width="1.5" /></svg>
                                                Visualizar
                                            </a>
                                            <a href="{{ route('casos.edit', $caso) }}" class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1.5 text-amber-700 transition hover:bg-amber-100">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 3.487a2.121 2.121 0 113 3L7.5 18.85l-4.5 1.5 1.5-4.5 12.362-12.363z" /></svg>
                                                Editar
                                            </a>
                                            <form method="POST" action="{{ route('casos.destroy', $caso) }}" onsubmit="return confirm('Tem certeza de que deseja excluir este caso?\nEsta ação não poderá ser desfeita.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2.5 py-1.5 text-red-700 transition hover:bg-red-100">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 011.5-1.5h6A1.5 1.5 0 0116.5 6v1.5m-9 0l.638 10.213A1.5 1.5 0 009.634 19.5h4.732a1.5 1.5 0 001.496-1.287L16.5 7.5" /></svg>
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 15 : 14 }}" class="px-4 py-8 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 9h16.5m-16.5 0a2.25 2.25 0 00-2.25 2.25v7.5A2.25 2.25 0 003.75 21h16.5a2.25 2.25 0 002.25-2.25v-7.5A2.25 2.25 0 0020.25 9m-16.5 0V6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25V9" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Nenhum caso cadastrado ainda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-4">
                    {{ $casos->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
