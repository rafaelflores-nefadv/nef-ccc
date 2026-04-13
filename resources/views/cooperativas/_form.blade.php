@php
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
    $statusSelecionado = old('ativo', $cooperativa->exists ? ($cooperativa->ativo ? '1' : '0') : '1');
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
        <input type="text" name="nome" id="nome" value="{{ old('nome', $cooperativa->nome) }}" class="{{ $inputClass }}" required>
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $cooperativa->slug) }}" class="{{ $inputClass }}" required>
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <label for="ativo" class="block text-sm font-medium text-gray-700">Status</label>
        <select name="ativo" id="ativo" class="{{ $inputClass }}">
            <option value="1" @selected((string) $statusSelecionado === '1')>Ativa</option>
            <option value="0" @selected((string) $statusSelecionado === '0')>Inativa</option>
        </select>
        <x-input-error :messages="$errors->get('ativo')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        {{ $submitLabel }}
    </button>
    <a href="{{ route('cooperativas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
