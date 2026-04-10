<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RobotExecucaoLog extends Model
{
    use HasFactory;

    protected $table = 'robot_execucao_logs';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'robot_execucao_id',
        'nivel',
        'mensagem',
        'contexto_json',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contexto_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function execucao(): BelongsTo
    {
        return $this->belongsTo(RobotExecucao::class, 'robot_execucao_id');
    }
}
