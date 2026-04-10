<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoStatus extends Model
{
    use HasFactory;

    protected $table = 'tipos_status';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'descricao',
        'ordem',
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

    public function tiposSubstatus(): HasMany
    {
        return $this->hasMany(TipoSubstatus::class, 'tipo_status_id');
    }

    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class, 'tipo_status_id');
    }

    public function andamentos(): HasMany
    {
        return $this->hasMany(AndamentoCaso::class, 'tipo_status_id');
    }
}
