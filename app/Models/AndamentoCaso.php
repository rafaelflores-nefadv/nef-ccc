<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AndamentoCaso extends Model
{
    use HasFactory;

    protected $table = 'andamentos_caso';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'caso_id',
        'usuario_id',
        'data_descricao',
        'tipo_status_id',
        'data_alteracao_status',
        'tipo_substatus_id',
        'data_alteracao_substatus',
        'descricao',
        'observacoes',
        'parecer',
        'data_prazo',
        'data_primeiro_leilao',
        'data_segundo_leilao',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_descricao' => 'date',
            'data_alteracao_status' => 'date',
            'data_alteracao_substatus' => 'date',
            'data_prazo' => 'date',
            'data_primeiro_leilao' => 'date',
            'data_segundo_leilao' => 'date',
        ];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function tipoStatus(): BelongsTo
    {
        return $this->belongsTo(TipoStatus::class, 'tipo_status_id');
    }

    public function tipoSubstatus(): BelongsTo
    {
        return $this->belongsTo(TipoSubstatus::class, 'tipo_substatus_id');
    }
}
