<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\ConfiguracaoNotificacao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PrazoCasoService
{
    public const STATUS_DENTRO_DO_PRAZO = 'dentro_do_prazo';
    public const STATUS_IGUAL_AO_PRAZO = 'igual_ao_prazo';
    public const STATUS_PRAZO_VENCIDO = 'prazo_vencido';
    public const STATUS_SEM_DISTRIBUICAO = 'sem_distribuicao';

    protected static ?int $diasAntesPrazoCache = null;

    /**
     * @return array{
     *   status: string,
     *   data_limite: ?Carbon,
     *   dias_restantes: ?int,
     *   dias_atraso: ?int,
     *   dias_configurados: int
     * }
     */
    public function calcular(Caso $caso): array
    {
        $diasConfigurados = $this->obterDiasAntesPrazo();
        $distribuicao = $this->normalizarDistribuicao($caso);

        if (! $distribuicao) {
            return [
                'status' => self::STATUS_SEM_DISTRIBUICAO,
                'data_limite' => null,
                'dias_restantes' => null,
                'dias_atraso' => null,
                'dias_configurados' => $diasConfigurados,
            ];
        }

        $hoje = now()->startOfDay();
        $dataLimite = $distribuicao->copy()->startOfDay()->addDays($diasConfigurados);
        $diferencaEmDias = $hoje->diffInDays($dataLimite, false);

        $status = match (true) {
            $diferencaEmDias > 0 => self::STATUS_DENTRO_DO_PRAZO,
            $diferencaEmDias === 0 => self::STATUS_IGUAL_AO_PRAZO,
            default => self::STATUS_PRAZO_VENCIDO,
        };

        return [
            'status' => $status,
            'data_limite' => $dataLimite,
            'dias_restantes' => $diferencaEmDias >= 0 ? $diferencaEmDias : 0,
            'dias_atraso' => $diferencaEmDias < 0 ? abs($diferencaEmDias) : 0,
            'dias_configurados' => $diasConfigurados,
        ];
    }

    public function obterDiasAntesPrazo(): int
    {
        if (self::$diasAntesPrazoCache !== null) {
            return self::$diasAntesPrazoCache;
        }

        $valor = Cache::remember('configuracoes_notificacao:dias_antes_prazo', 180, function (): int {
            return (int) (ConfiguracaoNotificacao::query()->value('dias_antes_prazo') ?? 1);
        });
        self::$diasAntesPrazoCache = max(0, (int) ($valor ?? 1));

        return self::$diasAntesPrazoCache;
    }

    public function dataBasePrazo(?Carbon $referencia = null): Carbon
    {
        $base = ($referencia ?? now())->copy()->startOfDay();

        return $base->subDays($this->obterDiasAntesPrazo());
    }

    protected function normalizarDistribuicao(Caso $caso): ?Carbon
    {
        $distribuicao = $caso->distribuicao;

        if (! $distribuicao) {
            return null;
        }

        if ($distribuicao instanceof Carbon) {
            return $distribuicao->copy();
        }

        try {
            return Carbon::parse((string) $distribuicao);
        } catch (\Throwable) {
            return null;
        }
    }
}
