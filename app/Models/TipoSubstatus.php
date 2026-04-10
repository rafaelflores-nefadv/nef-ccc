<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TipoSubstatus extends Model
{
    use HasFactory;

    protected $table = 'tipos_substatus';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo_status_id',
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

    public function tipoStatus(): BelongsTo
    {
        return $this->belongsTo(TipoStatus::class, 'tipo_status_id');
    }

    public function regraSubstatus(): HasOne
    {
        return $this->hasOne(RegraSubstatus::class, 'tipo_substatus_id');
    }

    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class, 'tipo_substatus_id');
    }

    public function andamentos(): HasMany
    {
        return $this->hasMany(AndamentoCaso::class, 'tipo_substatus_id');
    }
}
