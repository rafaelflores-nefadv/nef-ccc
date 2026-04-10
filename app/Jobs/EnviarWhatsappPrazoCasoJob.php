<?php

namespace App\Jobs;

use App\Models\ConfiguracaoProvedorMensagem;
use App\Models\PrazoNotificacaoEnvio;
use App\Services\ProvedorWhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EnviarWhatsappPrazoCasoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $envioId,
        public int $configuracaoWhatsappId,
        public string $numeroWhatsapp,
        public string $mensagem
    ) {}

    public function handle(ProvedorWhatsappService $provedorWhatsappService): void
    {
        $envio = PrazoNotificacaoEnvio::query()->find($this->envioId);

        if (! $envio) {
            return;
        }

        $configuracao = ConfiguracaoProvedorMensagem::query()
            ->with('provedor:id,nome,slug,tipo')
            ->find($this->configuracaoWhatsappId);

        if (! $configuracao) {
            $envio->forceFill([
                'status' => PrazoNotificacaoEnvio::STATUS_FALHA,
                'erro' => 'Configuracao de provedor de WhatsApp nao encontrada.',
            ])->save();

            return;
        }

        $envio->forceFill([
            'status' => PrazoNotificacaoEnvio::STATUS_PROCESSANDO,
            'erro' => null,
        ])->save();

        try {
            $provedorWhatsappService->enviarMensagem(
                configuracao: $configuracao,
                numero: $this->numeroWhatsapp,
                texto: $this->mensagem
            );

            $envio->forceFill([
                'status' => PrazoNotificacaoEnvio::STATUS_SUCESSO,
                'enviado_em' => now(),
                'erro' => null,
            ])->save();
        } catch (Throwable $exception) {
            $envio->forceFill([
                'status' => PrazoNotificacaoEnvio::STATUS_FALHA,
                'erro' => Str::limit($exception->getMessage(), 2000),
            ])->save();

            Log::error('Falha ao enviar notificacao de prazo via WhatsApp.', [
                'envio_id' => $this->envioId,
                'configuracao_whatsapp_id' => $this->configuracaoWhatsappId,
                'numero' => $this->numeroWhatsapp,
                'erro' => $exception->getMessage(),
            ]);
        }
    }
}
