<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracaoProvedorMensagem extends Model
{
    use HasFactory;

    protected $table = 'configuracoes_provedor_mensagem';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provedor_id',
        'nome_conexao',
        'url_base',
        'token',
        'instancia',
        'configuracoes_json',
        'ativo',
        'padrao',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provedor_id' => 'integer',
            'configuracoes_json' => 'array',
            'ativo' => 'boolean',
            'padrao' => 'boolean',
        ];
    }

    public function provedor(): BelongsTo
    {
        return $this->belongsTo(ProvedorMensagem::class, 'provedor_id');
    }
}

