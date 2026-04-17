<?php

namespace App\Exports;

use App\Models\Caso;
use App\Models\User;
use App\Services\RelatorioCasoService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RelatorioCasosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param array<string, mixed> $filtros
     */
    public function __construct(
        protected RelatorioCasoService $relatorioCasoService,
        protected array $filtros,
        protected User $usuario
    ) {
    }

    public function collection(): Collection
    {
        return $this->relatorioCasoService->dadosRelatorio($this->filtros, $this->usuario);
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'codigo_caso',
            'cooperativa',
            'numero_protocolo',
            'numero_prenotacao',
            'data_cadastro_caso',
            'nome',
            'contrato',
            'partes',
            'comarca',
            'uf',
            'matricula',
            'valor_causa',
            'valor_divida',
            'responsavel',
            'status_atual',
            'substatus_atual',
            'data_alteracao_status',
            'data_alteracao_substatus',
            'pendente_faturamento',
            'data_prazo',
            'data_primeiro_leilao',
            'data_segundo_leilao',
            'parecer',
            'observacoes_gerais',
            'data_ultimo_andamento',
            'ultimo_andamento_descricao',
            'ultimo_andamento_observacoes',
            'quantidade_andamentos',
            'historico_consolidado',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($caso): array
    {
        /** @var Caso $caso */
        return [
            $caso->codigo_caso ?? '',
            $caso->cooperativa?->nome ?? '',
            $caso->numero_protocolo ?? '',
            $caso->numero_prenotacao ?? '',
            $this->formatarData($caso->data_cadastro_caso),
            $caso->nome ?? '',
            $caso->contrato ?? '',
            $caso->partes ?? '',
            $caso->comarca ?? '',
            $caso->uf ?? '',
            $caso->matricula ?? '',
            $this->formatarValor($caso->valor_causa),
            $this->formatarValor($caso->valor_divida),
            $caso->responsavel?->name ?? '',
            $caso->tipoStatus?->nome ?? '',
            $caso->tipoSubstatus?->nome ?? '',
            $this->formatarData($caso->data_alteracao_status),
            $this->formatarData($caso->data_alteracao_substatus),
            $caso->pendente_faturamento ? 'Pendente' : 'Não pendente',
            $this->formatarData($caso->data_prazo),
            $this->formatarData($caso->data_primeiro_leilao),
            $this->formatarData($caso->data_segundo_leilao),
            $caso->parecer ?? '',
            $caso->observacoes_gerais ?? '',
            $this->formatarDataHora($caso->data_ultimo_andamento),
            $caso->ultimoAndamento?->descricao ?? '',
            $caso->ultimoAndamento?->observacoes ?? '',
            (string) ($caso->andamentos_count ?? 0),
            $this->historicoConsolidado($caso),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    protected function formatarData(mixed $data): string
    {
        return $data ? $data->format('d/m/Y') : '';
    }

    protected function formatarDataHora(mixed $data): string
    {
        return $data ? $data->format('d/m/Y H:i:s') : '';
    }

    protected function formatarValor(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        return number_format((float) $valor, 2, ',', '.');
    }

    protected function historicoConsolidado(Caso $caso): string
    {
        if (! $caso->relationLoaded('andamentos') || $caso->andamentos->isEmpty()) {
            return '';
        }

        $itens = [];

        foreach ($caso->andamentos as $andamento) {
            $status = $andamento->tipoStatus?->nome ?? '';
            $substatus = $andamento->tipoSubstatus?->nome ?? '';
            $descricao = trim((string) ($andamento->descricao ?? ''));
            $data = $andamento->data_descricao?->format('d/m/Y') ?? '';

            $linha = sprintf(
                '[%s] %s / %s - %s',
                $data,
                $status,
                $substatus,
                $descricao
            );

            if (! empty($andamento->observacoes)) {
                $linha .= "\n".'Observacoes: '.trim((string) $andamento->observacoes);
            }

            $itens[] = $linha;
        }

        return implode("\n\n", $itens);
    }
}
