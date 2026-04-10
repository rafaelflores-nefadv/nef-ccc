<?php

namespace Database\Seeders;

use App\Models\ProvedorMensagem;
use Illuminate\Database\Seeder;

class ProvedorMensagemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agora = now();

        $provedores = [
            ['nome' => 'Meta', 'slug' => 'meta', 'tipo' => 'whatsapp', 'ativo' => true],
            ['nome' => 'WAHA', 'slug' => 'waha', 'tipo' => 'whatsapp', 'ativo' => true],
        ];

        $registros = array_map(function (array $provedor) use ($agora): array {
            return [
                ...$provedor,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }, $provedores);

        ProvedorMensagem::query()->upsert(
            $registros,
            ['slug'],
            ['nome', 'tipo', 'ativo', 'updated_at']
        );
    }
}
