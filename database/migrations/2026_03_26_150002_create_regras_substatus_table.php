<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regras_substatus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_substatus_id')
                ->unique()
                ->constrained('tipos_substatus')
                ->cascadeOnDelete();
            $table->integer('quantidade_dias')->nullable();
            $table->string('tipo_contagem')->nullable();
            $table->boolean('exige_primeiro_leilao')->default(false);
            $table->boolean('exige_segundo_leilao')->default(false);
            $table->boolean('gera_prazo')->default(true);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regras_substatus');
    }
};
