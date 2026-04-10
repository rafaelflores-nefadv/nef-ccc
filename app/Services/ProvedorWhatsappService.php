<?php

namespace App\Services;

use App\Models\ConfiguracaoProvedorMensagem;
use App\Models\ProvedorMensagem;
use App\Support\ConfiguracaoSistema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ProvedorWhatsappService
{
    public const NOME_CONEXAO_UNICA = 'whatsapp_principal';

    public function garantirProvedoresSuportados(): void
    {
        $agora = now();

        ProvedorMensagem::query()->upsert(
            [
                [
                    'nome' => 'Meta',
                    'slug' => 'meta',
                    'tipo' => 'whatsapp',
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ],
                [
                    'nome' => 'WAHA',
                    'slug' => 'waha',
                    'tipo' => 'whatsapp',
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ],
            ],
            ['slug'],
            ['nome', 'tipo', 'ativo', 'updated_at']
        );
    }

    public function queryProvedoresSuportados(): Builder
    {
        return ProvedorMensagem::query()
            ->where('tipo', 'whatsapp')
            ->whereIn('slug', ConfiguracaoSistema::slugsProvedoresWhatsappSuportados());
    }

    public function listaProvedoresSuportados(): Collection
    {
        return $this->queryProvedoresSuportados()
            ->orderByRaw("CASE WHEN slug = 'meta' THEN 0 WHEN slug = 'waha' THEN 1 ELSE 99 END")
            ->orderBy('nome')
            ->get(['id', 'nome', 'slug', 'tipo', 'ativo']);
    }

    public function obterConfiguracaoUnica(bool $criarSeNaoExistir = false): ?ConfiguracaoProvedorMensagem
    {
        $configuracao = ConfiguracaoProvedorMensagem::query()
            ->with('provedor:id,nome,slug,tipo')
            ->where('nome_conexao', self::NOME_CONEXAO_UNICA)
            ->first();

        if (! $configuracao) {
            $configuracao = ConfiguracaoProvedorMensagem::query()
                ->with('provedor:id,nome,slug,tipo')
                ->whereHas('provedor', function ($query): void {
                    $query->where('tipo', 'whatsapp')
                        ->whereIn('slug', ConfiguracaoSistema::slugsProvedoresWhatsappSuportados());
                })
                ->orderByDesc('updated_at')
                ->first();
        }

        if (! $configuracao && $criarSeNaoExistir) {
            $meta = $this->queryProvedoresSuportados()->where('slug', 'meta')->first();

            if (! $meta) {
                return null;
            }

            $configuracao = ConfiguracaoProvedorMensagem::query()->create([
                'provedor_id' => (int) $meta->id,
                'nome_conexao' => self::NOME_CONEXAO_UNICA,
                'url_base' => 'https://graph.facebook.com',
                'token' => null,
                'instancia' => null,
                'configuracoes_json' => [
                    'meta_api_version' => 'v20.0',
                    'meta_phone_number_id' => '',
                    'meta_business_account_id' => '',
                ],
                'ativo' => true,
                'padrao' => true,
            ]);

            $configuracao->load('provedor:id,nome,slug,tipo');
        }

        if ($configuracao && $configuracao->nome_conexao !== self::NOME_CONEXAO_UNICA) {
            $configuracao->update(['nome_conexao' => self::NOME_CONEXAO_UNICA]);
        }

        return $configuracao;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function salvarConfiguracaoUnica(array $dados): ConfiguracaoProvedorMensagem
    {
        $provedor = $this->queryProvedoresSuportados()
            ->whereKey((int) ($dados['provedor_id'] ?? 0))
            ->first();

        if (! $provedor) {
            throw new RuntimeException('Selecione um provedor de WhatsApp suportado.');
        }

        $payload = $this->payloadSalvar($dados, (string) $provedor->slug);

        return DB::transaction(function () use ($payload): ConfiguracaoProvedorMensagem {
            $configuracao = $this->obterConfiguracaoUnica(false);

            if (! $configuracao) {
                $configuracao = ConfiguracaoProvedorMensagem::query()->create($payload);
            } else {
                $configuracao->update($payload);
            }

            ConfiguracaoProvedorMensagem::query()
                ->whereKeyNot($configuracao->id)
                ->delete();

            return $configuracao->fresh(['provedor:id,nome,slug,tipo']);
        });
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function dadosFormulario(array $dados = [], ?ConfiguracaoProvedorMensagem $configuracao = null): array
    {
        $provedorId = (int) ($dados['provedor_id'] ?? $configuracao?->provedor_id ?? 0);
        $provedorSlug = null;

        if ($provedorId > 0) {
            $provedorSlug = $this->queryProvedoresSuportados()
                ->whereKey($provedorId)
                ->value('slug');
        }

        if (! $provedorSlug && $configuracao?->relationLoaded('provedor')) {
            $provedorSlug = $configuracao->provedor?->slug;
        }

        $json = is_array($configuracao?->configuracoes_json) ? $configuracao->configuracoes_json : [];

        $metaUrl = (string) ($dados['meta_url_base'] ?? ($provedorSlug === 'meta' ? ($configuracao?->url_base ?? '') : 'https://graph.facebook.com'));
        $metaToken = (string) ($dados['meta_token'] ?? ($provedorSlug === 'meta' ? ($configuracao?->token ?? '') : ''));
        $metaPhoneNumberId = (string) ($dados['meta_phone_number_id'] ?? ($json['meta_phone_number_id'] ?? ''));
        $metaBusinessAccountId = (string) ($dados['meta_business_account_id'] ?? ($json['meta_business_account_id'] ?? ''));
        $metaApiVersion = (string) ($dados['meta_api_version'] ?? ($json['meta_api_version'] ?? 'v20.0'));

        $wahaUrl = (string) ($dados['waha_url_base'] ?? ($provedorSlug === 'waha' ? ($configuracao?->url_base ?? '') : ''));
        $wahaToken = (string) ($dados['waha_token'] ?? ($provedorSlug === 'waha' ? ($configuracao?->token ?? '') : ''));
        $wahaInstancia = (string) ($dados['waha_instancia'] ?? ($provedorSlug === 'waha' ? ($configuracao?->instancia ?? '') : ''));

        return [
            'provedor_id' => $provedorId,
            'provedor_slug' => $provedorSlug ?: 'meta',
            'meta_url_base' => $metaUrl,
            'meta_token' => $metaToken,
            'meta_phone_number_id' => $metaPhoneNumberId,
            'meta_business_account_id' => $metaBusinessAccountId,
            'meta_api_version' => $metaApiVersion,
            'waha_url_base' => $wahaUrl,
            'waha_token' => $wahaToken,
            'waha_instancia' => $wahaInstancia,
        ];
    }

    public function validarConfiguracaoSuportada(ConfiguracaoProvedorMensagem $configuracao): void
    {
        $configuracao->loadMissing('provedor:id,slug,nome,tipo');

        $slug = (string) ($configuracao->provedor?->slug ?? '');
        $tipo = (string) ($configuracao->provedor?->tipo ?? '');

        if ($tipo !== 'whatsapp' || ! in_array($slug, ConfiguracaoSistema::slugsProvedoresWhatsappSuportados(), true)) {
            throw new RuntimeException('O provedor configurado não é suportado. Utilize Meta ou WAHA.');
        }
    }

    public function testarConectividadeApi(ConfiguracaoProvedorMensagem $configuracao): void
    {
        $this->validarConfiguracaoSuportada($configuracao);

        $tentativas = $this->tentativasConectividade($configuracao);
        $erros = [];

        foreach ($tentativas as $tentativa) {
            try {
                $response = $this->requestApi(
                    $configuracao,
                    (string) $tentativa['method'],
                    (string) $tentativa['endpoint'],
                    (array) ($tentativa['payload'] ?? []),
                    (string) ($tentativa['payload_type'] ?? 'none')
                );

                if ($response->successful()) {
                    return;
                }

                $erros[] = sprintf('%s %s -> HTTP %s', $tentativa['method'], $tentativa['endpoint'], $response->status());
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                $erros[] = sprintf('%s %s -> %s', $tentativa['method'], $tentativa['endpoint'], $exception->getMessage());
            }
        }

        throw new RuntimeException('Não foi possível conectar com a API do provedor. '.implode(' | ', $erros));
    }

    public function enviarMensagemTeste(ConfiguracaoProvedorMensagem $configuracao, string $numero, string $texto): void
    {
        $this->enviarMensagem($configuracao, $numero, $texto);
    }

    public function enviarMensagem(ConfiguracaoProvedorMensagem $configuracao, string $numero, string $texto): void
    {
        $this->validarConfiguracaoSuportada($configuracao);

        $tentativas = $this->tentativasEnvioMensagem($configuracao, $numero, $texto);
        $erros = [];

        foreach ($tentativas as $tentativa) {
            try {
                $response = $this->requestApi(
                    $configuracao,
                    (string) $tentativa['method'],
                    (string) $tentativa['endpoint'],
                    (array) ($tentativa['payload'] ?? []),
                    (string) ($tentativa['payload_type'] ?? 'json')
                );

                if ($response->successful()) {
                    return;
                }

                $erros[] = sprintf('%s %s -> HTTP %s', $tentativa['method'], $tentativa['endpoint'], $response->status());
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                $erros[] = sprintf('%s %s -> %s', $tentativa['method'], $tentativa['endpoint'], $exception->getMessage());
            }
        }

        throw new RuntimeException('Não foi possível enviar a mensagem de teste. '.implode(' | ', $erros));
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    protected function payloadSalvar(array $dados, string $provedorSlug): array
    {
        if ($provedorSlug === 'meta') {
            $apiVersion = (string) ($dados['meta_api_version'] ?? 'v20.0');

            return [
                'provedor_id' => (int) $dados['provedor_id'],
                'nome_conexao' => self::NOME_CONEXAO_UNICA,
                'url_base' => (string) $dados['meta_url_base'],
                'token' => (string) $dados['meta_token'],
                'instancia' => null,
                'configuracoes_json' => [
                    'meta_api_version' => $apiVersion,
                    'meta_phone_number_id' => (string) $dados['meta_phone_number_id'],
                    'meta_business_account_id' => (string) ($dados['meta_business_account_id'] ?? ''),
                ],
                'ativo' => true,
                'padrao' => true,
            ];
        }

        return [
            'provedor_id' => (int) $dados['provedor_id'],
            'nome_conexao' => self::NOME_CONEXAO_UNICA,
            'url_base' => (string) $dados['waha_url_base'],
            'token' => (string) ($dados['waha_token'] ?? ''),
            'instancia' => (string) $dados['waha_instancia'],
            'configuracoes_json' => [
                'waha_session' => (string) $dados['waha_instancia'],
            ],
            'ativo' => true,
            'padrao' => true,
        ];
    }

    /**
     * @return list<array{method:string,endpoint:string,payload?:array<string,mixed>,payload_type?:string}>
     */
    protected function tentativasConectividade(ConfiguracaoProvedorMensagem $configuracao): array
    {
        $json = $configuracao->configuracoes_json ?? [];
        $custom = $json['health_endpoint'] ?? null;

        if (is_string($custom) && trim($custom) !== '') {
            return [
                [
                    'method' => strtoupper((string) ($json['health_method'] ?? 'GET')),
                    'endpoint' => trim($custom),
                    'payload_type' => 'none',
                ],
            ];
        }

        $slug = (string) ($configuracao->provedor?->slug ?? '');

        if ($slug === 'meta') {
            $versao = (string) ($json['meta_api_version'] ?? 'v20.0');
            $numeroId = (string) ($json['meta_phone_number_id'] ?? '');

            if ($numeroId !== '') {
                return [
                    ['method' => 'GET', 'endpoint' => sprintf('/%s/%s', trim($versao, '/'), $numeroId), 'payload_type' => 'none'],
                ];
            }

            return [
                ['method' => 'GET', 'endpoint' => '/'.trim($versao, '/'), 'payload_type' => 'none'],
            ];
        }

        return [
            ['method' => 'GET', 'endpoint' => '/api/sessions', 'payload_type' => 'none'],
            ['method' => 'GET', 'endpoint' => '/sessions', 'payload_type' => 'none'],
            ['method' => 'GET', 'endpoint' => '/health', 'payload_type' => 'none'],
        ];
    }

    /**
     * @return list<array{method:string,endpoint:string,payload:array<string,mixed>,payload_type?:string}>
     */
    protected function tentativasEnvioMensagem(ConfiguracaoProvedorMensagem $configuracao, string $numero, string $texto): array
    {
        $json = $configuracao->configuracoes_json ?? [];
        $customEndpoint = $json['send_endpoint'] ?? null;

        if (is_string($customEndpoint) && trim($customEndpoint) !== '') {
            return [
                [
                    'method' => strtoupper((string) ($json['send_method'] ?? 'POST')),
                    'endpoint' => trim($customEndpoint),
                    'payload' => [
                        'numero' => $numero,
                        'mensagem' => $texto,
                    ],
                    'payload_type' => (string) ($json['send_payload_type'] ?? 'json'),
                ],
            ];
        }

        $slug = (string) ($configuracao->provedor?->slug ?? '');

        if ($slug === 'meta') {
            $versao = (string) ($json['meta_api_version'] ?? 'v20.0');
            $numeroId = trim((string) ($json['meta_phone_number_id'] ?? ''));

            if ($numeroId === '') {
                throw new RuntimeException('Phone Number ID da Meta não está configurado.');
            }

            return [
                [
                    'method' => 'POST',
                    'endpoint' => sprintf('/%s/%s/messages', trim($versao, '/'), $numeroId),
                    'payload' => [
                        'messaging_product' => 'whatsapp',
                        'to' => $numero,
                        'type' => 'text',
                        'text' => [
                            'body' => $texto,
                        ],
                    ],
                    'payload_type' => 'json',
                ],
            ];
        }

        return [
            [
                'method' => 'POST',
                'endpoint' => '/api/sendText',
                'payload' => [
                    'chatId' => $numero.'@c.us',
                    'text' => $texto,
                ],
                'payload_type' => 'json',
            ],
            [
                'method' => 'POST',
                'endpoint' => '/api/messages/text',
                'payload' => [
                    'to' => $numero.'@c.us',
                    'text' => $texto,
                ],
                'payload_type' => 'json',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function requestApi(
        ConfiguracaoProvedorMensagem $configuracao,
        string $method,
        string $endpoint,
        array $payload = [],
        string $payloadType = 'json'
    ) {
        $headers = $this->headersAutenticacao($configuracao);
        $url = $this->montarUrl($configuracao->url_base, $endpoint);

        $request = Http::acceptJson()
            ->timeout(15)
            ->connectTimeout(5)
            ->retry(1, 250)
            ->withHeaders($headers);

        $opcoes = [];

        if ($payloadType === 'json') {
            $opcoes['json'] = $payload;
        } elseif ($payloadType === 'form') {
            $opcoes['form_params'] = $payload;
        }

        return $request->send(strtoupper($method), $url, $opcoes);
    }

    /**
     * @return array<string, string>
     */
    protected function headersAutenticacao(ConfiguracaoProvedorMensagem $configuracao): array
    {
        $json = $configuracao->configuracoes_json ?? [];
        $headers = [];

        $headerCustom = $json['token_header'] ?? 'Authorization';
        $token = trim((string) ($configuracao->token ?? ''));

        if ($token !== '') {
            if (strcasecmp((string) $headerCustom, 'Authorization') === 0) {
                $headers['Authorization'] = str_starts_with(strtolower($token), 'bearer ')
                    ? $token
                    : 'Bearer '.$token;
            } else {
                $headers[(string) $headerCustom] = $token;
            }
        }

        return $headers;
    }

    protected function montarUrl(string $base, string $endpoint): string
    {
        $base = rtrim(trim($base), '/');
        $endpoint = trim($endpoint);

        if ($endpoint === '') {
            return $base;
        }

        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        return $base.'/'.ltrim($endpoint, '/');
    }
}
