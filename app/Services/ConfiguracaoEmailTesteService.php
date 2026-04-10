<?php

namespace App\Services;

use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ConfiguracaoEmailTesteService
{
    /**
     * @param array<string, mixed> $dados
     */
    public function enviarTeste(array $dados, string $destinatario): void
    {
        $nomeMailer = 'teste_configuracao_'.Str::lower(Str::random(10));
        $driver = strtolower((string) ($dados['driver'] ?? 'smtp'));

        Config::set("mail.mailers.$nomeMailer", $this->configuracaoMailer($driver, $dados));

        $assunto = 'Teste de configuração de e-mail';
        $conteudo = 'Este é um envio de teste da tela de configurações do sistema.';

        Mail::mailer($nomeMailer)->raw($conteudo, function ($message) use ($dados, $destinatario, $assunto): void {
            $message->to($destinatario);
            $message->subject($assunto);
            $message->from(
                (string) $dados['email_remetente'],
                (string) $dados['nome_remetente']
            );
        });
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    protected function configuracaoMailer(string $driver, array $dados): array
    {
        if ($driver === 'smtp') {
            return [
                'transport' => 'smtp',
                'host' => (string) $dados['host'],
                'port' => (int) $dados['porta'],
                'encryption' => $dados['criptografia'] ?: null,
                'username' => (string) $dados['usuario'],
                'password' => (string) ($dados['senha'] ?? ''),
                'timeout' => 15,
            ];
        }

        if ($driver === 'sendmail') {
            return [
                'transport' => 'sendmail',
                'path' => config('mail.mailers.sendmail.path', '/usr/sbin/sendmail -bs -i'),
            ];
        }

        if ($driver === 'log') {
            return [
                'transport' => 'log',
                'channel' => config('mail.mailers.log.channel'),
            ];
        }

        throw new InvalidArgumentException('Driver de e-mail não suportado para teste.');
    }
}

