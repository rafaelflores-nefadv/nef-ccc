<?php

namespace App\Models;

use App\Services\PrazoCasoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Caso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'casos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cooperativa_id',
        'codigo_caso',
        'id_processo',
        'status_processo',
        'numero_processo',
        'area_direito',
        'esfera',
        'foro_tribunal',
        'vara_local',
        'tipo_acao',
        'codigo_empresa',
        'empresa',
        'agencia_filial',
        'escritorio_externo',
        'distribuicao',
        'fase_fluxo',
        'na_fase_desde',
        'fase',
        'data_fase_processual',
        'data_encerramento',
        'motivo_encerramento',
        'parte_contraria_cpf_cnpj',
        'modelo_conducao',
        'conducao_estrategica',
        'status_citacao',
        'classificacao',
        'irrecuperavel',
        'objeto_demanda',
        'instancia',
        'fase_acordo',
        'na_fase_acordo_desde',
        'polo_acao',
        'observacao_encerramento',
        'existe_saldo_residual',
        'medida_atipica',
        'advogado_parte_contraria',
        'id_externo',
        'codigo_importacao',
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
        'responsavel_id',
        'observacoes_gerais',
        'tipo_status_id',
        'tipo_substatus_id',
        'data_alteracao_status',
        'data_alteracao_substatus',
        'data_prazo',
        'data_primeiro_leilao',
        'data_segundo_leilao',
        'parecer',
        'data_ultimo_andamento',
        'arquivado',
        'pendente_faturamento',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'distribuicao' => 'datetime',
            'na_fase_desde' => 'date',
            'data_fase_processual' => 'date',
            'data_encerramento' => 'date',
            'irrecuperavel' => 'boolean',
            'na_fase_acordo_desde' => 'date',
            'existe_saldo_residual' => 'boolean',
            'data_cadastro_caso' => 'date',
            'valor_causa' => 'decimal:2',
            'valor_divida' => 'decimal:2',
            'data_alteracao_status' => 'date',
            'data_alteracao_substatus' => 'date',
            'data_prazo' => 'date',
            'data_primeiro_leilao' => 'date',
            'data_segundo_leilao' => 'date',
            'data_ultimo_andamento' => 'datetime',
            'arquivado' => 'boolean',
            'pendente_faturamento' => 'boolean',
        ];
    }

    public function cooperativa(): BelongsTo
    {
        return $this->belongsTo(Cooperativa::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function tipoStatus(): BelongsTo
    {
        return $this->belongsTo(TipoStatus::class, 'tipo_status_id');
    }

    public function tipoSubstatus(): BelongsTo
    {
        return $this->belongsTo(TipoSubstatus::class, 'tipo_substatus_id');
    }

    public function andamentos(): HasMany
    {
        return $this->hasMany(AndamentoCaso::class);
    }

    public function ultimoAndamento(): HasOne
    {
        return $this->hasOne(AndamentoCaso::class)
            ->ofMany([
                'data_descricao' => 'max',
                'id' => 'max',
            ]);
    }

    public function suspensoesPrazo(): HasMany
    {
        return $this->hasMany(SuspensaoPrazo::class);
    }

    /**
     * @return array{
     *   status: string,
     *   data_limite: ?Carbon,
     *   dias_restantes: ?int,
     *   dias_atraso: ?int,
     *   dias_configurados: int
     * }
     */
    public function prazoDistribuicao(): array
    {
        return once(fn (): array => app(PrazoCasoService::class)->calcular($this));
    }

    public function getDataLimitePrazoAttribute(): ?Carbon
    {
        return $this->prazoDistribuicao()['data_limite'];
    }

    public function getStatusPrazoDistribuicaoAttribute(): string
    {
        return $this->prazoDistribuicao()['status'];
    }

    public function getDiasRestantesPrazoAttribute(): ?int
    {
        return $this->prazoDistribuicao()['dias_restantes'];
    }

    public function getDiasAtrasoPrazoAttribute(): ?int
    {
        return $this->prazoDistribuicao()['dias_atraso'];
    }

    public function getDiasPrazoConfiguradoAttribute(): int
    {
        return $this->prazoDistribuicao()['dias_configurados'];
    }
}
