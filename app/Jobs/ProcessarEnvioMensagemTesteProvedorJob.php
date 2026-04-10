<?php

namespace App\Jobs;

use App\Models\ConfiguracaoProvedorMensagem;
use App\Services\ConfiguracaoAsyncTaskService;
use App\Services\ProvedorWhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessarEnvioMensagemTesteProvedorJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
        public int $configuracaoProvedorMensagemId,
        public string $numero,
        public string $mensagem
    ) {}

    public function handle(
        ConfiguracaoAsyncTaskService $taskService,
        ProvedorWhatsappService $provedorWhatsappService
    ): void {
        $taskService->marcarProcessando($this->token, 'Enviando mensagem de teste...');

        $configuracao = ConfiguracaoProvedorMensagem::query()
            ->with('provedor:id,nome,slug,tipo')
            ->find($this->configuracaoProvedorMensagemId);

        if (! $configuracao) {
            $taskService->marcarFalha($this->token, 'Configuração de provedor não encontrada.');

            return;
        }

        try {
            $provedorWhatsappService->enviarMensagemTeste($configuracao, $this->numero, $this->mensagem);

            $taskService->marcarSucesso(
                $this->token,
                'Mensagem de teste enviada com sucesso.',
                [
                    'configuracao_id' => $configuracao->id,
                    'numero' => $this->numero,
                ]
            );
        } catch (Throwable $exception) {
            Log::error('Falha ao enviar mensagem de teste pelo provedor de WhatsApp.', [
                'token' => $this->token,
                'configuracao_id' => $configuracao->id,
                'provedor' => $configuracao->provedor?->slug,
                'numero' => $this->numero,
                'erro' => $exception->getMessage(),
            ]);

            $taskService->marcarFalha(
                $this->token,
                'Falha ao enviar mensagem de teste. Revise os dados e tente novamente.'
            );
        }
    }
}

