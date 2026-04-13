@extends('layouts.app')

@section('title', 'Painel')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-2 text-sm text-gray-700 sm:grid-cols-2 lg:grid-cols-4">
                    <p><strong>Usuário:</strong> {{ $usuario->name }}</p>
                    <p><strong>E-mail:</strong> {{ $usuario->email }}</p>
                    <p><strong>Perfil:</strong> {{ $usuario->perfil }}</p>
                    <p><strong>Cooperativas:</strong> {{ $cooperativa }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total de casos</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $totalCasos }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Casos ativos</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $casosAtivos }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Casos arquivados</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-700">{{ $casosArquivados }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Prazos vencidos (distribuição)</p>
                    <p class="mt-2 text-2xl font-semibold text-red-700">{{ $prazosVencidos }}</p>
                </div>
            </div>

            @if ($totalCasos === 0)
                <div class="rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm">
                    <svg class="mx-auto h-10 w-10 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 9h16.5m-16.5 0a2.25 2.25 0 00-2.25 2.25v7.5A2.25 2.25 0 003.75 21h16.5a2.25 2.25 0 002.25-2.25v-7.5A2.25 2.25 0 0020.25 9m-16.5 0V6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25V9" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-600">Nenhum registro encontrado para os critérios de acesso do usuário.</p>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">No limite hoje (distribuição)</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $prazosHoje }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Limite em até 7 dias</p>
                    <p class="mt-2 text-2xl font-semibold text-indigo-700">{{ $prazosProximos }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Casos com leilão</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $casosLeilao }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Sem responsável</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $casosSemResponsavel }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-xl bg-white shadow-sm">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Alertas de prazos vencidos por distribuição</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Caso</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nome</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Data limite</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($alertasVencidos as $caso)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                <a href="{{ route('casos.show', $caso) }}" class="text-indigo-600 hover:text-indigo-900">{{ $caso->codigo_caso }}</a>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->nome ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm font-semibold text-red-700">{{ $caso->data_limite_dashboard ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->tipoStatus?->nome ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Nenhum prazo vencido por distribuição.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-white shadow-sm">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Alertas no limite de prazo hoje</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Caso</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nome</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Data limite</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($alertasHoje as $caso)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                <a href="{{ route('casos.show', $caso) }}" class="text-indigo-600 hover:text-indigo-900">{{ $caso->codigo_caso }}</a>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->nome ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm font-semibold text-amber-700">{{ $caso->data_limite_dashboard ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->tipoStatus?->nome ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Nenhum caso no limite de prazo hoje.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-xl bg-white shadow-sm">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Últimos casos cadastrados</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Caso</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nome</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cadastro</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($ultimosCasos as $caso)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                <a href="{{ route('casos.show', $caso) }}" class="text-indigo-600 hover:text-indigo-900">{{ $caso->codigo_caso }}</a>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->nome ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $caso->tipoStatus?->nome ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ optional($caso->created_at)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Nenhum caso cadastrado ainda.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-white shadow-sm">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Últimos andamentos</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Caso</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Descrição</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Registro</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($ultimosAndamentos as $andamento)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                @if ($andamento->caso)
                                                    <a href="{{ route('casos.show', $andamento->caso) }}" class="text-indigo-600 hover:text-indigo-900">{{ $andamento->caso->codigo_caso }}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $andamento->tipoStatus?->nome ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($andamento->descricao, 80) }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ optional($andamento->created_at)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Nenhum andamento registrado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-xl bg-white shadow-sm">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Casos por status</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($casosPorStatus as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $item->nome }}</td>
                                        <td class="px-4 py-2 text-sm font-semibold text-gray-700">{{ $item->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">Nenhum registro encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl bg-white shadow-sm">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Casos por substatus</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Substatus</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($casosPorSubstatus as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $item->nome }}</td>
                                        <td class="px-4 py-2 text-sm font-semibold text-gray-700">{{ $item->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">Nenhum registro encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

