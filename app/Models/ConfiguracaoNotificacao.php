<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoNotificacao extends Model
{
    use HasFactory;

    protected $table = 'configuracoes_notificacao';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'canal_email_ativo',
        'canal_whatsapp_ativo',
        'notificar_prazo_vencendo',
        'dias_antes_prazo',
        'notificar_prazo_vencido',
        'notificar_leilao',
        'notificar_novo_andamento',
        'emails_destino_json',
        'usuarios_destino_json',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'canal_email_ativo' => 'boolean',
            'canal_whatsapp_ativo' => 'boolean',
            'notificar_prazo_vencendo' => 'boolean',
            'dias_antes_prazo' => 'integer',
            'notificar_prazo_vencido' => 'boolean',
            'notificar_leilao' => 'boolean',
            'notificar_novo_andamento' => 'boolean',
            'emails_destino_json' => 'array',
            'usuarios_destino_json' => 'array',
        ];
    }
}

