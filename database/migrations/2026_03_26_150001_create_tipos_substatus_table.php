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
        Schema::create('tipos_substatus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_status_id')
                ->nullable()
                ->constrained('tipos_status')
                ->nullOnDelete();
            $table->string('nome')->unique();
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['tipo_status_id', 'ativo', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_substatus');
    }
};
