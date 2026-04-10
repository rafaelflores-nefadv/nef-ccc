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
        Schema::create('robot_execucao_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('robot_execucao_id')
                ->constrained('robot_execucoes')
                ->cascadeOnDelete();
            $table->string('nivel', 20)->index();
            $table->text('mensagem');
            $table->json('contexto_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robot_execucao_logs');
    }
};
