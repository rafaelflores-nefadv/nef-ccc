@php
    $ativoValor = old('ativo', $feriadoSuspensao->ativo ?? true);
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="data" class="block text-sm font-medium text-gray-700">Data</label>
        <input type="date" name="data" id="data" value="{{ old('data', optional($feriadoSuspensao->data)->format('Y-m-d')) }}" class="{{ $inputClass }}" required>
        <x-input-error :messages="$errors->get('data')" class="mt-2" />
    </div>

    <div>
        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
        <input type="text" name="descricao" id="descricao" value="{{ old('descricao', $feriadoSuspensao->descricao) }}" class="{{ $inputClass }}" required>
        <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
    </div>

    <div>
        <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
        <select name="tipo" id="tipo" class="{{ $inputClass }}" required>
            <option value="">Selecione</option>
            @foreach ($tipos as $valor => $label)
                <option value="{{ $valor }}" @selected(old('tipo', $feriadoSuspensao->tipo) === $valor)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
    </div>

    <div>
        <label for="abrangencia" class="block text-sm font-medium text-gray-700">Abrangência</label>
        <select name="abrangencia" id="abrangencia" class="{{ $inputClass }}" required>
            <option value="">Selecione</option>
            @foreach ($abrangencias as $valor => $label)
                <option value="{{ $valor }}" @selected(old('abrangencia', $feriadoSuspensao->abrangencia) === $valor)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('abrangencia')" class="mt-2" />
    </div>

    <div>
        <label for="uf" class="block text-sm font-medium text-gray-700">UF</label>
        <input type="text" name="uf" id="uf" maxlength="2" value="{{ old('uf', $feriadoSuspensao->uf) }}" class="{{ $inputClass }} uppercase">
        <x-input-error :messages="$errors->get('uf')" class="mt-2" />
    </div>

    <div>
        <label for="comarca" class="block text-sm font-medium text-gray-700">Comarca</label>
        <input type="text" name="comarca" id="comarca" value="{{ old('comarca', $feriadoSuspensao->comarca) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('comarca')" class="mt-2" />
    </div>
</div>

<div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
    Quando a abrangência for <strong>nacional</strong>, UF e comarca não são necessários e serão ignorados.
</div>

<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox" name="ativo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked((bool) $ativoValor)>
        <span class="ms-2 text-sm text-gray-600">Registro ativo</span>
    </label>
    <x-input-error :messages="$errors->get('ativo')" class="mt-2" />
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
        {{ $submitLabel }}
    </button>
    <a href="{{ route('feriados_suspensoes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
