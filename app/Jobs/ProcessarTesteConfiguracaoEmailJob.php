<?php

namespace App\Jobs;

use App\Services\ConfiguracaoAsyncTaskService;
use App\Services\ConfiguracaoEmailTesteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessarTesteConfiguracaoEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $dadosEmail
     */
    public function __construct(
        public string $token,
        public array $dadosEmail,
        public string $destinatario
    ) {}

    public function handle(
        ConfiguracaoAsyncTaskService $taskService,
        ConfiguracaoEmailTesteService $emailTesteService
    ): void {
        $taskService->marcarProcessando($this->token, 'Processando teste de e-mail...');

        try {
            $emailTesteService->enviarTeste($this->dadosEmail, $this->destinatario);

            $taskService->marcarSucesso(
                $this->token,
                'E-mail de teste enviado com sucesso.',
                ['destinatario' => $this->destinatario]
            );
        } catch (Throwable $exception) {
            Log::error('Falha no teste de configuração de e-mail.', [
                'token' => $this->token,
                'destinatario' => $this->destinatario,
                'driver' => $this->dadosEmail['driver'] ?? null,
                'host' => $this->dadosEmail['host'] ?? null,
                'erro' => $exception->getMessage(),
            ]);

            $taskService->marcarFalha(
                $this->token,
                'Falha ao enviar e-mail de teste. Revise as configurações e tente novamente.'
            );
        }
    }
}

