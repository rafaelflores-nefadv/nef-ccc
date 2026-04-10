@php
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
@endphp

<form method="POST" action="{{ route('configuracoes.notificacoes.update') }}" class="space-y-5">
    @csrf
    @method('PATCH')

    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        Os destinatários de notificações são definidos automaticamente com base nos usuários ativos cadastrados no sistema.
        <div class="mt-1 font-semibold">
            Usuários ativos considerados atualmente: {{ $totalDestinatariosNotificacao }}
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="canal_email_ativo" value="0">
            <input type="checkbox" name="canal_email_ativo" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked((bool) old('canal_email_ativo', $configuracaoNotificacao->canal_email_ativo))>
            Ativar canal de e-mail
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="canal_whatsapp_ativo" value="0">
            <input type="checkbox" name="canal_whatsapp_ativo" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked((bool) old('canal_whatsapp_ativo', $configuracaoNotificacao->canal_whatsapp_ativo))>
            Ativar canal de WhatsApp
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="notificar_prazo_vencendo" value="0">
            <input type="checkbox" name="notificar_prazo_vencendo" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked((bool) old('notificar_prazo_vencendo', $configuracaoNotificacao->notificar_prazo_vencendo))>
            Notificar prazo vencendo
        </label>

        <div>
            <label for="dias_antes_prazo" class="block text-sm font-medium text-gray-700">Dias antes do prazo</label>
            <input type="number" min="0" name="dias_antes_prazo" id="dias_antes_prazo" value="{{ old('dias_antes_prazo', $configuracaoNotificacao->dias_antes_prazo) }}" class="{{ $inputClass }}" required>
            <x-input-error :messages="$errors->get('dias_antes_prazo')" class="mt-2" />
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="notificar_prazo_vencido" value="0">
            <input type="checkbox" name="notificar_prazo_vencido" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked((bool) old('notificar_prazo_vencido', $configuracaoNotificacao->notificar_prazo_vencido))>
            Notificar prazo vencido
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="notificar_leilao" value="0">
            <input type="checkbox" name="notificar_leilao" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked((bool) old('notificar_leilao', $configuracaoNotificacao->notificar_leilao))>
            Notificar leilão
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700 md:col-span-2">
            <input type="hidden" name="notificar_novo_andamento" value="0">
            <input type="checkbox" name="notificar_novo_andamento" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked((bool) old('notificar_novo_andamento', $configuracaoNotificacao->notificar_novo_andamento))>
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

