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
        Schema::create('prazo_notificacao_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')
                ->constrained('casos')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('cooperativa_id')
                ->constrained('cooperativas')
                ->cascadeOnDelete();
            $table->string('canal', 30);
            $table->string('tipo_evento', 40);
            $table->date('data_referencia');
            $table->timestamp('enviado_em')->nullable();
            $table->string('status', 20)->default('pendente');
            $table->text('erro')->nullable();
            $table->json('payload_resumo')->nullable();
            $table->timestamps();

            $table->unique(
                ['caso_id', 'user_id', 'canal', 'tipo_evento', 'data_referencia'],
                'prazo_notif_envios_unico_por_evento'
            );
            $table->index(['cooperativa_id', 'data_referencia'], 'prazo_notif_envios_coop_data_idx');
            $table->index(['tipo_evento', 'status'], 'prazo_notif_envios_evento_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prazo_notificacao_envios');
    }
};
