<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS casos_distribuicao_index ON casos (distribuicao)');
        DB::statement('CREATE INDEX IF NOT EXISTS casos_cooperativa_distribuicao_index ON casos (cooperativa_id, distribuicao)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS casos_cooperativa_distribuicao_index');
        DB::statement('DROP INDEX IF EXISTS casos_distribuicao_index');
    }
};
