@php
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
@endphp

<form method="POST" action="{{ route('configuracoes.geral.update') }}" class="space-y-4">
    @csrf
    @method('PATCH')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="nome_sistema" class="block text-sm font-medium text-gray-700">Nome do sistema</label>
            <input type="text" name="nome_sistema" id="nome_sistema" value="{{ old('nome_sistema', $configuracaoGeral->nome_sistema) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('nome_sistema')" class="mt-2" />
        </div>

        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700">Fuso horário</label>
            <select name="timezone" id="timezone" class="{{ $inputClass }}" required>
                @foreach ($timezonesBrasil as $valor => $label)
                    <option value="{{ $valor }}" @selected(old('timezone', $configuracaoGeral->timezone) === $valor)>
                        {{ $label }} - {{ $valor }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
        </div>

        <div class="md:col-span-2">
            <label for="email_suporte" class="block text-sm font-medium text-gray-700">E-mail de suporte</label>
            <input type="email" name="email_suporte" id="email_suporte" value="{{ old('email_suporte', $configuracaoGeral->email_suporte) }}" class="{{ $inputClass }}">
            <x-input-error :messages="$errors->get('email_suporte')" class="mt-2" />
        </div>

        <div>
            <label for="login_badge_text" class="block text-sm font-medium text-gray-700">Texto do selo do login</label>
            <input type="text" name="login_badge_text" id="login_badge_text" value="{{ old('login_badge_text', $configuracaoGeral->login_badge_text) }}" maxlength="80" class="{{ $inputClass }}">
            <p class="mt-1 text-xs text-gray-500">Se vazio, o sistema usa: Sistema interno.</p>
            <x-input-error :messages="$errors->get('login_badge_text')" class="mt-2" />
        </div>

        <div>
            <label for="login_title" class="block text-sm font-medium text-gray-700">Título do login</label>
            <input type="text" name="login_title" id="login_title" value="{{ old('login_title', $configuracaoGeral->login_title) }}" maxlength="120" class="{{ $inputClass }}">
            <p class="mt-1 text-xs text-gray-500">Se vazio, o sistema usa: {{ $nomeSistema ?? config('app.name', 'Sistema') }}.</p>
            <x-input-error :messages="$errors->get('login_title')" class="mt-2" />
        </div>

        <div class="md:col-span-2">
            <label for="login_description" class="block text-sm font-medium text-gray-700">Descrição do login</label>
            <textarea name="login_description" id="login_description" rows="3" maxlength="600" class="{{ $inputClass }}">{{ old('login_description', $configuracaoGeral->login_description) }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Texto institucional exibido abaixo do título na tela de login.</p>
            <x-input-error :messages="$errors->get('login_description')" class="mt-2" />
        </div>

        <div class="md:col-span-2">
            <label for="rodape_relatorio" class="block text-sm font-medium text-gray-700">Rodapé do relatório</label>
            <textarea name="rodape_relatorio" id="rodape_relatorio" rows="4" class="{{ $inputClass }}">{{ old('rodape_relatorio', $configuracaoGeral->rodape_relatorio) }}</textarea>
            <x-input-error :messages="$errors->get('rodape_relatorio')" class="mt-2" />
        </div>
    </div>

    <div>
        <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
            Salvar configurações gerais
        </button>
    </div>
</form>
