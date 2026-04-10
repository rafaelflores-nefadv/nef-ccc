<?php

namespace Database\Seeders;

use App\Models\RegraSubstatus;
use App\Models\TipoSubstatus;
use Illuminate\Database\Seeder;

class RegraSubstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $intimacao = "Intima\u{00E7}\u{00E3}o";
        $averbacao = "Averba\u{00E7}\u{00E3}o";
        $leilao = "Leil\u{00E3}o";

        $regras = [
            "{$intimacao} protocolada" => [
                'quantidade_dias' => 7,
                'tipo_contagem' => RegraSubstatus::TIPO_CONTAGEM_UTEIS,
                'exige_primeiro_leilao' => false,
                'exige_segundo_leilao' => false,
                'gera_prazo' => true,
            ],
            "{$intimacao} expedida" => [
                'quantidade_dias' => 30,
                'tipo_contagem' => RegraSubstatus::TIPO_CONTAGEM_CORRIDOS,
                'exige_primeiro_leilao' => false,
                'exige_segundo_leilao' => false,
                'gera_prazo' => true,
            ],
            'Intimado' => [
                'quantidade_dias' => 15,
                'tipo_contagem' => RegraSubstatus::TIPO_CONTAGEM_UTEIS,
                'exige_primeiro_leilao' => false,
                'exige_segundo_leilao' => false,
                'gera_prazo' => true,
            ],
            "{$averbacao} Protocolada" => [
                'quantidade_dias' => 10,
                'tipo_contagem' => RegraSubstatus::TIPO_CONTAGEM_UTEIS,
                'exige_primeiro_leilao' => false,
                'exige_segundo_leilao' => false,
                'gera_prazo' => true,
            ],
            "Protocolo de {$leilao}" => [
                'quantidade_dias' => 10,
                'tipo_contagem' => RegraSubstatus::TIPO_CONTAGEM_UTEIS,
                'exige_primeiro_leilao' => false,
                'exige_segundo_leilao' => false,
                'gera_prazo' => true,
            ],
            $leilao => [
                'quantidade_dias' => null,
                'tipo_contagem' => null,
                'exige_primeiro_leilao' => true,
                'exige_segundo_leilao' => true,
                'gera_prazo' => false,
            ],
        ];

        foreach ($regras as $nomeSubstatus => $dados) {
            $substatus = TipoSubstatus::query()
                ->where('nome', $nomeSubstatus)
                ->firstOrFail();

            RegraSubstatus::updateOrCreate(
                ['tipo_substatus_id' => $substatus->id],
                $dados + ['ativo' => true]
            );
        }
    }
}
