<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspensaoPrazo extends Model
{
    use HasFactory;

    protected $table = 'suspensoes_prazo';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'caso_id',
        'motivo',
        'data_inicio',
        'data_fim',
        'data_prazo_original',
        'data_prazo_recalculado',
        'ativo',
        'criado_por',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'data_prazo_original' => 'date',
            'data_prazo_recalculado' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }
}
