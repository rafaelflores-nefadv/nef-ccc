<?php

namespace Database\Seeders;

use App\Models\Papel;
use App\Models\Permissao;
use Illuminate\Database\Seeder;

class PapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agora = now();

        $papeis = [
            [
                'nome' => 'Gestor',
                'slug' => 'gestor',
                'descricao' => 'Papel com acesso operacional e de gestão.',
                'ativo' => true,
            ],
            [
                'nome' => 'Operacional',
                'slug' => 'operacional',
                'descricao' => 'Papel focado em operação e acompanhamento.',
                'ativo' => true,
            ],
        ];

        $registros = array_map(function (array $papel) use ($agora): array {
            return [
                ...$papel,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }, $papeis);

        Papel::query()->upsert(
            $registros,
            ['slug'],
            ['nome', 'descricao', 'ativo', 'updated_at']
        );

        $permissoesPorPapel = [
            'gestor' => [
                'dashboard.visualizar',
                'casos.visualizar',
                'casos.criar',
                'casos.editar',
                'andamentos.visualizar',
                'andamentos.criar',
                'relatorios.visualizar',
                'relatorios.exportar',
            ],
            'operacional' => [
                'dashboard.visualizar',
                'casos.visualizar',
                'andamentos.visualizar',
                'andamentos.criar',
            ],
        ];

        foreach ($permissoesPorPapel as $slugPapel => $slugsPermissao) {
            $papel = Papel::query()->where('slug', $slugPapel)->first();

            if (! $papel) {
                continue;
            }

            $idsPermissoes = Permissao::query()
                ->whereIn('slug', $slugsPermissao)
                ->pluck('id')
                ->all();

            $papel->permissoes()->sync($idsPermissoes);
        }
    }
}

