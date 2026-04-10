<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeriadoSuspensao extends Model
{
    use HasFactory;

    public const TIPO_FERIADO = 'feriado';
    public const TIPO_SUSPENSAO = 'suspensao';

    public const ABRANGENCIA_NACIONAL = 'nacional';
    public const ABRANGENCIA_LOCAL = 'local';

    protected $table = 'feriados_suspensoes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'data',
        'descricao',
        'tipo',
        'abrangencia',
        'uf',
        'comarca',
        'ativo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'date',
            'ativo' => 'boolean',
        ];
    }
}
