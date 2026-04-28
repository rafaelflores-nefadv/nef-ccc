<?php

namespace App\Console\Commands;

use App\Models\ConfiguracaoNotificacao;
use App\Services\NotificacaoEmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TestarEmailNotificacaoCommand extends Command
{
    protected $signature = 'notificacoes:testar-email
        {email : Endereco de e-mail destinatario do teste}';

    protected $description = 'Envia um e-mail de teste usando a configuracao atual de notificacoes.';

    public function handle(NotificacaoEmailService $notificacaoEmailService): int
    {
        $destinatario = mb_strtolower(trim((string) $this->argument('email')));

        if (! filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            $this->error('Informe um e-mail valido para teste.');
            return self::FAILURE;
        }

        $configuracaoNotificacao = ConfiguracaoNotificacao::query()->firstOrCreate(
            ['id' => 1],
            [
                'canal_email_ativo' => false,
                'canal_whatsapp_ativo' => false,
                'notificar_prazo_vencendo' => false,
                'dias_antes_prazo' => 1,
                'notificar_prazo_vencido' => false,
                'notificar_leilao' => false,
                'notificar_novo_andamento' => false,
                'emails_destino_json' => [],
                'usuarios_destino_json' => [],
            ]
        );

        if (! (bool) $configuracaoNotificacao->canal_email_ativo) {
            $this->error('Canal de e-mail desativado em Configuracoes > Notificacoes.');
            return self::FAILURE;
        }

        $configuracaoEmail = $notificacaoEmailService->configuracaoAtivaSnapshot();

        if ($configuracaoEmail === null) {
            $this->error('Nao existe configuracao de e-mail ativa em Configuracoes > E-mail.');
            return self::FAILURE;
        }

        $assunto = '[Teste] Notificacoes do sistema';
        $conteudo = implode("\n", [
            'Teste de envio de notificacao por e-mail.',
            'Data/hora: '.now()->format('d/m/Y H:i:s'),
            'Aplicacao: '.config('app.name', 'Sistema'),
            'Token: '.Str::upper(Str::random(8)),
        ]);

        try {
            $notificacaoEmailService->enviar(
                dados: $configuracaoEmail,
                destinatario: $destinatario,
                assunto: $assunto,
                conteudo: $conteudo
            );

            Log::info('E-mail de teste de notificacoes enviado.', [
                'tipo' => 'teste_email_notificacao',
                'email' => $destinatario,
                'canal_email_ativo' => (bool) $configuracaoNotificacao->canal_email_ativo,
            ]);

            $this->info('E-mail de teste enviado com sucesso para: '.$destinatario);
            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Falha ao enviar e-mail de teste de notificacoes.', [
                'tipo' => 'teste_email_notificacao',
                'email' => $destinatario,
                'erro' => $exception->getMessage(),
            ]);

            $this->error('Falha no envio do e-mail de teste: '.$exception->getMessage());
            return self::FAILURE;
        }
    }
}

