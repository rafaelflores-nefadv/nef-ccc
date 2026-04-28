@extends('layouts.app')

@section('title', 'Cooperativas')

@section('content')
    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-slate-900">Gestão de cooperativas</h2>
                <a href="{{ route('cooperativas.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nova cooperativa
                </a>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="p-6">
                    <form method="GET" action="{{ route('cooperativas.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
                            <input type="text" name="nome" id="nome" value="{{ $filtros['nome'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                            <input type="text" name="slug" id="slug" value="{{ $filtros['slug'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="ativo" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="ativo" id="ativo" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="1" @selected((string) ($filtros['ativo'] ?? '') === '1')>Ativa</option>
                                <option value="0" @selected((string) ($filtros['ativo'] ?? '') === '0')>Inativa</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" stroke-width="1.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 20l-3.5-3.5" />
                                </svg>
                                Buscar
                            </button>
                            <a href="{{ route('cooperativas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpar filtros</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nome</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Slug</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Usuários vinculados</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Casos vinculados</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($cooperativas as $cooperativa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $cooperativa->nome }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $cooperativa->slug }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $cooperativa->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $cooperativa->ativo ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $cooperativa->users_count }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $cooperativa->casos_count }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('cooperativas.edit', $cooperativa) }}" class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1.5 text-amber-700 transition hover:bg-amber-100">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 3.487a2.121 2.121 0 113 3L7.5 18.85l-4.5 1.5 1.5-4.5 12.362-12.363z" />
                                                </svg>
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('cooperativas.status', $cooperativa) }}"
                                                data-confirm="true"
                                                data-confirm-title="{{ $cooperativa->ativo ? 'Desativar cooperativa' : 'Ativar cooperativa' }}"
                                                data-confirm-message="{{ $cooperativa->ativo ? 'Tem certeza de que deseja desativar esta cooperativa?' : 'Tem certeza de que deseja ativar esta cooperativa?' }}"
                                                data-confirm-text="{{ $cooperativa->ativo ? 'Desativar' : 'Ativar' }}"
                                                data-confirm-variant="{{ $cooperativa->ativo ? 'danger' : 'primary' }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="ativo" value="{{ $cooperativa->ativo ? '0' : '1' }}">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 transition {{ $cooperativa->ativo ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                        @if ($cooperativa->ativo)
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.25 7.5h13.5M9 7.5V6a3 3 0 016 0v1.5m-8.25 0v8.25A2.25 2.25 0 009 18h6a2.25 2.25 0 002.25-2.25V7.5" />
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m6-6H6" />
                                                        @endif
                                                    </svg>
                                                    {{ $cooperativa->ativo ? 'Desativar' : 'Ativar' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 9h16.5m-16.5 0a2.25 2.25 0 00-2.25 2.25v7.5A2.25 2.25 0 003.75 21h16.5a2.25 2.25 0 002.25-2.25v-7.5A2.25 2.25 0 0020.25 9m-16.5 0V6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25V9" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Nenhuma cooperativa encontrada.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-4">
                    {{ $cooperativas->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
