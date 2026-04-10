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
        Schema::create('andamentos_caso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')
                ->constrained('casos')
                ->cascadeOnDelete();
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('data_descricao');
            $table->foreignId('tipo_status_id')
                ->nullable()
                ->constrained('tipos_status')
                ->nullOnDelete();
            $table->date('data_alteracao_status')->nullable();
            $table->foreignId('tipo_substatus_id')
                ->nullable()
                ->constrained('tipos_substatus')
                ->nullOnDelete();
            $table->date('data_alteracao_substatus')->nullable();
            $table->text('descricao');
            $table->text('observacoes')->nullable();
            $table->text('parecer')->nullable();
            $table->date('data_prazo')->nullable();
            $table->date('data_primeiro_leilao')->nullable();
            $table->date('data_segundo_leilao')->nullable();
            $table->timestamps();

            $table->index(['caso_id', 'data_descricao']);
            $table->index('tipo_status_id');
            $table->index('tipo_substatus_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('andamentos_caso');
    }
};
