<?php

namespace App\Services;

use App\Jobs\EnviarEmailPrazoCasoJob;
use App\Jobs\EnviarWhatsappPrazoCasoJob;
use App\Models\Caso;
use App\Models\ConfiguracaoNotificacao;
use App\Models\PrazoNotificacaoEnvio;
use App\Models\User;
use App\Notifications\NotificacaoInterna;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PrazoNotificacaoService
{
    public const EVENTO_PRAZO_VENCENDO = 'prazo_vencendo';
    public const EVENTO_PRAZO_HOJE = 'prazo_hoje';
    public const EVENTO_PRAZO_VENCIDO = 'prazo_vencido';
    protected const RETRY_FALHA_COOLDOWN_MINUTOS = 30;

    /**
     * @var array<int, Collection<int, User>>
     */
    protected array $usuariosElegiveisPorCooperativa = [];
    /**
     * @var Collection<int, User>|null
     */
    protected ?Collection $adminsAtivosGlobais = null;

    public function __construct(
        protected ProvedorWhatsappService $provedorWhatsappService,
        protected NotificacaoEmailService $notificacaoEmailService
    ) {}

    /**
     * @return array<string, int|string>
     */
    public function executar(?Carbon $dataReferencia = null, ?int $cooperativaId = null): array
    {
        $resumo = $this->resumoBase();
        $referencia = ($dataReferencia ?? now())->copy()->startOfDay();

        $configuracao = ConfiguracaoNotificacao::query()->firstOrCreate(
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

        $diasAntesPrazo = max(0, (int) $configuracao->dias_antes_prazo);
        $resumo['configuracao'] = $this->snapshotConfiguracao($configuracao);
        $usuariosDestino = $this->usuariosDestinoConfigurados($configuracao);
        $eventos = $this->eventosAtivos($configuracao, $referencia);
        $resumo['eventos_ativos'] = collect($eventos)->pluck('tipo')->values()->all();
        $emailConfig = $this->obterConfiguracaoEmailAtiva($configuracao);
        $configuracaoWhatsappId = $this->obterConfiguracaoWhatsappAtivaId($configuracao);

        if ($eventos === []) {
            $resumo['mensagem'] = sprintf(
                'Nenhum evento de prazo ativo para notificacao. Flags atuais: notificar_prazo_vencendo=%s, notificar_prazo_vencido=%s.',
                $configuracao->notificar_prazo_vencendo ? 'true' : 'false',
                $configuracao->notificar_prazo_vencido ? 'true' : 'false',
            );

            return $resumo;
        }

        foreach ($eventos as $evento) {
            $resumo['detalhes_eventos'][$evento['tipo']] = $this->processarEvento(
                evento: $evento,
                referencia: $referencia,
                diasAntesPrazo: $diasAntesPrazo,
                usuariosDestino: $usuariosDestino,
                cooperativaId: $cooperativaId,
                emailConfig: $emailConfig,
                configuracaoWhatsappId: $configuracaoWhatsappId,
                resumo: $resumo,
            );
        }

        $resumo['mensagem'] = $this->mensagemConclusao($resumo);

        return $resumo;
    }

    /**
     * @param array{tipo:string,data_comparacao:string,operador:string} $evento
     * @param array<int> $usuariosDestino
     * @param array<string, mixed>|null $emailConfig
     * @param array<string, int|string> $resumo
     */
    protected function processarEvento(
        array $evento,
        Carbon $referencia,
        int $diasAntesPrazo,
        array $usuariosDestino,
        ?int $cooperativaId,
        ?array $emailConfig,
        ?int $configuracaoWhatsappId,
        array &$resumo,
    ): array {
        $resumoEvento = $this->resumoEventoBase($evento['tipo']);
        $expressaoDataLimite = "(date(distribuicao) + (? * interval '1 day'))::date";

        $query = Caso::query()
            ->with('responsavel')
            ->whereNotNull('distribuicao')
            ->whereRaw(
                $expressaoDataLimite.' '.$evento['operador'].' ?::date',
                [$diasAntesPrazo, $evento['data_comparacao']]
            )
            ->orderBy('id');

        if ($cooperativaId !== null) {
            $query->where('cooperativa_id', $cooperativaId);
        }

        $query->chunkById(400, function (Collection $casos) use (
            $evento,
            $referencia,
            $diasAntesPrazo,
            $usuariosDestino,
            $emailConfig,
            $configuracaoWhatsappId,
            &$resumo,
            &$resumoEvento
        ): void {
            $this->precarregarUsuariosElegiveis(
                cooperativas: $casos->pluck('cooperativa_id')->filter()->unique()->map(fn ($id): int => (int) $id)->all(),
                usuariosDestino: $usuariosDestino
            );

            foreach ($casos as $caso) {
                $resumo['casos_processados']++;
                $resumoEvento['casos_encontrados']++;

                $dataLimite = $this->dataLimiteCaso($caso, $diasAntesPrazo);

                if (! $dataLimite) {
                    continue;
                }

                $destinatarios = $this->destinatariosCaso($caso);

                if ($destinatarios->isEmpty()) {
                    $resumo['casos_sem_destinatario']++;
                    $resumoEvento['casos_sem_destinatario']++;
                    continue;
                }

                $mensagem = $this->mensagemEvento($caso, $evento['tipo'], $dataLimite, $referencia);

                foreach ($destinatarios as $destinatario) {
                    $this->notificarInterno($caso, $destinatario, $evento['tipo'], $dataLimite, $mensagem, $resumo, $resumoEvento);

                    if ($emailConfig !== null && $this->emailValido($destinatario->email)) {
                        $this->notificarEmail($caso, $destinatario, $evento['tipo'], $dataLimite, $mensagem, $emailConfig, $resumo, $resumoEvento);
                    }

                    if ($configuracaoWhatsappId !== null) {
                        $numeroWhatsapp = $this->numeroWhatsappValido($destinatario);

                        if ($numeroWhatsapp !== null) {
                            $this->notificarWhatsapp(
                                caso: $caso,
                                destinatario: $destinatario,
                                tipoEvento: $evento['tipo'],
                                dataLimite: $dataLimite,
                                mensagem: $mensagem,
                                numeroWhatsapp: $numeroWhatsapp,
                                configuracaoWhatsappId: $configuracaoWhatsappId,
                                resumo: $resumo,
                                resumoEvento: $resumoEvento,
                            );
                        }
                    }
                }
            }
        }, 'id');

        return $resumoEvento;
    }

    /**
     * @param array<string, int|string> $mensagem
     * @param array<string, int|string> $resumo
     * @param array<string, int|string> $resumoEvento
     */
    protected function notificarInterno(
        Caso $caso,
        User $destinatario,
        string $tipoEvento,
        Carbon $dataLimite,
        array $mensagem,
        array &$resumo,
        array &$resumoEvento
    ): void {
        $registro = $this->criarRegistroEnvio(
            caso: $caso,
            destinatario: $destinatario,
            canal: PrazoNotificacaoEnvio::CANAL_INTERNO,
            tipoEvento: $tipoEvento,
            dataReferencia: $dataLimite,
            statusInicial: PrazoNotificacaoEnvio::STATUS_PENDENTE,
            payload: $this->payloadResumo($caso, $destinatario, $tipoEvento, $dataLimite)
        );

        if (! $registro) {
            $resumo['envios_duplicados']++;
            $resumoEvento['envios_duplicados']++;
            return;
        }

        try {
            $destinatario->notify(new NotificacaoInterna(
                title: (string) $mensagem['titulo'],
                message: (string) $mensagem['texto'],
                url: route('casos.show', $caso),
                type: (string) $mensagem['tipo_interno'],
                icon: null,
            ));

            $this->atualizarRegistro($registro, PrazoNotificacaoEnvio::STATUS_SUCESSO, null, true);
            $resumo['internos_enviados']++;
            $resumoEvento['internos_enviados']++;
        } catch (Throwable $exception) {
            $this->atualizarRegistro(
                $registro,
                PrazoNotificacaoEnvio::STATUS_FALHA,
                Str::limit($exception->getMessage(), 2000),
                false
            );

            Log::error('Falha ao enviar notificacao interna de prazo.', [
                'caso_id' => $caso->id,
                'user_id' => $destinatario->id,
                'tipo_evento' => $tipoEvento,
                'erro' => $exception->getMessage(),
            ]);

            $resumo['falhas']++;
            $resumoEvento['falhas']++;
        }
    }

    /**
     * @param array<string, int|string> $mensagem
     * @param array<string, mixed> $emailConfig
     * @param array<string, int|string> $resumo
     * @param array<string, int|string> $resumoEvento
     */
    protected function notificarEmail(
        Caso $caso,
        User $destinatario,
        string $tipoEvento,
        Carbon $dataLimite,
        array $mensagem,
        array $emailConfig,
        array &$resumo,
        array &$resumoEvento
    ): void {
        $registro = $this->criarRegistroEnvio(
            caso: $caso,
            destinatario: $destinatario,
            canal: PrazoNotificacaoEnvio::CANAL_EMAIL,
            tipoEvento: $tipoEvento,
            dataReferencia: $dataLimite,
            statusInicial: PrazoNotificacaoEnvio::STATUS_ENFILEIRADO,
            payload: $this->payloadResumo($caso, $destinatario, $tipoEvento, $dataLimite)
        );

        if (! $registro) {
            $resumo['envios_duplicados']++;
            $resumoEvento['envios_duplicados']++;
            return;
        }

        try {
            EnviarEmailPrazoCasoJob::dispatch(
                envioId: (int) $registro->id,
                destinatario: (string) $destinatario->email,
                assunto: (string) $mensagem['assunto_email'],
                conteudo: (string) $mensagem['texto_email'],
                configuracaoEmail: $emailConfig
            );

            $resumo['emails_enfileirados']++;
            $resumoEvento['emails_enfileirados']++;
        } catch (Throwable $exception) {
            $this->atualizarRegistro(
                $registro,
                PrazoNotificacaoEnvio::STATUS_FALHA,
                Str::limit($exception->getMessage(), 2000),
                false
            );

            Log::error('Falha ao enfileirar envio de e-mail de prazo.', [
                'caso_id' => $caso->id,
                'user_id' => $destinatario->id,
                'tipo_evento' => $tipoEvento,
                'erro' => $exception->getMessage(),
            ]);

            $resumo['falhas']++;
            $resumoEvento['falhas']++;
        }
    }

    /**
     * @param array<string, int|string> $mensagem
     * @param array<string, int|string> $resumo
     * @param array<string, int|string> $resumoEvento
     */
    protected function notificarWhatsapp(
        Caso $caso,
        User $destinatario,
        string $tipoEvento,
        Carbon $dataLimite,
        array $mensagem,
        string $numeroWhatsapp,
        int $configuracaoWhatsappId,
        array &$resumo,
        array &$resumoEvento
    ): void {
        $registro = $this->criarRegistroEnvio(
            caso: $caso,
            destinatario: $destinatario,
            canal: PrazoNotificacaoEnvio::CANAL_WHATSAPP,
            tipoEvento: $tipoEvento,
            dataReferencia: $dataLimite,
            statusInicial: PrazoNotificacaoEnvio::STATUS_ENFILEIRADO,
            payload: $this->payloadResumo($caso, $destinatario, $tipoEvento, $dataLimite)
        );

        if (! $registro) {
            $resumo['envios_duplicados']++;
            $resumoEvento['envios_duplicados']++;
            return;
        }

        try {
            EnviarWhatsappPrazoCasoJob::dispatch(
                envioId: (int) $registro->id,
                configuracaoWhatsappId: $configuracaoWhatsappId,
                numeroWhatsapp: $numeroWhatsapp,
                mensagem: (string) $mensagem['texto_whatsapp']
            );

            $resumo['whatsapps_enfileirados']++;
            $resumoEvento['whatsapps_enfileirados']++;
        } catch (Throwable $exception) {
            $this->atualizarRegistro(
                $registro,
                PrazoNotificacaoEnvio::STATUS_FALHA,
                Str::limit($exception->getMessage(), 2000),
                false
            );

            Log::error('Falha ao enfileirar envio de WhatsApp de prazo.', [
                'caso_id' => $caso->id,
                'user_id' => $destinatario->id,
                'tipo_evento' => $tipoEvento,
                'erro' => $exception->getMessage(),
            ]);

            $resumo['falhas']++;
            $resumoEvento['falhas']++;
        }
    }

    /**
     * @param array<int> $cooperativas
     * @param array<int> $usuariosDestino
     */
    protected function precarregarUsuariosElegiveis(array $cooperativas, array $usuariosDestino): void
    {
        foreach ($cooperativas as $cooperativaId) {
            if (isset($this->usuariosElegiveisPorCooperativa[$cooperativaId])) {
                continue;
            }

            $query = User::query()
                ->where('ativo', true)
                ->where('perfil', '!=', User::PERFIL_ADMIN)
                ->where('cooperativa_id', $cooperativaId)
                ->orderBy('name');

            if ($usuariosDestino !== []) {
                $query->whereIn('id', $usuariosDestino);
            }

            $this->usuariosElegiveisPorCooperativa[$cooperativaId] = $query->get();
        }
    }

    /**
     * @return Collection<int, User>
     */
    protected function destinatariosCaso(Caso $caso): Collection
    {
        $destinatarios = $this->obterAdminsAtivosGlobais();
        $responsavel = $caso->responsavel;

        if (
            $responsavel instanceof User
            && $responsavel->ativo
            && ! $responsavel->isAdmin()
            && (int) $responsavel->cooperativa_id === (int) $caso->cooperativa_id
        ) {
            $destinatarios = $destinatarios->push($responsavel);
        } else {
            $destinatarios = $destinatarios->concat(
                $this->usuariosElegiveisPorCooperativa[(int) $caso->cooperativa_id] ?? collect()
            );
        }

        return $destinatarios
            ->unique('id')
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    protected function obterAdminsAtivosGlobais(): Collection
    {
        if ($this->adminsAtivosGlobais !== null) {
            return $this->adminsAtivosGlobais;
        }

        $this->adminsAtivosGlobais = User::query()
            ->where('ativo', true)
            ->where('perfil', User::PERFIL_ADMIN)
            ->orderBy('name')
            ->get();

        return $this->adminsAtivosGlobais;
    }

    protected function dataLimiteCaso(Caso $caso, int $diasAntesPrazo): ?Carbon
    {
        if (! $caso->distribuicao) {
            return null;
        }

        try {
            return Carbon::parse((string) $caso->distribuicao)->startOfDay()->addDays($diasAntesPrazo);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, int|string>
     */
    protected function mensagemEvento(Caso $caso, string $tipoEvento, Carbon $dataLimite, Carbon $referencia): array
    {
        $codigoCaso = (string) ($caso->codigo_caso ?: '#'.$caso->id);
        $nomeCaso = trim((string) ($caso->nome ?? ''));
        $nomeCaso = $nomeCaso !== '' ? $nomeCaso : 'Sem nome';
        $dataLimiteFormatada = $dataLimite->format('d/m/Y');

        if ($tipoEvento === self::EVENTO_PRAZO_VENCENDO) {
            $texto = "Caso {$codigoCaso} ({$nomeCaso}) vence em {$dataLimiteFormatada}.";

            return [
                'titulo' => 'Prazo vencendo em breve',
                'texto' => $texto,
                'assunto_email' => "Prazo vencendo em breve - caso {$codigoCaso}",
                'texto_email' => $texto,
                'texto_whatsapp' => $texto,
                'tipo_interno' => NotificacaoInterna::TYPE_WARNING,
            ];
        }

        if ($tipoEvento === self::EVENTO_PRAZO_HOJE) {
            $texto = "Caso {$codigoCaso} ({$nomeCaso}) vence hoje ({$dataLimiteFormatada}).";

            return [
                'titulo' => 'Prazo vence hoje',
                'texto' => $texto,
                'assunto_email' => "Prazo vence hoje - caso {$codigoCaso}",
                'texto_email' => $texto,
                'texto_whatsapp' => $texto,
                'tipo_interno' => NotificacaoInterna::TYPE_WARNING,
            ];
        }

        $diasAtraso = max(1, $dataLimite->diffInDays($referencia));
        $texto = "Caso {$codigoCaso} ({$nomeCaso}) esta vencido desde {$dataLimiteFormatada} ({$diasAtraso} dia(s) de atraso).";

        return [
            'titulo' => 'Prazo vencido',
            'texto' => $texto,
            'assunto_email' => "Prazo vencido - caso {$codigoCaso}",
            'texto_email' => $texto,
            'texto_whatsapp' => $texto,
            'tipo_interno' => NotificacaoInterna::TYPE_DANGER,
        ];
    }

    protected function emailValido(?string $email): bool
    {
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function numeroWhatsappValido(User $usuario): ?string
    {
        foreach (['whatsapp', 'telefone_whatsapp', 'telefone', 'celular', 'phone'] as $campo) {
            $valor = $usuario->getAttribute($campo);

            if (! is_string($valor) || trim($valor) === '') {
                continue;
            }

            $normalizado = preg_replace('/\D+/', '', $valor);

            if (! is_string($normalizado)) {
                continue;
            }

            if (strlen($normalizado) >= 10 && strlen($normalizado) <= 15) {
                return $normalizado;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function criarRegistroEnvio(
        Caso $caso,
        User $destinatario,
        string $canal,
        string $tipoEvento,
        Carbon $dataReferencia,
        string $statusInicial,
        array $payload
    ): ?PrazoNotificacaoEnvio {
        $chaveUnica = [
            'caso_id' => (int) $caso->id,
            'user_id' => (int) $destinatario->id,
            'cooperativa_id' => (int) $caso->cooperativa_id,
            'canal' => $canal,
            'tipo_evento' => $tipoEvento,
            'data_referencia' => $dataReferencia->toDateString(),
        ];

        $registroExistente = PrazoNotificacaoEnvio::query()
            ->where($chaveUnica)
            ->first();

        if ($registroExistente) {
            return $this->reanalisarRegistroExistenteParaReenvio(
                registro: $registroExistente,
                statusInicial: $statusInicial,
                payload: $payload
            );
        }

        $agora = now();

        $inseridos = DB::table('prazo_notificacao_envios')->insertOrIgnore([
            ...$chaveUnica,
            'status' => $statusInicial,
            'erro' => null,
            'payload_resumo' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'enviado_em' => null,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        if ($inseridos === 0) {
            $registroConcorrente = PrazoNotificacaoEnvio::query()
                ->where($chaveUnica)
                ->first();

            if (! $registroConcorrente) {
                return null;
            }

            return $this->reanalisarRegistroExistenteParaReenvio(
                registro: $registroConcorrente,
                statusInicial: $statusInicial,
                payload: $payload
            );
        }

        return PrazoNotificacaoEnvio::query()
            ->where($chaveUnica)
            ->first();
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function reanalisarRegistroExistenteParaReenvio(
        PrazoNotificacaoEnvio $registro,
        string $statusInicial,
        array $payload
    ): ?PrazoNotificacaoEnvio {
        if ($registro->status !== PrazoNotificacaoEnvio::STATUS_FALHA) {
            return null;
        }

        if (
            $registro->updated_at
            && $registro->updated_at->gt(now()->subMinutes(self::RETRY_FALHA_COOLDOWN_MINUTOS))
        ) {
            return null;
        }

        $registro->forceFill([
            'status' => $statusInicial,
            'erro' => null,
            'enviado_em' => null,
            'payload_resumo' => $payload,
        ])->save();

        return $registro;
    }

    protected function atualizarRegistro(
        PrazoNotificacaoEnvio $registro,
        string $status,
        ?string $erro,
        bool $marcarEnviado
    ): void {
        $registro->forceFill([
            'status' => $status,
            'erro' => $erro,
            'enviado_em' => $marcarEnviado ? now() : $registro->enviado_em,
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    protected function payloadResumo(Caso $caso, User $destinatario, string $tipoEvento, Carbon $dataLimite): array
    {
        return [
            'caso_id' => (int) $caso->id,
            'codigo_caso' => (string) $caso->codigo_caso,
            'user_id' => (int) $destinatario->id,
            'tipo_evento' => $tipoEvento,
            'data_limite' => $dataLimite->toDateString(),
        ];
    }

    /**
     * @return array<int>
     */
    protected function usuariosDestinoConfigurados(ConfiguracaoNotificacao $configuracao): array
    {
        $ids = is_array($configuracao->usuarios_destino_json)
            ? $configuracao->usuarios_destino_json
            : [];

        return collect($ids)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{tipo:string,data_comparacao:string,operador:string}>
     */
    protected function eventosAtivos(ConfiguracaoNotificacao $configuracao, Carbon $referencia): array
    {
        $eventos = [];

        if ((bool) $configuracao->notificar_prazo_vencendo) {
            $eventos[] = [
                'tipo' => self::EVENTO_PRAZO_VENCENDO,
                'data_comparacao' => $referencia->copy()->addDay()->toDateString(),
                'operador' => '=',
            ];
            $eventos[] = [
                'tipo' => self::EVENTO_PRAZO_HOJE,
                'data_comparacao' => $referencia->toDateString(),
                'operador' => '=',
            ];
        }

        if ((bool) $configuracao->notificar_prazo_vencido) {
            $eventos[] = [
                'tipo' => self::EVENTO_PRAZO_VENCIDO,
                'data_comparacao' => $referencia->copy()->subDay()->toDateString(),
                'operador' => '=',
            ];
        }

        return $eventos;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function obterConfiguracaoEmailAtiva(ConfiguracaoNotificacao $configuracao): ?array
    {
        if (! $configuracao->canal_email_ativo) {
            return null;
        }

        return $this->notificacaoEmailService->configuracaoAtivaSnapshot();
    }

    protected function obterConfiguracaoWhatsappAtivaId(ConfiguracaoNotificacao $configuracao): ?int
    {
        if (! $configuracao->canal_whatsapp_ativo) {
            return null;
        }

        $configuracaoWhatsapp = $this->provedorWhatsappService->obterConfiguracaoUnica(false);

        if (! $configuracaoWhatsapp || ! $configuracaoWhatsapp->ativo) {
            return null;
        }

        try {
            $this->provedorWhatsappService->validarConfiguracaoSuportada($configuracaoWhatsapp);
        } catch (Throwable) {
            return null;
        }

        return (int) $configuracaoWhatsapp->id;
    }

    /**
     * @return array<string, int|string>
     */
    protected function resumoBase(): array
    {
        return [
            'casos_processados' => 0,
            'casos_sem_destinatario' => 0,
            'internos_enviados' => 0,
            'emails_enfileirados' => 0,
            'whatsapps_enfileirados' => 0,
            'envios_duplicados' => 0,
            'falhas' => 0,
            'eventos_ativos' => [],
            'detalhes_eventos' => [],
            'configuracao' => [],
            'mensagem' => 'Rotina executada com sucesso.',
        ];
    }

    /**
     * @return array<string, int|string>
     */
    protected function resumoEventoBase(string $tipoEvento): array
    {
        return [
            'evento' => $tipoEvento,
            'casos_encontrados' => 0,
            'casos_sem_destinatario' => 0,
            'internos_enviados' => 0,
            'emails_enfileirados' => 0,
            'whatsapps_enfileirados' => 0,
            'envios_duplicados' => 0,
            'falhas' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function snapshotConfiguracao(ConfiguracaoNotificacao $configuracao): array
    {
        return [
            'dias_antes_prazo' => (int) $configuracao->dias_antes_prazo,
            'notificar_prazo_vencendo' => (bool) $configuracao->notificar_prazo_vencendo,
            'notificar_prazo_vencido' => (bool) $configuracao->notificar_prazo_vencido,
            'canal_email_ativo' => (bool) $configuracao->canal_email_ativo,
            'canal_whatsapp_ativo' => (bool) $configuracao->canal_whatsapp_ativo,
        ];
    }

    /**
     * @param array<string, mixed> $resumo
     */
    protected function mensagemConclusao(array $resumo): string
    {
        if ((int) $resumo['casos_processados'] === 0) {
            return 'Nenhum caso elegivel encontrado para os eventos de prazo ativos.';
        }

        if (
            (int) $resumo['internos_enviados'] === 0
            && (int) $resumo['emails_enfileirados'] === 0
            && (int) $resumo['whatsapps_enfileirados'] === 0
        ) {
            if ((int) $resumo['casos_sem_destinatario'] > 0) {
                return 'Casos encontrados, mas sem destinatarios validos para notificacao.';
            }

            if ((int) $resumo['envios_duplicados'] > 0) {
                return 'Casos encontrados, mas os envios foram ignorados por deduplicacao.';
            }
        }

        return 'Rotina executada com sucesso.';
    }
}
