<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\RegraSubstatus;
use Carbon\Carbon;

class CalculadoraPrazoService
{
    public function __construct(
        protected CalendarioJuridicoService $calendarioJuridicoService
    ) {
    }

    public function calcular(?string $dataBase, ?RegraSubstatus $regraSubstatus, Caso $caso): ?Carbon
    {
        if (! $dataBase || ! $regraSubstatus || ! $regraSubstatus->ativo || ! $regraSubstatus->gera_prazo) {
            return null;
        }

        if ($regraSubstatus->quantidade_dias === null) {
            return null;
        }

        $quantidadeDias = (int) $regraSubstatus->quantidade_dias;
        $dataInicial = Carbon::parse($dataBase)->startOfDay();

        if ($quantidadeDias <= 0) {
            return $dataInicial;
        }

        if ($regraSubstatus->tipo_contagem === RegraSubstatus::TIPO_CONTAGEM_CORRIDOS) {
            return $dataInicial->copy()->addDays($quantidadeDias);
        }

        if ($regraSubstatus->tipo_contagem === RegraSubstatus::TIPO_CONTAGEM_UTEIS) {
            return $this->somarDiasUteis($dataInicial, $quantidadeDias, $caso);
        }

        return null;
    }

    protected function somarDiasUteis(Carbon $dataInicial, int $quantidadeDias, Caso $caso): Carbon
    {
        $dataAtual = $dataInicial->copy();
        $diasContados = 0;

        while ($diasContados < $quantidadeDias) {
            $dataAtual->addDay();

            if ($this->calendarioJuridicoService->isDiaNaoUtil($dataAtual, $caso)) {
                continue;
            }

            $diasContados++;
        }

        return $dataAtual;
    }
}
