@php
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
@endphp

<form
    method="POST"
    action="{{ route('configuracoes.email.update') }}"
    class="space-y-4"
    x-data="emailTester({
        testeUrl: @js(route('configuracoes.email.teste')),
        statusUrlTemplate: @js(route('configuracoes.tarefas.status', ['token' => '__TOKEN__'])),
        csrfToken: @js(csrf_token()),
    })"
    x-ref="form"
>
    @csrf
    @method('PATCH')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="driver" class="block text-sm font-medium text-gray-700">Driver</label>
            <select name="driver" id="driver" class="{{ $inputClass }}" required>
                @foreach (['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log'] as $valor => $label)
                    <option value="{{ $valor }}" @selected(old('driver', $configuracaoEmail->driver) === $valor)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('driver')" class="mt-2" />
        </div>

        <div>
            <label for="host" class="block text-sm font-medium text-gray-700">Host</label>
            <input type="text" name="host" id="host" value="{{ old('host', $configuracaoEmail->host) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('host')" class="mt-2" />
        </div>

        <div>
            <label for="porta" class="block text-sm font-medium text-gray-700">Porta</label>
            <input type="number" name="porta" id="porta" min="1" value="{{ old('porta', $configuracaoEmail->porta) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('porta')" class="mt-2" />
        </div>

        <div>
            <label for="usuario" class="block text-sm font-medium text-gray-700">Usuário</label>
            <input type="text" name="usuario" id="usuario" value="{{ old('usuario', $configuracaoEmail->usuario) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('usuario')" class="mt-2" />
        </div>

        <div>
            <label for="senha" class="block text-sm font-medium text-gray-700">Senha</label>
            <input type="password" name="senha" id="senha" class="{{ $inputClass }}">
            <p class="mt-1 text-xs text-gray-500">Deixe em branco para manter a senha atual.</p>
            <x-input-error :messages="$errors->get('senha')" class="mt-2" />
        </div>

        <div>
            <label for="criptografia" class="block text-sm font-medium text-gray-700">Criptografia</label>
            <select name="criptografia" id="criptografia" class="{{ $inputClass }}">
                <option value="">Nenhuma</option>
                <option value="tls" @selected(old('criptografia', $configuracaoEmail->criptografia) === 'tls')>TLS</option>
                <option value="ssl" @selected(old('criptografia', $configuracaoEmail->criptografia) === 'ssl')>SSL</option>
            </select>
            <x-input-error :messages="$errors->get('criptografia')" class="mt-2" />
        </div>

        <div>
            <label for="email_remetente" class="block text-sm font-medium text-gray-700">E-mail remetente</label>
            <input type="email" name="email_remetente" id="email_remetente" value="{{ old('email_remetente', $configuracaoEmail->email_remetente) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('email_remetente')" class="mt-2" />
        </div>

        <div>
            <label for="nome_remetente" class="block text-sm font-medium text-gray-700">Nome remetente</label>
            <input type="text" name="nome_remetente" id="nome_remetente" value="{{ old('nome_remetente', $configuracaoEmail->nome_remetente) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('nome_remetente')" class="mt-2" />
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" name="ativo" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked((bool) old('ativo', $configuracaoEmail->ativo))>
        Configuração de e-mail ativa
    </label>

    <div
        x-show="mensagem !== ''"
        x-cloak
        class="rounded-lg border px-4 py-3 text-sm"
        :class="{
            'border-slate-300 bg-slate-50 text-slate-700': status === 'pendente' || status === 'processando',
            'border-emerald-300 bg-emerald-50 text-emerald-700': status === 'sucesso',
            'border-rose-300 bg-rose-50 text-rose-700': status === 'falha'
        }"
    >
        <span class="font-semibold uppercase text-xs" x-text="status"></span>
        <p class="mt-1" x-text="mensagem"></p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
            Salvar configurações de e-mail
        </button>

        <button
            type="button"
            @click="testarConfiguracao()"
            :disabled="loading"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 12h-15m0 0l4.5-4.5M4.5 12l4.5 4.5" /></svg>
            <span x-text="loading ? 'Testando...' : 'Testar configuração'"></span>
        </button>
    </div>
</form>

