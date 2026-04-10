<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoEmail extends Model
{
    use HasFactory;

    protected $table = 'configuracoes_email';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'driver',
        'host',
        'porta',
        'usuario',
        'senha',
        'criptografia',
        'email_remetente',
        'nome_remetente',
        'ativo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'porta' => 'integer',
            'ativo' => 'boolean',
        ];
    }
}

