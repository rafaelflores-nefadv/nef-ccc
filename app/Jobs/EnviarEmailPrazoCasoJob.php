<?php

namespace App\Jobs;

use App\Models\PrazoNotificacaoEnvio;
use App\Services\NotificacaoEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EnviarEmailPrazoCasoJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $configuracaoEmail
     */
    public function __construct(
        public int $envioId,
        public string $destinatario,
        public string $assunto,
        public string $conteudo,
        public array $configuracaoEmail
    ) {}

    public function handle(NotificacaoEmailService $notificacaoEmailService): void
    {
        $envio = PrazoNotificacaoEnvio::query()->find($this->envioId);

        if (! $envio) {
            return;
        }

        $envio->forceFill([
            'status' => PrazoNotificacaoEnvio::STATUS_PROCESSANDO,
            'erro' => null,
        ])->save();

        try {
            $notificacaoEmailService->enviar(
                dados: $this->configuracaoEmail,
                destinatario: $this->destinatario,
                assunto: $this->assunto,
                conteudo: $this->conteudo
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

            Log::error('Falha ao enviar e-mail de notificacao de prazo.', [
                'envio_id' => $this->envioId,
                'destinatario' => $this->destinatario,
                'erro' => $exception->getMessage(),
            ]);
        }
    }
}
