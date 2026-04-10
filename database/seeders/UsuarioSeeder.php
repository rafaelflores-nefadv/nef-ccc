<?php

namespace Database\Seeders;

use App\Models\Cooperativa;
use App\Models\Papel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'admin@admin.com';
        $usuarioCooperativaEmail = 'usuario@teste.com';

        $cooperativa = Cooperativa::query()
            ->where('slug', 'sicredi')
            ->firstOrFail();

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin Master',
                'password' => Hash::make('admin123'),
                'perfil' => User::PERFIL_ADMIN,
                'cooperativa_id' => null,
                'ativo' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => $usuarioCooperativaEmail],
            [
                'name' => 'Usuário Cooperativa',
                'password' => Hash::make('password'),
                'perfil' => User::PERFIL_OPERACIONAL,
                'cooperativa_id' => $cooperativa->id,
                'ativo' => true,
            ]
        );

        $papelOperacional = Papel::query()
            ->where('slug', 'operacional')
            ->first();

        $usuarioCooperativa = User::query()
            ->where('email', $usuarioCooperativaEmail)
            ->first();

        if ($papelOperacional && $usuarioCooperativa) {
            $usuarioCooperativa->papeis()->sync([$papelOperacional->id]);
        }
    }
}
