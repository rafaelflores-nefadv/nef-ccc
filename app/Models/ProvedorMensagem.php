<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProvedorMensagem extends Model
{
    use HasFactory;

    protected $table = 'provedores_mensagem';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'slug',
        'tipo',
        'ativo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function configuracoes(): HasMany
    {
        return $this->hasMany(ConfiguracaoProvedorMensagem::class, 'provedor_id');
    }
}

