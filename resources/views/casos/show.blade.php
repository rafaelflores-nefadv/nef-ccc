@extends('layouts.app')

@section('title', 'Consulta')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Detalhes do caso {{ $caso->codigo_caso }}</h2>

                <div class="flex items-center gap-3">
                    <a href="{{ route('casos.edit', $caso) }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-amber-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 3.487a2.121 2.121 0 113 3L7.5 18.85l-4.5 1.5 1.5-4.5 12.362-12.363z" /></svg>
                        Editar
                    </a>
                    <a href="{{ route('casos.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Voltar</a>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Dados principais</h3>
                    <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                        <div><strong>Código:</strong> {{ $caso->codigo_caso }}</div>
                        <div><strong>Cooperativa:</strong> {{ $caso->cooperativa?->nome ?? '-' }}</div>
                        <div><strong>Responsável:</strong> {{ $caso->responsavel?->name ?? '-' }}</div>
                        <div><strong>Contrato:</strong> {{ $caso->contrato }}</div>
                        <div><strong>Protocolo:</strong> {{ $caso->numero_protocolo ?? '-' }}</div>
                        <div><strong>Prenotação:</strong> {{ $caso->numero_prenotacao ?? '-' }}</div>
                        <div><strong>Nome:</strong> {{ $caso->nome ?? '-' }}</div>
                        <div><strong>Comarca:</strong> {{ $caso->comarca ?? '-' }}</div>
                        <div><strong>UF:</strong> {{ $caso->uf ?? '-' }}</div>
                        <div><strong>Matrícula:</strong> {{ $caso->matricula ?? '-' }}</div>
                        <div><strong>Valor da causa:</strong> {{ $caso->valor_causa !== null ? number_format((float) $caso->valor_causa, 2, ',', '.') : '-' }}</div>
                        <div><strong>Valor da dívida:</strong> {{ $caso->valor_divida !== null ? number_format((float) $caso->valor_divida, 2, ',', '.') : '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    @php
                        $statusPrazo = $caso->status_prazo_distribuicao;
                        $statusPrazoLabel = match ($statusPrazo) {
                            'dentro_do_prazo' => 'Dentro do prazo',
                            'igual_ao_prazo' => 'Igual ao prazo',
                            'prazo_vencido' => 'Passou do prazo',
                            default => 'Sem distribuição',
                        };

                        $statusPrazoClass = match ($statusPrazo) {
                            'dentro_do_prazo' => 'bg-emerald-100 text-emerald-700',
                            'igual_ao_prazo' => 'bg-amber-100 text-amber-700',
                            'prazo_vencido' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <h3 class="mb-4 text-lg font-semibold">Status e prazos</h3>
                    <div class="mb-4 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Prazo atual do caso</p>
                        <p class="text-lg font-semibold text-indigo-900">{{ optional($caso->data_prazo)->format('d/m/Y') ?? 'Não definido' }}</p>
                    </div>
                    <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-700">Prazo por distribuição ({{ $diasPrazoConfigurado }} dia(s))</p>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusPrazoClass }}">
                                {{ $statusPrazoLabel }}
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-slate-700 md:grid-cols-2 xl:grid-cols-3">
                            <div><strong>Distribuição:</strong> {{ optional($caso->distribuicao)->format('d/m/Y H:i') ?? '-' }}</div>
                            <div><strong>Data limite:</strong> {{ optional($caso->data_limite_prazo)->format('d/m/Y') ?? '-' }}</div>
                            @if ($statusPrazo === 'dentro_do_prazo')
                                <div><strong>Dias restantes:</strong> {{ $caso->dias_restantes_prazo ?? 0 }}</div>
                            @elseif ($statusPrazo === 'igual_ao_prazo')
                                <div><strong>Situação:</strong> Vence hoje</div>
                            @elseif ($statusPrazo === 'prazo_vencido')
                                <div><strong>Dias em atraso:</strong> {{ $caso->dias_atraso_prazo ?? 0 }}</div>
                            @else
                                <div><strong>Situação:</strong> Sem base de cálculo</div>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                        <div><strong>Status:</strong> {{ $caso->tipoStatus?->nome ?? '-' }}</div>
                        <div><strong>Substatus:</strong> {{ $caso->tipoSubstatus?->nome ?? '-' }}</div>
                        <div><strong>Arquivado:</strong> {{ $caso->arquivado ? 'Sim' : 'Não' }}</div>
                        <div><strong>Dias configurados (distribuição):</strong> {{ $diasPrazoConfigurado }}</div>
                        <div><strong>Data alteração status:</strong> {{ optional($caso->data_alteracao_status)->format('d/m/Y') ?? '-' }}</div>
                        <div><strong>Data alteração substatus:</strong> {{ optional($caso->data_alteracao_substatus)->format('d/m/Y') ?? '-' }}</div>
                        <div><strong>Data prazo:</strong> {{ optional($caso->data_prazo)->format('d/m/Y') ?? '-' }}</div>
                        <div><strong>Primeiro leilão:</strong> {{ optional($caso->data_primeiro_leilao)->format('d/m/Y') ?? '-' }}</div>
                        <div><strong>Segundo leilão:</strong> {{ optional($caso->data_segundo_leilao)->format('d/m/Y') ?? '-' }}</div>
                        <div><strong>Último andamento:</strong> {{ optional($caso->data_ultimo_andamento)->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="space-y-4 p-6 text-gray-900">
                    <div>
                        <h3 class="mb-2 text-lg font-semibold">Partes</h3>
                        <p class="whitespace-pre-line text-sm">{{ $caso->partes }}</p>
                    </div>

                    <div>
                        <h3 class="mb-2 text-lg font-semibold">Observações gerais</h3>
                        <p class="whitespace-pre-line text-sm">{{ $caso->observacoes_gerais ?: '-' }}</p>
                    </div>

                    <div>
                        <h3 class="mb-2 text-lg font-semibold">Parecer</h3>
                        <p class="whitespace-pre-line text-sm">{{ $caso->parecer ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div id="andamentos" class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h3 class="text-lg font-semibold">Histórico de andamentos</h3>
                        <a href="{{ route('casos.andamentos.index', $caso) }}" class="text-sm text-gray-600 hover:text-gray-900">Atualizar lista</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Data</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Substatus</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Descrição</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Observações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($caso->andamentos as $andamento)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ optional($andamento->data_descricao)->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $andamento->tipoStatus?->nome ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $andamento->tipoSubstatus?->nome ?? '-' }}</td>
                                        <td class="whitespace-pre-line px-4 py-2 text-sm text-gray-700">{{ $andamento->descricao }}</td>
                                        <td class="whitespace-pre-line px-4 py-2 text-sm text-gray-700">{{ $andamento->observacoes ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Nenhum andamento registrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Novo andamento</h3>
                    <p class="mb-4 text-sm text-gray-600">O prazo poderá ser calculado automaticamente conforme o substatus.</p>

                    <form method="POST" action="{{ route('casos.andamentos.store', $caso) }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="caso_id" value="{{ $caso->id }}">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="tipo_status_id" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="tipo_status_id" id="tipo_status_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Selecione</option>
                                    @foreach ($tiposStatus as $status)
                                        <option value="{{ $status->id }}" @selected((string) old('tipo_status_id') === (string) $status->id)>{{ $status->nome }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tipo_status_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="tipo_substatus_id" class="block text-sm font-medium text-gray-700">Substatus</label>
                                <select name="tipo_substatus_id" id="tipo_substatus_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Selecione</option>
                                    @foreach ($tiposSubstatus as $substatus)
                                        <option value="{{ $substatus->id }}" @selected((string) old('tipo_substatus_id') === (string) $substatus->id)>{{ $substatus->nome }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tipo_substatus_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="data_andamento" class="block text-sm font-medium text-gray-700">Data do andamento</label>
                                <input type="date" name="data_andamento" id="data_andamento" value="{{ old('data_andamento', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500" required>
                                <x-input-error :messages="$errors->get('data_andamento')" class="mt-2" />
                            </div>

                            <div>
                                <label for="data_prazo" class="block text-sm font-medium text-gray-700">Data de prazo manual (opcional)</label>
                                <input type="date" name="data_prazo" id="data_prazo" value="{{ old('data_prazo') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('data_prazo')" class="mt-2" />
                            </div>

                            <div>
                                <label for="data_primeiro_leilao" class="block text-sm font-medium text-gray-700">Data do primeiro leilão</label>
                                <input type="date" name="data_primeiro_leilao" id="data_primeiro_leilao" value="{{ old('data_primeiro_leilao') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('data_primeiro_leilao')" class="mt-2" />
                            </div>

                            <div>
                                <label for="data_segundo_leilao" class="block text-sm font-medium text-gray-700">Data do segundo leilão</label>
                                <input type="date" name="data_segundo_leilao" id="data_segundo_leilao" value="{{ old('data_segundo_leilao') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('data_segundo_leilao')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="descricao" id="descricao" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500" required>{{ old('descricao') }}</textarea>
                            <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                        </div>

                        <div>
                            <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">{{ old('observacoes') }}</textarea>
                            <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
                        </div>

                        <div>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Adicionar andamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

