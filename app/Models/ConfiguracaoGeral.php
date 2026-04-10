<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoGeral extends Model
{
    use HasFactory;

    protected $table = 'configuracoes_gerais';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nome_sistema',
        'timezone',
        'email_suporte',
        'logo_path',
        'login_badge_text',
        'login_title',
        'login_description',
        'rodape_relatorio',
    ];
}
