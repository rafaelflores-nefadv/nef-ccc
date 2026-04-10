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
        Schema::create('robot_execucoes', function (Blueprint $table) {
            $table->id();
            $table->string('robot_nome')->index();
            $table->string('status', 30)->default('pendente')->index();
            $table->string('arquivo_origem')->nullable();
            $table->string('relatorio_id')->nullable();

            $table->unsignedInteger('total_linhas')->default(0);
            $table->unsignedInteger('linhas_processadas')->default(0);
            $table->unsignedInteger('linhas_inseridas')->default(0);
            $table->unsignedInteger('linhas_atualizadas')->default(0);
            $table->unsignedInteger('linhas_ignoradas')->default(0);
            $table->unsignedInteger('linhas_com_erro')->default(0);
            $table->decimal('percentual', 5, 2)->default(0);

            $table->text('mensagem_status')->nullable();
            $table->timestamp('iniciado_em')->nullable()->index();
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robot_execucoes');
    }
};
