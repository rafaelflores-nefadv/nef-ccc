<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RobotExecucao extends Model
{
    use HasFactory;

    protected $table = 'robot_execucoes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'robot_nome',
        'status',
        'arquivo_origem',
        'relatorio_id',
        'total_linhas',
        'linhas_processadas',
        'linhas_inseridas',
        'linhas_atualizadas',
        'linhas_ignoradas',
        'linhas_com_erro',
        'percentual',
        'mensagem_status',
        'iniciado_em',
        'finalizado_em',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_linhas' => 'integer',
            'linhas_processadas' => 'integer',
            'linhas_inseridas' => 'integer',
            'linhas_atualizadas' => 'integer',
            'linhas_ignoradas' => 'integer',
            'linhas_com_erro' => 'integer',
            'percentual' => 'decimal:2',
            'iniciado_em' => 'datetime',
            'finalizado_em' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RobotExecucaoLog::class, 'robot_execucao_id');
    }
}
