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
        Schema::create('casos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperativa_id')
                ->constrained('cooperativas');
            $table->string('codigo_caso')->unique();
            $table->string('id_processo')->nullable()->index();
            $table->string('status_processo')->nullable()->index();
            $table->string('numero_processo')->nullable()->index();
            $table->string('area_direito')->nullable();
            $table->string('esfera')->nullable();
            $table->string('foro_tribunal')->nullable();
            $table->string('vara_local')->nullable();
            $table->string('tipo_acao')->nullable();
            $table->string('codigo_empresa')->nullable()->index();
            $table->string('empresa')->nullable();
            $table->string('agencia_filial')->nullable();
            $table->string('escritorio_externo')->nullable();
            $table->string('distribuicao')->nullable();
            $table->string('fase_fluxo')->nullable();
            $table->date('na_fase_desde')->nullable();
            $table->string('fase')->nullable();
            $table->date('data_fase_processual')->nullable();
            $table->date('data_encerramento')->nullable();
            $table->text('motivo_encerramento')->nullable();
            $table->string('nome')->nullable();
            $table->string('parte_contraria_cpf_cnpj', 20)->nullable();
            $table->string('modelo_conducao')->nullable();
            $table->string('conducao_estrategica')->nullable();
            $table->string('status_citacao')->nullable();
            $table->string('classificacao')->nullable();
            $table->boolean('irrecuperavel')->nullable();
            $table->text('objeto_demanda')->nullable();
            $table->string('instancia')->nullable();
            $table->string('fase_acordo')->nullable();
            $table->date('na_fase_acordo_desde')->nullable();
            $table->string('polo_acao')->nullable();
            $table->text('observacoes_gerais')->nullable();
            $table->text('observacao_encerramento')->nullable();
            $table->boolean('existe_saldo_residual')->nullable();
            $table->string('medida_atipica')->nullable();
            $table->string('advogado_parte_contraria')->nullable();
            $table->string('comarca')->nullable()->index();
            $table->char('uf', 2)->nullable()->index();
            $table->decimal('valor_causa', 15, 2)->nullable();
            $table->date('data_cadastro_caso')->nullable();
            $table->string('id_externo')->nullable()->index();
            $table->string('codigo_importacao')->nullable()->index();

            $table->string('numero_protocolo')->nullable()->index();
            $table->string('numero_prenotacao')->nullable()->index();
            $table->string('contrato')->index();
            $table->text('partes');
            $table->string('matricula')->nullable();
            $table->decimal('valor_divida', 15, 2)->nullable();
            $table->foreignId('responsavel_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('tipo_status_id')
                ->nullable()
                ->constrained('tipos_status')
                ->nullOnDelete();
            $table->foreignId('tipo_substatus_id')
                ->nullable()
                ->constrained('tipos_substatus')
                ->nullOnDelete();
            $table->date('data_alteracao_status')->nullable();
            $table->date('data_alteracao_substatus')->nullable();
            $table->date('data_prazo')->nullable();
            $table->date('data_primeiro_leilao')->nullable();
            $table->date('data_segundo_leilao')->nullable();
            $table->text('parecer')->nullable();
            $table->timestamp('data_ultimo_andamento')->nullable();
            $table->boolean('arquivado')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cooperativa_id', 'arquivado']);
            $table->index(['cooperativa_id', 'tipo_status_id']);
            $table->index(['cooperativa_id', 'tipo_substatus_id']);
            $table->index(['cooperativa_id', 'updated_at']);
            $table->index('data_prazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('casos');
    }
};
