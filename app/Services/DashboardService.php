<?php

namespace App\Services;

use App\Models\AndamentoCaso;
use App\Models\Caso;
use App\Models\ConfiguracaoNotificacao;
use App\Models\User;
use App\Support\EscopoCooperativa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    protected ?int $diasAntesPrazo = null;

    public function totalCasos(User $usuario): int
    {
        return $this->consultaCasos($usuario)->count();
    }

    public function casosAtivos(User $usuario): int
    {
        return $this->consultaCasos($usuario)
            ->where('arquivado', false)
            ->count();
    }

    public function casosArquivados(User $usuario): int
    {
        return $this->consultaCasos($usuario)
            ->where('arquivado', true)
            ->count();
    }

    public function casosPorStatus(User $usuario): Collection
    {
        return $this->consultaCasos($usuario)
            ->leftJoin('tipos_status', 'casos.tipo_status_id', '=', 'tipos_status.id')
            ->selectRaw("COALESCE(tipos_status.nome, 'Sem status') as nome, COUNT(*) as total")
            ->groupBy('tipos_status.nome')
            ->orderByDesc('total')
            ->get();
    }

    public function casosPorSubstatus(User $usuario): Collection
    {
        return $this->consultaCasos($usuario)
            ->leftJoin('tipos_substatus', 'casos.tipo_substatus_id', '=', 'tipos_substatus.id')
            ->selectRaw("COALESCE(tipos_substatus.nome, 'Sem substatus') as nome, COUNT(*) as total")
            ->groupBy('tipos_substatus.nome')
            ->orderByDesc('total')
            ->get();
    }

    public function prazosVencidos(User $usuario): int
    {
        $diasAntesPrazo = $this->obterDiasAntesPrazo();

        return $this->consultaCasosComPrazo($usuario)
            ->whereRaw(
                "distribuicao < (CURRENT_DATE - (? * interval '1 day'))",
                [$diasAntesPrazo]
            )
            ->count();
    }

    public function prazosHoje(User $usuario): int
    {
        $diasAntesPrazo = $this->obterDiasAntesPrazo();

        return $this->consultaCasosComPrazo($usuario)
            ->whereRaw(
                "distribuicao >= (CURRENT_DATE - (? * interval '1 day'))
                 AND distribuicao < (CURRENT_DATE - (? * interval '1 day') + interval '1 day')",
                [$diasAntesPrazo, $diasAntesPrazo]
            )
            ->count();
    }

    public function prazosProximos(User $usuario): int
    {
        $diasAntesPrazo = $this->obterDiasAntesPrazo();

        return $this->consultaCasosComPrazo($usuario)
            ->whereRaw(
                "distribuicao >= (CURRENT_DATE - (? * interval '1 day'))
                 AND distribuicao < (CURRENT_DATE + interval '8 day' - (? * interval '1 day'))",
                [$diasAntesPrazo, $diasAntesPrazo]
            )
            ->count();
    }

    public function casosLeilao(User $usuario): int
    {
        return $this->consultaCasos($usuario)
            ->where(function (Builder $query): void {
                $query->whereNotNull('data_primeiro_leilao')
                    ->orWhereNotNull('data_segundo_leilao');
            })
            ->count();
    }

    public function casosSemResponsavel(User $usuario): int
    {
        return $this->consultaCasos($usuario)
            ->whereNull('responsavel_id')
            ->count();
    }

    public function alertasPrazosVencidos(User $usuario, int $limite = 10): Collection
    {
        $diasAntesPrazo = $this->obterDiasAntesPrazo();

        return $this->consultaCasosComPrazo($usuario)
            ->with(['tipoStatus:id,nome'])
            ->whereRaw(
                "distribuicao < (CURRENT_DATE - (? * interval '1 day'))",
                [$diasAntesPrazo]
            )
            ->select([
                'id',
                'codigo_caso',
                'nome',
                'distribuicao',
                'tipo_status_id',
            ])
            ->selectRaw(
                "to_char((date(distribuicao) + (? * interval '1 day'))::date, 'DD/MM/YYYY') as data_limite_dashboard",
                [$diasAntesPrazo]
            )
            ->orderBy('distribuicao')
            ->limit($limite)
            ->get();
    }

    public function alertasPrazosHoje(User $usuario, int $limite = 10): Collection
    {
        $diasAntesPrazo = $this->obterDiasAntesPrazo();

        return $this->consultaCasosComPrazo($usuario)
            ->with(['tipoStatus:id,nome'])
            ->whereRaw(
                "distribuicao >= (CURRENT_DATE - (? * interval '1 day'))
                 AND distribuicao < (CURRENT_DATE - (? * interval '1 day') + interval '1 day')",
                [$diasAntesPrazo, $diasAntesPrazo]
            )
            ->select([
                'id',
                'codigo_caso',
                'nome',
                'distribuicao',
                'tipo_status_id',
            ])
            ->selectRaw(
                "to_char((date(distribuicao) + (? * interval '1 day'))::date, 'DD/MM/YYYY') as data_limite_dashboard",
                [$diasAntesPrazo]
            )
            ->orderBy('distribuicao')
            ->limit($limite)
            ->get();
    }

    public function ultimosCasos(User $usuario, int $limite = 10): Collection
    {
        return $this->consultaCasos($usuario)
            ->with([
                'cooperativa:id,nome',
                'tipoStatus:id,nome',
            ])
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get([
                'id',
                'cooperativa_id',
                'codigo_caso',
                'nome',
                'tipo_status_id',
                'created_at',
            ]);
    }

    public function ultimosAndamentos(User $usuario, int $limite = 10): Collection
    {
        $query = AndamentoCaso::query()
            ->with([
                'caso:id,codigo_caso,cooperativa_id',
                'tipoStatus:id,nome',
                'tipoSubstatus:id,nome',
            ])
            ->whereHas('caso', function (Builder $query) use ($usuario): void {
                $cooperativasIds = EscopoCooperativa::cooperativaIds($usuario);

                if ($cooperativasIds !== []) {
                    $query->whereIn('cooperativa_id', $cooperativasIds);
                }
            })
            ->orderByDesc('created_at')
            ->limit($limite);

        return $query->get([
            'id',
            'caso_id',
            'data_descricao',
            'descricao',
            'tipo_status_id',
            'tipo_substatus_id',
            'created_at',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function dadosDashboard(User $usuario): array
    {
        return [
            'totalCasos' => $this->totalCasos($usuario),
            'casosAtivos' => $this->casosAtivos($usuario),
            'casosArquivados' => $this->casosArquivados($usuario),
            'casosPorStatus' => $this->casosPorStatus($usuario),
            'casosPorSubstatus' => $this->casosPorSubstatus($usuario),
            'prazosVencidos' => $this->prazosVencidos($usuario),
            'prazosHoje' => $this->prazosHoje($usuario),
            'prazosProximos' => $this->prazosProximos($usuario),
            'casosLeilao' => $this->casosLeilao($usuario),
            'casosSemResponsavel' => $this->casosSemResponsavel($usuario),
            'alertasVencidos' => $this->alertasPrazosVencidos($usuario),
            'alertasHoje' => $this->alertasPrazosHoje($usuario),
            'ultimosCasos' => $this->ultimosCasos($usuario),
            'ultimosAndamentos' => $this->ultimosAndamentos($usuario),
        ];
    }

    protected function consultaCasos(User $usuario): Builder
    {
        $query = Caso::query();

        $cooperativasIds = EscopoCooperativa::cooperativaIds($usuario);

        if ($cooperativasIds !== []) {
            $query->whereIn('casos.cooperativa_id', $cooperativasIds);
        }

        return $query;
    }

    protected function consultaCasosComPrazo(User $usuario): Builder
    {
        return $this->consultaCasos($usuario)
            ->whereNotNull('distribuicao');
    }

    protected function obterDiasAntesPrazo(): int
    {
        if ($this->diasAntesPrazo !== null) {
            return $this->diasAntesPrazo;
        }

        $valor = Cache::remember('configuracoes_notificacao:dias_antes_prazo', 180, function (): int {
            return (int) (ConfiguracaoNotificacao::query()->value('dias_antes_prazo') ?? 1);
        });

        $this->diasAntesPrazo = max(0, (int) $valor);

        return $this->diasAntesPrazo;
    }
}
