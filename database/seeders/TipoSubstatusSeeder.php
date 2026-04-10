<?php

namespace Database\Seeders;

use App\Models\TipoStatus;
use App\Models\TipoSubstatus;
use Illuminate\Database\Seeder;

class TipoSubstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusEmAndamento = TipoStatus::query()
            ->where('nome', 'Em andamento')
            ->firstOrFail();

        $intimacao = "Intima\u{00E7}\u{00E3}o";
        $averbacao = "Averba\u{00E7}\u{00E3}o";
        $leilao = "Leil\u{00E3}o";

        $substatus = [
            "{$intimacao} protocolada",
            "{$intimacao} expedida",
            'Intimado',
            "{$averbacao} Protocolada",
            "Protocolo de {$leilao}",
            $leilao,
        ];

        foreach ($substatus as $ordem => $nome) {
            TipoSubstatus::updateOrCreate(
                ['nome' => $nome],
                [
                    'tipo_status_id' => $statusEmAndamento->id,
                    'descricao' => null,
                    'ordem' => $ordem + 1,
                    'ativo' => true,
                ]
            );
        }
    }
}
