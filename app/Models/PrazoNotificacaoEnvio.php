<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrazoNotificacaoEnvio extends Model
{
    use HasFactory;

    public const CANAL_INTERNO = 'interno';
    public const CANAL_EMAIL = 'email';
    public const CANAL_WHATSAPP = 'whatsapp';

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_ENFILEIRADO = 'enfileirado';
    public const STATUS_PROCESSANDO = 'processando';
    public const STATUS_SUCESSO = 'sucesso';
    public const STATUS_FALHA = 'falha';

    protected $table = 'prazo_notificacao_envios';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'caso_id',
        'user_id',
        'cooperativa_id',
        'canal',
        'tipo_evento',
        'data_referencia',
        'enviado_em',
        'status',
        'erro',
        'payload_resumo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'caso_id' => 'integer',
            'user_id' => 'integer',
            'cooperativa_id' => 'integer',
            'data_referencia' => 'date',
            'enviado_em' => 'datetime',
            'payload_resumo' => 'array',
        ];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cooperativa(): BelongsTo
    {
        return $this->belongsTo(Cooperativa::class);
    }
}
