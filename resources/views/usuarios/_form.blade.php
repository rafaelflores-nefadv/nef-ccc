@php
    $perfilSelecionado = old('perfil', $usuario->perfil ?: \App\Models\User::PERFIL_OPERACIONAL);
    $cooperativaSelecionada = old('cooperativa_id', $usuario->cooperativa_id);
    $papelSelecionado = old('papel_id', $usuario->papeis->first()?->id);
    $ativoSelecionado = old('ativo', $usuario->exists ? ($usuario->ativo ? '1' : '0') : '1');
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
@endphp

<div x-data="{ perfil: @js((string) $perfilSelecionado) }" class="space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
            <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
            <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="perfil" class="block text-sm font-medium text-gray-700">Perfil</label>
            <select name="perfil" id="perfil" x-model="perfil" class="{{ $inputClass }}" required>
                <option value="">Selecione</option>
                @foreach ($perfis as $valor => $label)
                    <option value="{{ $valor }}" @selected((string) $perfilSelecionado === (string) $valor)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('perfil')" class="mt-2" />
        </div>

        <div>
            <label for="cooperativa_id" class="block text-sm font-medium text-gray-700">
                Cooperativa
                <span class="text-red-600" x-show="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>*</span>
            </label>
            <select name="cooperativa_id" id="cooperativa_id" class="{{ $inputClass }}" :required="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'">
                <option value="">Sem cooperativa</option>
                @foreach ($cooperativas as $cooperativa)
                    <option value="{{ $cooperativa->id }}" @selected((string) $cooperativaSelecionada === (string) $cooperativa->id)>
                        {{ $cooperativa->nome }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500" x-show="perfil === '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>
                Para perfil admin, a cooperativa deve ficar vazia.
            </p>
            <p class="mt-1 text-xs text-gray-500" x-show="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>
                Para perfis gestor e operacional, a cooperativa é obrigatória.
            </p>
            <x-input-error :messages="$errors->get('cooperativa_id')" class="mt-2" />
        </div>

        <div>
            <label for="papel_id" class="block text-sm font-medium text-gray-700">
                Papel
                <span class="text-red-600" x-show="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>*</span>
            </label>
            <select name="papel_id" id="papel_id" class="{{ $inputClass }}" :required="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'">
                <option value="">Sem papel</option>
                @foreach ($papeis as $papel)
                    <option value="{{ $papel->id }}" @selected((string) $papelSelecionado === (string) $papel->id)>
                        {{ $papel->nome }}{{ $papel->ativo ? '' : ' (inativo)' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500" x-show="perfil === '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>
                Para perfil admin, o papel pode ficar vazio.
            </p>
            <p class="mt-1 text-xs text-gray-500" x-show="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>
                Para perfis gestor e operacional, o papel é obrigatório.
            </p>
            <x-input-error :messages="$errors->get('papel_id')" class="mt-2" />
        </div>

        <div>
            <label for="ativo" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="ativo" id="ativo" class="{{ $inputClass }}">
                <option value="1" @selected((string) $ativoSelecionado === '1')>Ativo</option>
                <option value="0" @selected((string) $ativoSelecionado === '0')>Inativo</option>
            </select>
            <x-input-error :messages="$errors->get('ativo')" class="mt-2" />
        </div>

        @if (! $isEdit)
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                <input type="password" name="password" id="password" class="{{ $inputClass }}" minlength="6" required>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
        @endif
    </div>

    <div class="pt-2">
        <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            {{ $submitLabel }}
        </button>
        <a href="{{ route('usuarios.index') }}" class="ml-3 text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
    </div>
</div>
