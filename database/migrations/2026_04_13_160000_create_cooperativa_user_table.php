<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cooperativa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('cooperativa_id')
                ->constrained('cooperativas')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'cooperativa_id'], 'cooperativa_user_unique');
            $table->index('cooperativa_id');
        });

        $agora = now();

        DB::table('users')
            ->select(['id', 'cooperativa_id'])
            ->whereNotNull('cooperativa_id')
            ->orderBy('id')
            ->chunkById(500, function ($usuarios) use ($agora): void {
                $vinculos = $usuarios
                    ->map(function ($usuario) use ($agora): array {
                        return [
                            'user_id' => (int) $usuario->id,
                            'cooperativa_id' => (int) $usuario->cooperativa_id,
                            'created_at' => $agora,
                            'updated_at' => $agora,
                        ];
                    })
                    ->all();

                if ($vinculos === []) {
                    return;
                }

                DB::table('cooperativa_user')->upsert(
                    $vinculos,
                    ['user_id', 'cooperativa_id'],
                    ['updated_at']
                );
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperativa_user');
    }
};
