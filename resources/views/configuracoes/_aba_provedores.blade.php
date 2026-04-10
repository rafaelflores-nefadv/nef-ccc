@php
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
@endphp

<div
    class="space-y-6"
    x-data="provedorTester({
        csrfToken: @js(csrf_token()),
        testeConectividadeUrl: @js(route('configuracoes.provedores.teste.conectividade')),
        testeMensagemUrl: @js(route('configuracoes.provedores.teste.mensagem')),
        statusUrlTemplate: @js(route('configuracoes.tarefas.status', ['token' => '__TOKEN__'])),
        initialForm: @js($provedorWhatsappForm),
    })"
>
    @if ($errors->has('provedor_id') || $errors->has('meta_url_base') || $errors->has('meta_token') || $errors->has('meta_phone_number_id') || $errors->has('meta_business_account_id') || $errors->has('meta_api_version') || $errors->has('waha_url_base') || $errors->has('waha_token') || $errors->has('waha_instancia'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            Verifique os campos da configuração do provedor de WhatsApp.
        </div>
    @endif

    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        O sistema utiliza uma configuração única de provedor de WhatsApp.
        Apenas o provedor selecionado neste formulário ficará ativo.
    </div>

    <div class="rounded-xl border border-gray-200 p-5">
        <h3 class="text-base font-semibold text-slate-900">Configuração do provedor de WhatsApp</h3>
        <p class="mt-1 text-sm text-slate-600">
            Defina o provedor oficial do sistema e preencha os parâmetros necessários da integração.
        </p>

        @if ($configuracaoProvedorWhatsapp)
            <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                Configuração atual: <strong>{{ $configuracaoProvedorWhatsapp->provedor?->nome ?? 'Não definida' }}</strong>
            </div>
        @endif

        <form method="POST" action="{{ route('configuracoes.provedores.update') }}" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="provedor_id" class="block text-sm font-medium text-gray-700">Provedor oficial</label>
                <select name="provedor_id" id="provedor_id" class="{{ $inputClass }}" x-model.number="provedorId" @change="sincronizarSlug()" required>
                    <option value="">Selecione</option>
                    @foreach ($provedores as $provedor)
                        <option value="{{ $provedor->id }}" data-slug="{{ $provedor->slug }}">{{ $provedor->nome }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('provedor_id')" class="mt-2" />
            </div>

            <div x-show="provedorSlug === 'meta'" x-cloak class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="meta_url_base" class="block text-sm font-medium text-gray-700">URL base da API da Meta</label>
                    <input type="url" name="meta_url_base" id="meta_url_base" x-model="form.meta_url_base" class="{{ $inputClass }}" placeholder="https://graph.facebook.com">
                    <p class="mt-1 text-xs text-gray-500">URL base da API Graph do WhatsApp Business.</p>
                    <x-input-error :messages="$errors->get('meta_url_base')" class="mt-2" />
                </div>

                <div>
                    <label for="meta_token" class="block text-sm font-medium text-gray-700">Token de acesso</label>
                    <input type="text" name="meta_token" id="meta_token" x-model="form.meta_token" class="{{ $inputClass }}" placeholder="Bearer token">
                    <x-input-error :messages="$errors->get('meta_token')" class="mt-2" />
                </div>

                <div>
                    <label for="meta_api_version" class="block text-sm font-medium text-gray-700">Versão da API</label>
                    <input type="text" name="meta_api_version" id="meta_api_version" x-model="form.meta_api_version" class="{{ $inputClass }}" placeholder="v20.0">
                    <x-input-error :messages="$errors->get('meta_api_version')" class="mt-2" />
                </div>

                <div>
                    <label for="meta_phone_number_id" class="block text-sm font-medium text-gray-700">Phone Number ID</label>
                    <input type="text" name="meta_phone_number_id" id="meta_phone_number_id" x-model="form.meta_phone_number_id" class="{{ $inputClass }}" placeholder="ID do número do WhatsApp">
                    <x-input-error :messages="$errors->get('meta_phone_number_id')" class="mt-2" />
                </div>

                <div>
                    <label for="meta_business_account_id" class="block text-sm font-medium text-gray-700">Business Account ID (opcional)</label>
                    <input type="text" name="meta_business_account_id" id="meta_business_account_id" x-model="form.meta_business_account_id" class="{{ $inputClass }}" placeholder="ID da conta business">
                    <x-input-error :messages="$errors->get('meta_business_account_id')" class="mt-2" />
                </div>
            </div>

            <div x-show="provedorSlug === 'waha'" x-cloak class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="waha_url_base" class="block text-sm font-medium text-gray-700">URL base da API WAHA</label>
                    <input type="url" name="waha_url_base" id="waha_url_base" x-model="form.waha_url_base" class="{{ $inputClass }}" placeholder="https://seu-waha.exemplo.com">
                    <x-input-error :messages="$errors->get('waha_url_base')" class="mt-2" />
                </div>

                <div>
                    <label for="waha_token" class="block text-sm font-medium text-gray-700">Token (opcional)</label>
                    <input type="text" name="waha_token" id="waha_token" x-model="form.waha_token" class="{{ $inputClass }}" placeholder="Token da API WAHA">
                    <x-input-error :messages="$errors->get('waha_token')" class="mt-2" />
                </div>

                <div>
                    <label for="waha_instancia" class="block text-sm font-medium text-gray-700">Instância / Sessão</label>
                    <input type="text" name="waha_instancia" id="waha_instancia" x-model="form.waha_instancia" class="{{ $inputClass }}" placeholder="default">
                    <x-input-error :messages="$errors->get('waha_instancia')" class="mt-2" />
                </div>
            </div>

            <div class="flex flex-wrap gap-2 pt-1">
                <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Salvar configuração
                </button>

                <button
                    type="button"
                    @click="testarConectividade()"
                    :disabled="loadingConectividade"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-text="loadingConectividade ? 'Testando...' : 'Testar conectividade'"></span>
                </button>

                <button
                    type="button"
                    @click="abrirModalTesteEnvio()"
                    class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-100"
                >
                    Testar envio
                </button>
            </div>
        </form>
    </div>

    <div
        x-show="statusMensagem !== ''"
        x-cloak
        class="rounded-lg border px-4 py-3 text-sm"
        :class="classeStatus()"
    >
        <span class="font-semibold uppercase text-xs" x-text="statusAtual || 'pendente'"></span>
        <p class="mt-1" x-text="statusMensagem"></p>
    </div>

    <div
        x-show="modalAberto"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
        @keydown.escape.window="fecharModalTesteEnvio()"
    >
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-lg">
            <h4 class="text-base font-semibold text-slate-900">Testar envio de mensagem</h4>
            <p class="mt-1 text-sm text-slate-600">Informe número e mensagem para validar o envio com o provedor atual.</p>

            <div class="mt-4 space-y-3">
                <div>
                    <label for="numero_teste_envio" class="block text-sm font-medium text-gray-700">Número</label>
                    <input id="numero_teste_envio" type="text" x-model="numeroTeste" class="{{ $inputClass }}" placeholder="Ex.: 5565999999999">
                </div>

                <div>
                    <label for="mensagem_teste_envio" class="block text-sm font-medium text-gray-700">Mensagem</label>
                    <textarea id="mensagem_teste_envio" rows="4" x-model="mensagemTeste" class="{{ $inputClass }}" placeholder="Mensagem de teste"></textarea>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-end gap-2">
                <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="fecharModalTesteEnvio()">
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loadingEnvio"
                    @click="testarEnvio()"
                >
                    <span x-text="loadingEnvio ? 'Enviando...' : 'Enviar teste'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
