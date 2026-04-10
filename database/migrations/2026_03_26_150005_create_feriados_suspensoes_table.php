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
        Schema::create('feriados_suspensoes', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->string('descricao');
            $table->string('tipo');
            $table->string('abrangencia');
            $table->char('uf', 2)->nullable();
            $table->string('comarca')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['data', 'tipo', 'abrangencia']);
            $table->index('uf');
            $table->index('comarca');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feriados_suspensoes');
    }
};
