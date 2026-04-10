<?php

namespace Database\Seeders;

use App\Models\Permissao;
use Illuminate\Database\Seeder;

class PermissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agora = now();

        $permissoes = [
            ['nome' => 'Visualizar dashboard', 'slug' => 'dashboard.visualizar', 'descricao' => 'Permite visualizar o painel principal.'],
            ['nome' => 'Visualizar casos', 'slug' => 'casos.visualizar', 'descricao' => 'Permite consultar casos.'],
            ['nome' => 'Criar casos', 'slug' => 'casos.criar', 'descricao' => 'Permite cadastrar casos.'],
            ['nome' => 'Editar casos', 'slug' => 'casos.editar', 'descricao' => 'Permite editar casos existentes.'],
            ['nome' => 'Excluir casos', 'slug' => 'casos.excluir', 'descricao' => 'Permite excluir casos.'],
            ['nome' => 'Visualizar andamentos', 'slug' => 'andamentos.visualizar', 'descricao' => 'Permite visualizar andamentos.'],
            ['nome' => 'Criar andamentos', 'slug' => 'andamentos.criar', 'descricao' => 'Permite registrar andamentos.'],
            ['nome' => 'Visualizar relatórios', 'slug' => 'relatorios.visualizar', 'descricao' => 'Permite acessar relatórios.'],
            ['nome' => 'Exportar relatórios', 'slug' => 'relatorios.exportar', 'descricao' => 'Permite exportar relatórios.'],
            ['nome' => 'Visualizar feriados', 'slug' => 'feriados.visualizar', 'descricao' => 'Permite consultar feriados e suspensões.'],
            ['nome' => 'Gerenciar feriados', 'slug' => 'feriados.gerenciar', 'descricao' => 'Permite cadastrar, editar e excluir feriados e suspensões.'],
            ['nome' => 'Visualizar usuários', 'slug' => 'usuarios.visualizar', 'descricao' => 'Permite visualizar usuários.'],
            ['nome' => 'Criar usuários', 'slug' => 'usuarios.criar', 'descricao' => 'Permite criar usuários.'],
            ['nome' => 'Editar usuários', 'slug' => 'usuarios.editar', 'descricao' => 'Permite editar usuários.'],
            ['nome' => 'Alterar status de usuários', 'slug' => 'usuarios.status', 'descricao' => 'Permite ativar e desativar usuários.'],
            ['nome' => 'Redefinir senha de usuários', 'slug' => 'usuarios.senha', 'descricao' => 'Permite redefinir senha de usuários.'],
            ['nome' => 'Visualizar papéis', 'slug' => 'papeis.visualizar', 'descricao' => 'Permite visualizar papéis e acessos.'],
            ['nome' => 'Gerenciar papéis', 'slug' => 'papeis.gerenciar', 'descricao' => 'Permite criar e editar papéis e permissões.'],
            ['nome' => 'Visualizar configurações', 'slug' => 'configuracoes.visualizar', 'descricao' => 'Permite visualizar configurações do sistema.'],
            ['nome' => 'Editar configurações', 'slug' => 'configuracoes.editar', 'descricao' => 'Permite alterar configurações do sistema.'],
        ];

        $registros = array_map(function (array $permissao) use ($agora): array {
            return [
                ...$permissao,
                'modulo' => explode('.', $permissao['slug'])[0] ?? null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }, $permissoes);

        Permissao::query()->upsert(
            $registros,
            ['slug'],
            ['nome', 'descricao', 'modulo', 'updated_at']
        );
    }
}
