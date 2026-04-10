<?php

namespace Database\Seeders;

use App\Models\TipoStatus;
use Illuminate\Database\Seeder;

class TipoStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusConcluido = "Conclu\u{00ED}do";

        $status = [
            ['nome' => 'Em andamento', 'ordem' => 1],
            ['nome' => 'Suspenso', 'ordem' => 2],
            ['nome' => $statusConcluido, 'ordem' => 3],
        ];

        foreach ($status as $item) {
            TipoStatus::updateOrCreate(
                ['nome' => $item['nome']],
                [
                    'descricao' => null,
                    'ordem' => $item['ordem'],
                    'ativo' => true,
                ]
            );
        }
    }
}
