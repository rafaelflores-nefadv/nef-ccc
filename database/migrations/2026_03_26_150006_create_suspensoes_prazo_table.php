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
        Schema::create('suspensoes_prazo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')
                ->constrained('casos')
                ->cascadeOnDelete();
            $table->text('motivo');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->date('data_prazo_original')->nullable();
            $table->date('data_prazo_recalculado')->nullable();
            $table->boolean('ativo')->default(true);
            $table->foreignId('criado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['caso_id', 'ativo']);
            $table->index(['data_inicio', 'data_fim']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspensoes_prazo');
    }
};
