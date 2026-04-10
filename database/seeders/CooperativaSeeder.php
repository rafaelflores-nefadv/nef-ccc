<?php

namespace Database\Seeders;

use App\Models\Cooperativa;
use Illuminate\Database\Seeder;

class CooperativaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cooperativa::updateOrCreate(
            ['slug' => 'sicredi'],
            [
                'nome' => 'Sicredi',
                'ativo' => true,
            ]
        );
    }
}
