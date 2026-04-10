@php
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
    $statusSelecionado = old('ativo', $papel->exists ? ($papel->ativo ? '1' : '0') : '1');
    $permissoesSelecionadas = collect(old('permissoes', $papel->permissoes->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
        <input type="text" name="nome" id="nome" value="{{ old('nome', $papel->nome) }}" class="{{ $inputClass }}" required>
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $papel->slug) }}" class="{{ $inputClass }}" required>
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
        <textarea name="descricao" id="descricao" rows="3" class="{{ $inputClass }}">{{ old('descricao', $papel->descricao) }}</textarea>
        <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
    </div>

    <div>
        <label for="ativo" class="block text-sm font-medium text-gray-700">Status</label>
        <select name="ativo" id="ativo" class="{{ $inputClass }}">
            <option value="1" @selected((string) $statusSelecionado === '1')>Ativo</option>
            <option value="0" @selected((string) $statusSelecionado === '0')>Inativo</option>
        </select>
        <x-input-error :messages="$errors->get('ativo')" class="mt-2" />
    </div>
</div>

<div class="mt-6 space-y-4">
    <h3 class="text-base font-semibold text-slate-900">Permissões do papel</h3>

    @forelse ($permissoesPorModulo as $modulo => $permissoes)
        <div class="rounded-lg border border-gray-200 p-4">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ $modulo }}</h4>
            <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2">
                @foreach ($permissoes as $permissao)
                    <label class="inline-flex items-start gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            name="permissoes[]"
                            value="{{ $permissao->id }}"
                            class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                            @checked(in_array((string) $permissao->id, $permissoesSelecionadas, true))
                        >
                        <span>
                            <span class="font-medium">{{ $permissao->nome }}</span>
                            <span class="block text-xs text-gray-500">{{ $permissao->slug }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Nenhuma permissao cadastrada.
        </div>
    @endforelse

    <x-input-error :messages="$errors->get('permissoes')" class="mt-2" />
    <x-input-error :messages="$errors->get('permissoes.*')" class="mt-2" />
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        {{ $submitLabel }}
    </button>
    <a href="{{ route('papeis.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>

