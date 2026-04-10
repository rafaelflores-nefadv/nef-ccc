<?php

namespace App\Services;

use App\Models\Caso;
use Illuminate\Support\Str;

class GeradorCodigoCasoService
{
    public function gerar(): string
    {
        do {
            $codigo = sprintf(
                'CASO-%s-%s',
                now()->format('Ymd-His'),
                strtoupper(Str::random(4))
            );
        } while (Caso::withTrashed()->where('codigo_caso', $codigo)->exists());

        return $codigo;
    }
}
