<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\User;
use App\Support\EscopoCooperativa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RelatorioCasoService
{
    /**
     * @param array<string, mixed> $filtros
     */
    public function queryRelatorio(array $filtros, User $usuario): Builder
    {
        $query = Caso::query()
            ->with([
                'cooperativa:id,nome',
                'responsavel:id,name',
                'tipoStatus:id,nome',
                'tipoSubstatus:id,nome',
                'ultimoAndamento' => fn ($query) => $query->with([
                    'tipoStatus:id,nome',
                    'tipoSubstatus:id,nome',
                ]),
                'andamentos' => fn ($query) => $query
                    ->with([
                        'tipoStatus:id,nome',
                        'tipoSubstatus:id,nome',
                    ])
                    ->orderBy('data_descricao')
                    ->orderBy('id'),
            ])
            ->withCount('andamentos');

        $this->aplicarEscopoCooperativa($query, $filtros, $usuario);
        $this->aplicarFiltros($query, $filtros);

        return $query->orderBy('codigo_caso');
    }

    /**
     * @param array<string, mixed> $filtros
     * @return Collection<int, Caso>
     */
    public function dadosRelatorio(array $filtros, User $usuario): Collection
    {
        return $this->queryRelatorio($filtros, $usuario)->get();
    }

    /**
     * @param array<string, mixed> $filtros
     */
    public function aplicarFiltros(Builder $query, array $filtros): void
    {
        if (! empty($filtros['codigo_caso'])) {
            $query->where('codigo_caso', 'ilike', '%'.trim((string) $filtros['codigo_caso']).'%');
        }

        if (! empty($filtros['numero_protocolo'])) {
            $query->where('numero_protocolo', 'ilike', '%'.trim((string) $filtros['numero_protocolo']).'%');
        }

        if (! empty($filtros['numero_prenotacao'])) {
            $query->where('numero_prenotacao', 'ilike', '%'.trim((string) $filtros['numero_prenotacao']).'%');
        }

        if (! empty($filtros['contrato'])) {
            $query->where('contrato', 'ilike', '%'.trim((string) $filtros['contrato']).'%');
        }

        if (! empty($filtros['nome'])) {
            $query->where('nome', 'ilike', '%'.trim((string) $filtros['nome']).'%');
        }

        if (! empty($filtros['comarca'])) {
            $query->where('comarca', 'ilike', '%'.trim((string) $filtros['comarca']).'%');
        }

        if (! empty($filtros['uf'])) {
            $query->where('uf', strtoupper(trim((string) $filtros['uf'])));
        }

        if (! empty($filtros['tipo_status_id'])) {
            $query->where('tipo_status_id', (int) $filtros['tipo_status_id']);
        }

        if (! empty($filtros['tipo_substatus_id'])) {
            $query->where('tipo_substatus_id', (int) $filtros['tipo_substatus_id']);
        }

        if (! empty($filtros['responsavel_id'])) {
            $query->where('responsavel_id', (int) $filtros['responsavel_id']);
        }

        if (array_key_exists('arquivado', $filtros) && $filtros['arquivado'] !== '') {
            $query->where('arquivado', (bool) (int) $filtros['arquivado']);
        }

        if (! empty($filtros['data_prazo_inicial'])) {
            $query->whereDate('data_prazo', '>=', $filtros['data_prazo_inicial']);
        }

        if (! empty($filtros['data_prazo_final'])) {
            $query->whereDate('data_prazo', '<=', $filtros['data_prazo_final']);
        }

        if (! empty($filtros['data_cadastro_inicial'])) {
            $query->whereDate('data_cadastro_caso', '>=', $filtros['data_cadastro_inicial']);
        }

        if (! empty($filtros['data_cadastro_final'])) {
            $query->whereDate('data_cadastro_caso', '<=', $filtros['data_cadastro_final']);
        }
    }

    /**
     * @param array<string, mixed> $filtros
     */
    public function aplicarEscopoCooperativa(Builder $query, array $filtros, User $usuario): void
    {
        $cooperativaIdUsuario = EscopoCooperativa::cooperativaId($usuario);

        if ($cooperativaIdUsuario !== null) {
            $query->where('cooperativa_id', $cooperativaIdUsuario);

            return;
        }

        if (! empty($filtros['cooperativa_id'])) {
            $query->where('cooperativa_id', (int) $filtros['cooperativa_id']);
        }
    }
}
