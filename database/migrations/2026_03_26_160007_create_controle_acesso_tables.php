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
        Schema::create('papeis', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->string('modulo')->nullable();
            $table->timestamps();
        });

        Schema::create('papel_permissao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('papel_id')
                ->constrained('papeis')
                ->cascadeOnDelete();
            $table->foreignId('permissao_id')
                ->constrained('permissoes')
                ->cascadeOnDelete();

            $table->unique(['papel_id', 'permissao_id']);
        });

        Schema::create('user_papel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('papel_id')
                ->constrained('papeis')
                ->cascadeOnDelete();

            $table->unique('user_id');
            $table->unique(['user_id', 'papel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_papel');
        Schema::dropIfExists('papel_permissao');
        Schema::dropIfExists('permissoes');
        Schema::dropIfExists('papeis');
    }
};

