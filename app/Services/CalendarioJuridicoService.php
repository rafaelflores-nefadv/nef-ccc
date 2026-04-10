<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\FeriadoSuspensao;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class CalendarioJuridicoService
{
    public function isDiaNaoUtil(CarbonInterface $data, ?Caso $caso = null): bool
    {
        if ($data->isWeekend()) {
            return true;
        }

        return $this->existeExcecaoCalendario($data, $caso);
    }

    protected function existeExcecaoCalendario(CarbonInterface $data, ?Caso $caso = null): bool
    {
        return FeriadoSuspensao::query()
            ->where('ativo', true)
            ->whereDate('data', $data->toDateString())
            ->whereIn('tipo', [
                FeriadoSuspensao::TIPO_FERIADO,
                FeriadoSuspensao::TIPO_SUSPENSAO,
            ])
            ->where(function (Builder $query) use ($caso): void {
                $query->where('abrangencia', FeriadoSuspensao::ABRANGENCIA_NACIONAL);

                if (! $caso?->uf) {
                    return;
                }

                $query->orWhere(function (Builder $localQuery) use ($caso): void {
                    $localQuery->where('abrangencia', FeriadoSuspensao::ABRANGENCIA_LOCAL)
                        ->where('uf', $caso->uf)
                        ->where(function (Builder $comarcaQuery) use ($caso): void {
                            $comarcaQuery->whereNull('comarca');

                            if ($caso->comarca) {
                                $comarcaQuery->orWhere('comarca', $caso->comarca);
                            }
                        });
                });
            })
            ->exists();
    }
}
