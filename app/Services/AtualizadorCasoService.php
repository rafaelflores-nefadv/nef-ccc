<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\RegraSubstatus;
use App\Models\TipoSubstatus;
use Carbon\Carbon;

class AtualizadorCasoService
{
    public function __construct(
        protected CalculadoraPrazoService $calculadoraPrazoService
    ) {
    }

    /**
     * @param array<string, mixed> $dadosAndamento
     */
    public function atualizarAposAndamento(Caso $caso, array $dadosAndamento): void
    {
        $dataAndamento = Carbon::parse((string) $dadosAndamento['data_andamento']);
        $regraSubstatus = $this->buscarRegraSubstatus((int) $dadosAndamento['tipo_substatus_id']);
        $prazoCalculado = $this->calculadoraPrazoService->calcular(
            (string) $dadosAndamento['data_andamento'],
            $regraSubstatus,
            $caso
        );

        $atualizacoes = [
            'tipo_status_id' => $dadosAndamento['tipo_status_id'],
            'tipo_substatus_id' => $dadosAndamento['tipo_substatus_id'],
            'data_alteracao_status' => $dataAndamento->toDateString(),
            'data_alteracao_substatus' => $dataAndamento->toDateString(),
            'data_ultimo_andamento' => $dataAndamento->toDateTimeString(),
        ];

        if ($prazoCalculado) {
            $atualizacoes['data_prazo'] = $prazoCalculado->toDateString();
        } elseif (! empty($dadosAndamento['data_prazo'])) {
            $atualizacoes['data_prazo'] = $dadosAndamento['data_prazo'];
        }

        if (! empty($dadosAndamento['data_primeiro_leilao'])) {
            $atualizacoes['data_primeiro_leilao'] = $dadosAndamento['data_primeiro_leilao'];
        }

        if (! empty($dadosAndamento['data_segundo_leilao'])) {
            $atualizacoes['data_segundo_leilao'] = $dadosAndamento['data_segundo_leilao'];
        }

        $caso->update($atualizacoes);
    }

    protected function buscarRegraSubstatus(?int $tipoSubstatusId): ?RegraSubstatus
    {
        if (! $tipoSubstatusId) {
            return null;
        }

        $tipoSubstatus = TipoSubstatus::query()
            ->with('regraSubstatus')
            ->find($tipoSubstatusId);

        return $tipoSubstatus?->regraSubstatus;
    }
}
