<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ConfiguracaoAsyncTaskService
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_PROCESSANDO = 'processando';
    public const STATUS_SUCESSO = 'sucesso';
    public const STATUS_FALHA = 'falha';

    public const TIPOS_VALIDOS = [
        'teste_email',
        'teste_conectividade_provedor',
        'teste_mensagem_provedor',
    ];

    public function criar(string $tipo, int $userId, ?string $mensagem = null): array
    {
        $token = (string) Str::uuid();
        $agora = now()->toIso8601String();
        $payload = [
            'token' => $token,
            'tipo' => in_array($tipo, self::TIPOS_VALIDOS, true) ? $tipo : 'teste_email',
            'user_id' => $userId,
            'status' => self::STATUS_PENDENTE,
            'mensagem' => $mensagem ?? 'Solicitação recebida. Aguarde...',
            'resultado' => [],
            'criado_em' => $agora,
            'atualizado_em' => $agora,
            'finalizado_em' => null,
        ];

        Cache::put($this->cacheKey($token), $payload, now()->addMinutes(30));

        return $payload;
    }

    public function marcarProcessando(string $token, ?string $mensagem = null): void
    {
        $this->atualizar($token, function (array $payload) use ($mensagem): array {
            $payload['status'] = self::STATUS_PROCESSANDO;
            $payload['mensagem'] = $mensagem ?? 'Processando...';
            $payload['atualizado_em'] = now()->toIso8601String();

            return $payload;
        });
    }

    /**
     * @param array<string, mixed> $resultado
     */
    public function marcarSucesso(string $token, string $mensagem, array $resultado = []): void
    {
        $this->atualizar($token, function (array $payload) use ($mensagem, $resultado): array {
            $agora = now()->toIso8601String();
            $payload['status'] = self::STATUS_SUCESSO;
            $payload['mensagem'] = $mensagem;
            $payload['resultado'] = $resultado;
            $payload['atualizado_em'] = $agora;
            $payload['finalizado_em'] = $agora;

            return $payload;
        }, 60);
    }

    public function marcarFalha(string $token, string $mensagem): void
    {
        $this->atualizar($token, function (array $payload) use ($mensagem): array {
            $agora = now()->toIso8601String();
            $payload['status'] = self::STATUS_FALHA;
            $payload['mensagem'] = $mensagem;
            $payload['atualizado_em'] = $agora;
            $payload['finalizado_em'] = $agora;

            return $payload;
        }, 60);
    }

    public function obter(string $token, int $userId): ?array
    {
        $payload = Cache::get($this->cacheKey($token));

        if (! is_array($payload)) {
            return null;
        }

        if ((int) ($payload['user_id'] ?? 0) !== $userId) {
            return null;
        }

        return $payload;
    }

    /**
     * @param callable(array<string, mixed>): array<string, mixed> $mutator
     */
    protected function atualizar(string $token, callable $mutator, int $ttlMinutes = 30): void
    {
        $key = $this->cacheKey($token);
        $payload = Cache::get($key);

        if (! is_array($payload)) {
            return;
        }

        $payloadAtualizado = $mutator($payload);
        Cache::put($key, $payloadAtualizado, now()->addMinutes($ttlMinutes));
    }

    protected function cacheKey(string $token): string
    {
        return 'configuracoes:tarefas:'.$token;
    }
}

