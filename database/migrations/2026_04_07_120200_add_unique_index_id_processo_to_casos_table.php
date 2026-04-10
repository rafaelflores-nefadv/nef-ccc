<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            <<<SQL
            WITH duplicados AS (
                SELECT
                    id,
                    ROW_NUMBER() OVER (
                        PARTITION BY id_processo
                        ORDER BY
                            CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END,
                            updated_at DESC NULLS LAST,
                            id DESC
                    ) AS linha
                FROM casos
                WHERE id_processo IS NOT NULL
            )
            UPDATE casos
            SET
                deleted_at = NOW(),
                updated_at = NOW()
            WHERE id IN (
                SELECT id
                FROM duplicados
                WHERE linha > 1
                  AND id IN (SELECT id FROM casos WHERE deleted_at IS NULL)
            )
            SQL
        );

        DB::statement(
            <<<SQL
            CREATE UNIQUE INDEX IF NOT EXISTS casos_id_processo_unique_active
            ON casos (id_processo)
            WHERE id_processo IS NOT NULL
              AND deleted_at IS NULL
            SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS casos_id_processo_unique_active');
    }
};
