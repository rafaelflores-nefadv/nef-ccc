<?php

namespace App\Console\Commands;

use App\Services\PrazoNotificacaoService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class VerificarPrazosCasosCommand extends Command
{
    protected $signature = 'prazos:verificar {--data= : Data de referencia no formato Y-m-d}
        {--cooperativa= : ID da cooperativa para execucao parcial}';

    protected $description = 'Verifica prazos dos casos por distribuicao e dispara notificacoes por canal.';

    public function handle(PrazoNotificacaoService $prazoNotificacaoService): int
    {
        $dataReferencia = $this->resolverDataReferencia((string) $this->option('data'));
        $cooperativaId = $this->resolverCooperativaId($this->option('cooperativa'));

        if ($dataReferencia === null || $cooperativaId === false) {
            return self::FAILURE;
        }

        $this->info('Iniciando verificacao sistemica de prazos...');

        $resumo = $prazoNotificacaoService->executar(
            dataReferencia: $dataReferencia,
            cooperativaId: $cooperativaId === null ? null : (int) $cooperativaId
        );

        /** @var array<string, mixed> $configuracao */
        $configuracao = is_array($resumo['configuracao'] ?? null) ? $resumo['configuracao'] : [];
        $eventosAtivos = is_array($resumo['eventos_ativos'] ?? null) ? $resumo['eventos_ativos'] : [];

        $this->table(
            ['Configuracao', 'Valor'],
            [
                ['dias_antes_prazo', (string) ($configuracao['dias_antes_prazo'] ?? '-')],
                ['notificar_prazo_vencendo', $this->boolToText((bool) ($configuracao['notificar_prazo_vencendo'] ?? false))],
                ['notificar_prazo_vencido', $this->boolToText((bool) ($configuracao['notificar_prazo_vencido'] ?? false))],
                ['canal_email_ativo', $this->boolToText((bool) ($configuracao['canal_email_ativo'] ?? false))],
                ['canal_whatsapp_ativo', $this->boolToText((bool) ($configuracao['canal_whatsapp_ativo'] ?? false))],
                ['eventos_ativos', $eventosAtivos === [] ? '(nenhum)' : implode(', ', $eventosAtivos)],
            ]
        );

        $this->table(
            ['Metrica', 'Valor'],
            [
                ['Casos processados', (string) $resumo['casos_processados']],
                ['Casos sem destinatario', (string) $resumo['casos_sem_destinatario']],
                ['Notificacoes internas enviadas', (string) $resumo['internos_enviados']],
                ['E-mails enfileirados', (string) $resumo['emails_enfileirados']],
                ['WhatsApps enfileirados', (string) $resumo['whatsapps_enfileirados']],
                ['Envios duplicados ignorados', (string) $resumo['envios_duplicados']],
                ['Falhas', (string) $resumo['falhas']],
                ['Mensagem', (string) $resumo['mensagem']],
            ]
        );

        $detalhesEventos = is_array($resumo['detalhes_eventos'] ?? null) ? $resumo['detalhes_eventos'] : [];

        if ($detalhesEventos !== []) {
            $linhas = [];

            foreach ($detalhesEventos as $nomeEvento => $detalhes) {
                if (! is_array($detalhes)) {
                    continue;
                }

                $linhas[] = [
                    (string) $nomeEvento,
                    (string) ($detalhes['casos_encontrados'] ?? 0),
                    (string) ($detalhes['casos_sem_destinatario'] ?? 0),
                    (string) ($detalhes['internos_enviados'] ?? 0),
                    (string) ($detalhes['emails_enfileirados'] ?? 0),
                    (string) ($detalhes['whatsapps_enfileirados'] ?? 0),
                    (string) ($detalhes['envios_duplicados'] ?? 0),
                    (string) ($detalhes['falhas'] ?? 0),
                ];
            }

            if ($linhas !== []) {
                $this->table(
                    ['Evento', 'Casos', 'Sem Dest', 'Interno', 'Email', 'WhatsApp', 'Duplicado', 'Falha'],
                    $linhas
                );
            }
        }

        $this->info('Verificacao concluida.');

        return self::SUCCESS;
    }

    protected function resolverDataReferencia(string $dataOpcional): ?Carbon
    {
        $dataOpcional = trim($dataOpcional);

        if ($dataOpcional === '') {
            return now()->startOfDay();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $dataOpcional)->startOfDay();
        } catch (Throwable) {
            $this->error('Parametro --data invalido. Use o formato Y-m-d, ex.: 2026-04-10.');

            return null;
        }
    }

    protected function resolverCooperativaId(mixed $cooperativaOpcional): int|false|null
    {
        if ($cooperativaOpcional === null || $cooperativaOpcional === '') {
            return null;
        }

        if (! is_numeric($cooperativaOpcional) || (int) $cooperativaOpcional <= 0) {
            $this->error('Parametro --cooperativa invalido. Informe um ID inteiro positivo.');

            return false;
        }

        return (int) $cooperativaOpcional;
    }

    protected function boolToText(bool $valor): string
    {
        return $valor ? 'true' : 'false';
    }
}
