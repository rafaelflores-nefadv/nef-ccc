<?php

namespace App\Services;

use App\Models\ConfiguracaoEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

class NotificacaoEmailService
{
    /**
     * @return array<string, mixed>|null
     */
    public function configuracaoAtivaSnapshot(): ?array
    {
        $configuracao = ConfiguracaoEmail::query()
            ->where('ativo', true)
            ->first();

        if (! $configuracao) {
            return null;
        }

        return [
            'driver' => (string) $configuracao->driver,
            'host' => (string) $configuracao->host,
            'porta' => (int) $configuracao->porta,
            'usuario' => (string) $configuracao->usuario,
            'senha' => (string) ($configuracao->senha ?? ''),
            'criptografia' => $configuracao->criptografia ? (string) $configuracao->criptografia : null,
            'email_remetente' => (string) $configuracao->email_remetente,
            'nome_remetente' => (string) $configuracao->nome_remetente,
            'ativo' => (bool) $configuracao->ativo,
        ];
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function enviar(array $dados, string $destinatario, string $assunto, string $conteudo): void
    {
        $nomeMailer = 'notificacao_prazo_'.Str::lower(Str::random(10));
        $driver = strtolower((string) ($dados['driver'] ?? 'smtp'));
        $dadosNormalizados = $this->normalizarConfiguracaoSmtp($driver, $dados);

        Config::set("mail.mailers.$nomeMailer", $this->configuracaoMailer($driver, $dadosNormalizados));

        Mail::mailer($nomeMailer)->raw($conteudo, function ($message) use ($dadosNormalizados, $destinatario, $assunto): void {
            $message->to($destinatario);
            $message->subject($assunto);
            $message->from(
                (string) $dadosNormalizados['email_remetente'],
                (string) $dadosNormalizados['nome_remetente']
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

        throw new InvalidArgumentException('Driver de e-mail nao suportado para notificacao.');
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    protected function normalizarConfiguracaoSmtp(string $driver, array $dados): array
    {
        if ($driver !== 'smtp') {
            return $dados;
        }

        $host = mb_strtolower(trim((string) ($dados['host'] ?? '')));

        if ($host !== 'smtp.titan.email') {
            return $dados;
        }

        $usuario = mb_strtolower(trim((string) ($dados['usuario'] ?? '')));
        $porta = (int) ($dados['porta'] ?? 0);

        if ($usuario !== '') {
            $dados['email_remetente'] = $usuario;
        }

        if ($porta === 587) {
            $dados['criptografia'] = 'tls';
        }

        if ($porta === 465) {
            $dados['criptografia'] = 'ssl';
        }

        return $dados;
    }
}

