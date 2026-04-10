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
        Schema::create('configuracoes_gerais', function (Blueprint $table) {
            $table->id();
            $table->string('nome_sistema');
            $table->string('timezone');
            $table->string('email_suporte')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('rodape_relatorio')->nullable();
            $table->timestamps();
        });

        Schema::create('configuracoes_email', function (Blueprint $table) {
            $table->id();
            $table->string('driver');
            $table->string('host');
            $table->unsignedInteger('porta');
            $table->string('usuario');
            $table->string('senha')->nullable();
            $table->string('criptografia')->nullable();
            $table->string('email_remetente');
            $table->string('nome_remetente');
            $table->boolean('ativo')->default(false);
            $table->timestamps();
        });

        Schema::create('provedores_mensagem', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('tipo');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('configuracoes_provedor_mensagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provedor_id')
                ->constrained('provedores_mensagem')
                ->cascadeOnDelete();
            $table->string('nome_conexao');
            $table->string('url_base');
            $table->text('token')->nullable();
            $table->string('instancia')->nullable();
            $table->json('configuracoes_json')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('padrao')->default(false);
            $table->timestamps();

            $table->unique(['provedor_id', 'nome_conexao']);
        });

        Schema::create('configuracoes_notificacao', function (Blueprint $table) {
            $table->id();
            $table->boolean('canal_email_ativo')->default(false);
            $table->boolean('canal_whatsapp_ativo')->default(false);
            $table->boolean('notificar_prazo_vencendo')->default(false);
            $table->unsignedInteger('dias_antes_prazo')->default(1);
            $table->boolean('notificar_prazo_vencido')->default(false);
            $table->boolean('notificar_leilao')->default(false);
            $table->boolean('notificar_novo_andamento')->default(false);
            $table->json('emails_destino_json')->nullable();
            $table->json('usuarios_destino_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes_notificacao');
        Schema::dropIfExists('configuracoes_provedor_mensagem');
        Schema::dropIfExists('provedores_mensagem');
        Schema::dropIfExists('configuracoes_email');
        Schema::dropIfExists('configuracoes_gerais');
    }
};

