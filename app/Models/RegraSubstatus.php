<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegraSubstatus extends Model
{
    use HasFactory;

    public const TIPO_CONTAGEM_UTEIS = 'uteis';
    public const TIPO_CONTAGEM_CORRIDOS = 'corridos';

    protected $table = 'regras_substatus';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo_substatus_id',
        'quantidade_dias',
        'tipo_contagem',
        'exige_primeiro_leilao',
        'exige_segundo_leilao',
        'gera_prazo',
        'ativo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantidade_dias' => 'integer',
            'exige_primeiro_leilao' => 'boolean',
            'exige_segundo_leilao' => 'boolean',
            'gera_prazo' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function tipoSubstatus(): BelongsTo
    {
        return $this->belongsTo(TipoSubstatus::class, 'tipo_substatus_id');
    }
}
