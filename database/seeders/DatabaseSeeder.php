<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CooperativaSeeder::class,
            PermissaoSeeder::class,
            PapelSeeder::class,
            ProvedorMensagemSeeder::class,
            UsuarioSeeder::class,
            TipoStatusSeeder::class,
            TipoSubstatusSeeder::class,
            RegraSubstatusSeeder::class,
        ]);
    }
}
