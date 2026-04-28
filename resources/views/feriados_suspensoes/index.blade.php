@extends('layouts.app')

@section('title', 'Atualização')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('feriados_suspensoes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Adicionar registro
                </a>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6">
                    <form method="GET" action="{{ route('feriados_suspensoes.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label for="data" class="block text-sm font-medium text-gray-700">Data</label>
                            <input type="date" name="data" id="data" value="{{ $filtros['data'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <input type="text" name="descricao" id="descricao" value="{{ $filtros['descricao'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="tipo" id="tipo" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach ($tipos as $valor => $label)
                                    <option value="{{ $valor }}" @selected(($filtros['tipo'] ?? '') === $valor)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="abrangencia" class="block text-sm font-medium text-gray-700">Abrangência</label>
                            <select name="abrangencia" id="abrangencia" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todas</option>
                                @foreach ($abrangencias as $valor => $label)
                                    <option value="{{ $valor }}" @selected(($filtros['abrangencia'] ?? '') === $valor)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="uf" class="block text-sm font-medium text-gray-700">UF</label>
                            <input type="text" name="uf" id="uf" maxlength="2" value="{{ $filtros['uf'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 uppercase shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="comarca" class="block text-sm font-medium text-gray-700">Comarca</label>
                            <input type="text" name="comarca" id="comarca" value="{{ $filtros['comarca'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="ativo" class="block text-sm font-medium text-gray-700">Ativo</label>
                            <select name="ativo" id="ativo" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="1" @selected(($filtros['ativo'] ?? '') === '1')>Sim</option>
                                <option value="0" @selected(($filtros['ativo'] ?? '') === '0')>Não</option>
                            </select>
                        </div>

                        <div class="flex flex-wrap items-end gap-3 md:col-span-4">
                            <div>
                                <label for="per_page" class="block text-sm font-medium text-gray-700">Registros por página</label>
                                <select name="per_page" id="per_page" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                                    @foreach ($perPageOptions as $opcao)
                                        <option value="{{ $opcao }}" @selected((int) $perPage === (int) $opcao)>{{ $opcao }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke-width="1.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 20l-3.5-3.5" /></svg>
                                Buscar
                            </button>
                            <a href="{{ route('feriados_suspensoes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpar filtros</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Data</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Descrição</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Abrangência</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">UF</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Comarca</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Ativo</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($feriadosSuspensoes as $registro)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ optional($registro->data)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $registro->descricao }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ ucfirst($registro->tipo) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ ucfirst($registro->abrangencia) }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $registro->uf ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $registro->comarca ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $registro->ativo ? 'Sim' : 'Não' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('feriados_suspensoes.edit', $registro) }}" class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1.5 text-amber-700 transition hover:bg-amber-100">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 3.487a2.121 2.121 0 113 3L7.5 18.85l-4.5 1.5 1.5-4.5 12.362-12.363z" /></svg>
                                                Editar
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('feriados_suspensoes.destroy', $registro) }}"
                                                data-confirm="true"
                                                data-confirm-title="Excluir registro"
                                                data-confirm-message="Tem certeza de que deseja excluir este registro? Esta ação não poderá ser desfeita."
                                                data-confirm-text="Excluir"
                                                data-confirm-variant="danger"
                                            >
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
                                    <td colspan="8" class="px-4 py-8 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 9h16.5m-16.5 0a2.25 2.25 0 00-2.25 2.25v7.5A2.25 2.25 0 003.75 21h16.5a2.25 2.25 0 002.25-2.25v-7.5A2.25 2.25 0 0020.25 9" /></svg>
                                        <p class="mt-2 text-sm text-gray-500">Nenhum registro cadastrado ainda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-600">
                            Mostrando {{ $feriadosSuspensoes->firstItem() ?? 0 }} a {{ $feriadosSuspensoes->lastItem() ?? 0 }} de {{ $feriadosSuspensoes->total() }} registros
                        </p>
                        {{ $feriadosSuspensoes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
