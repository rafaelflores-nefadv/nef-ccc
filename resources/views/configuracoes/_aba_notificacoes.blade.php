@php
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
    $oldInput = session()->getOldInput();
    $isChecked = static function (string $campo, bool $valorAtual = false) use ($oldInput): bool {
        if (is_array($oldInput) && array_key_exists($campo, $oldInput)) {
            return filter_var($oldInput[$campo], FILTER_VALIDATE_BOOLEAN);
        }

        return $valorAtual;
    };
@endphp

<form method="POST" action="{{ route('configuracoes.notificacoes.update') }}" class="space-y-5" data-notificacoes-form="true">
    @csrf
    @method('PATCH')

    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        Os destinatários de notificações são definidos automaticamente com base nos usuários ativos cadastrados no sistema.
        <div class="mt-1 font-semibold">
            Usuários ativos considerados atualmente: {{ $totalDestinatariosNotificacao }}
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <label for="canal_email_ativo" class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input id="canal_email_ativo" type="checkbox" name="canal_email_ativo" value="1" data-notificacoes-checkbox="true" class="input-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 accent-blue-600 focus:ring-blue-500" @checked($isChecked('canal_email_ativo', (bool) $configuracaoNotificacao->canal_email_ativo))>
            Ativar canal de e-mail
        </label>

        <label for="canal_whatsapp_ativo" class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input id="canal_whatsapp_ativo" type="checkbox" name="canal_whatsapp_ativo" value="1" data-notificacoes-checkbox="true" class="input-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 accent-blue-600 focus:ring-blue-500" @checked($isChecked('canal_whatsapp_ativo', (bool) $configuracaoNotificacao->canal_whatsapp_ativo))>
            Ativar canal de WhatsApp
        </label>

        <label for="notificar_prazo_vencendo" class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input id="notificar_prazo_vencendo" type="checkbox" name="notificar_prazo_vencendo" value="1" data-notificacoes-checkbox="true" class="input-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 accent-blue-600 focus:ring-blue-500" @checked($isChecked('notificar_prazo_vencendo', (bool) $configuracaoNotificacao->notificar_prazo_vencendo))>
            Notificar prazo vencendo
        </label>

        <div>
            <label for="dias_antes_prazo" class="block text-sm font-medium text-gray-700">Dias antes do prazo</label>
            <input type="number" min="0" name="dias_antes_prazo" id="dias_antes_prazo" value="{{ old('dias_antes_prazo', $configuracaoNotificacao->dias_antes_prazo) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('dias_antes_prazo')" class="mt-2" />
        </div>

        <label for="notificar_prazo_vencido" class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input id="notificar_prazo_vencido" type="checkbox" name="notificar_prazo_vencido" value="1" data-notificacoes-checkbox="true" class="input-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 accent-blue-600 focus:ring-blue-500" @checked($isChecked('notificar_prazo_vencido', (bool) $configuracaoNotificacao->notificar_prazo_vencido))>
            Notificar prazo vencido
        </label>

        <label for="notificar_leilao" class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input id="notificar_leilao" type="checkbox" name="notificar_leilao" value="1" data-notificacoes-checkbox="true" class="input-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 accent-blue-600 focus:ring-blue-500" @checked($isChecked('notificar_leilao', (bool) $configuracaoNotificacao->notificar_leilao))>
            Notificar leilão
        </label>

        <label for="notificar_novo_andamento" class="inline-flex items-center gap-2 text-sm text-gray-700 md:col-span-2">
            <input id="notificar_novo_andamento" type="checkbox" name="notificar_novo_andamento" value="1" data-notificacoes-checkbox="true" class="input-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 accent-blue-600 focus:ring-blue-500" @checked($isChecked('notificar_novo_andamento', (bool) $configuracaoNotificacao->notificar_novo_andamento))>
            Notificar novo andamento
        </label>
    </div>

    <div>
        <button type="submit" data-loading-text="Salvando..." class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
            Salvar configurações de notificações
        </button>
    </div>
</form>
