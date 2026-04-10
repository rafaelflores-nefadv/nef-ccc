<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permissao extends Model
{
    use HasFactory;

    protected $table = 'permissoes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'modulo',
    ];

    public function papeis(): BelongsToMany
    {
        return $this->belongsToMany(Papel::class, 'papel_permissao');
    }
}
