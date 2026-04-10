<?php

namespace App\Jobs;

use App\Models\ConfiguracaoProvedorMensagem;
use App\Services\ConfiguracaoAsyncTaskService;
use App\Services\ProvedorWhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessarTesteConectividadeProvedorJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
        public int $configuracaoProvedorMensagemId
    ) {}

    public function handle(
        ConfiguracaoAsyncTaskService $taskService,
        ProvedorWhatsappService $provedorWhatsappService
    ): void {
        $taskService->marcarProcessando($this->token, 'Testando conectividade da API...');

        $configuracao = ConfiguracaoProvedorMensagem::query()
            ->with('provedor:id,nome,slug,tipo')
            ->find($this->configuracaoProvedorMensagemId);

        if (! $configuracao) {
            $taskService->marcarFalha($this->token, 'Configuração de provedor não encontrada.');

            return;
        }

        try {
            $provedorWhatsappService->testarConectividadeApi($configuracao);

            $taskService->marcarSucesso(
                $this->token,
                'Conectividade da API validada com sucesso.',
                ['configuracao_id' => $configuracao->id]
            );
        } catch (Throwable $exception) {
            Log::error('Falha no teste de conectividade do provedor de WhatsApp.', [
                'token' => $this->token,
                'configuracao_id' => $configuracao->id,
                'provedor' => $configuracao->provedor?->slug,
                'erro' => $exception->getMessage(),
            ]);

            $taskService->marcarFalha(
                $this->token,
                'Falha ao testar conectividade da API. Verifique URL, token e parâmetros do provedor.'
            );
        }
    }
}

