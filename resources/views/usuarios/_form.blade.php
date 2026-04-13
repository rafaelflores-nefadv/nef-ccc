@php
    $perfilSelecionado = old('perfil', $usuario->perfil ?: \App\Models\User::PERFIL_OPERACIONAL);
    $cooperativasSelecionadas = collect(old('cooperativas', ($usuario->exists ? $usuario->cooperativas->pluck('id')->all() : []) ?: ($usuario->cooperativa_id ? [$usuario->cooperativa_id] : [])))
        ->map(fn ($id) => (string) $id)
        ->all();
    $cooperativasDisponiveis = $cooperativas
        ->map(fn ($cooperativa) => [
            'id' => (string) $cooperativa->id,
            'nome' => (string) $cooperativa->nome,
        ])
        ->values()
        ->all();
    $cooperativasPorId = $cooperativas->keyBy(fn ($cooperativa) => (string) $cooperativa->id);
    $cooperativasIniciais = collect($cooperativasSelecionadas)
        ->map(function (string $cooperativaId) use ($cooperativasPorId): array {
            $cooperativa = $cooperativasPorId->get($cooperativaId);

            if (! $cooperativa) {
                return [
                    'id' => $cooperativaId,
                    'nome' => 'Cooperativa #'.$cooperativaId,
                ];
            }

            return [
                'id' => (string) $cooperativa->id,
                'nome' => (string) $cooperativa->nome,
            ];
        })
        ->values()
        ->all();
    $papelSelecionado = old('papel_id', $usuario->papeis->first()?->id);
    $ativoSelecionado = old('ativo', $usuario->exists ? ($usuario->ativo ? '1' : '0') : '1');
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
@endphp

<div
    x-data="{
        perfil: @js((string) $perfilSelecionado),
        cooperativaAtualId: '',
        cooperativasDisponiveis: @js($cooperativasDisponiveis),
        cooperativasSelecionadas: @js($cooperativasIniciais),
        adicionarCooperativa() {
            if (! this.cooperativaAtualId) {
                return;
            }

            const jaExiste = this.cooperativasSelecionadas.some((item) => item.id === this.cooperativaAtualId);

            if (jaExiste) {
                this.cooperativaAtualId = '';

                return;
            }

            const cooperativa = this.cooperativasDisponiveis.find((item) => item.id === this.cooperativaAtualId);

            if (! cooperativa) {
                this.cooperativaAtualId = '';

                return;
            }

            this.cooperativasSelecionadas.push(cooperativa);
            this.cooperativaAtualId = '';
        },
        removerCooperativa(cooperativaId) {
            this.cooperativasSelecionadas = this.cooperativasSelecionadas.filter((item) => item.id !== cooperativaId);
        },
        cooperativasRestantes() {
            return this.cooperativasDisponiveis.filter(
                (item) => ! this.cooperativasSelecionadas.some((selecionada) => selecionada.id === item.id)
            );
        }
    }"
    class="space-y-4"
>
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
            <label for="cooperativa_disponivel" class="block text-sm font-medium text-gray-700">
                Cooperativas
                <span class="text-red-600" x-show="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>*</span>
            </label>
            <div class="mt-1 flex items-center gap-2">
                <select
                    id="cooperativa_disponivel"
                    x-model="cooperativaAtualId"
                    class="{{ $inputClass }} mt-0"
                    :disabled="perfil === '{{ \App\Models\User::PERFIL_ADMIN }}'"
                >
                    <option value="">Selecione uma cooperativa</option>
                    <template x-for="cooperativa in cooperativasRestantes()" :key="`disp-${cooperativa.id}`">
                        <option :value="cooperativa.id" x-text="cooperativa.nome"></option>
                    </template>
                </select>
                <button
                    type="button"
                    @click="adicionarCooperativa()"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="perfil === '{{ \App\Models\User::PERFIL_ADMIN }}' || !cooperativaAtualId"
                >
                    Adicionar
                </button>
            </div>

            <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cooperativas adicionadas</p>

                <template x-if="cooperativasSelecionadas.length === 0">
                    <p class="mt-2 text-sm text-gray-500">Nenhuma cooperativa adicionada.</p>
                </template>

                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="cooperativa in cooperativasSelecionadas" :key="`sel-${cooperativa.id}`">
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700">
                            <span x-text="cooperativa.nome"></span>
                            <button
                                type="button"
                                @click="removerCooperativa(cooperativa.id)"
                                class="font-semibold text-rose-600 transition hover:text-rose-700"
                            >
                                Remover
                            </button>
                            <input type="hidden" name="cooperativas[]" :value="cooperativa.id">
                        </div>
                    </template>
                </div>
            </div>

            <p class="mt-1 text-xs text-gray-500" x-show="perfil === '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>
                Para perfil administrador, deixe a lista sem cooperativas.
            </p>
            <p class="mt-1 text-xs text-gray-500" x-show="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>
                Para perfis gestor e operacional, selecione ao menos uma cooperativa.
            </p>
            <x-input-error :messages="$errors->get('cooperativas')" class="mt-2" />
            <x-input-error :messages="$errors->get('cooperativas.*')" class="mt-2" />
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
                Para perfil administrador, o papel pode ficar vazio.
            </p>
            <p class="mt-1 text-xs text-gray-500" x-show="perfil !== '{{ \App\Models\User::PERFIL_ADMIN }}'" x-cloak>
                Para perfis gestor e operacional, o papel e obrigatorio.
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

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Senha
                @if (! $isEdit)
                    <span class="text-red-600">*</span>
                @endif
            </label>
            <input
                type="password"
                name="password"
                id="password"
                class="{{ $inputClass }}"
                autocomplete="new-password"
                @if (! $isEdit) required @endif
            >
            <p class="mt-1 text-xs text-gray-500">
                A senha deve conter ao menos 8 caracteres, incluindo letra maiuscula, minuscula, numero e caractere especial.
            </p>
            @if ($isEdit)
                <p class="mt-1 text-xs text-gray-500">Preencha somente se desejar alterar a senha atual.</p>
            @endif
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirmar senha
                @if (! $isEdit)
                    <span class="text-red-600">*</span>
                @endif
            </label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="{{ $inputClass }}"
                autocomplete="new-password"
                @if (! $isEdit) required @endif
            >
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
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
