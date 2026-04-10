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
        Schema::table('configuracoes_gerais', function (Blueprint $table) {
            $table->string('login_badge_text')->nullable()->after('logo_path');
            $table->string('login_title')->nullable()->after('login_badge_text');
            $table->text('login_description')->nullable()->after('login_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracoes_gerais', function (Blueprint $table) {
            $table->dropColumn([
                'login_badge_text',
                'login_title',
                'login_description',
            ]);
        });
    }
};

