<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Organizador dono do evento (Sprint 3 e 5).
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            // Eventos em rascunho não aparecem na página principal.
            $table->boolean('is_published')->default(true)->after('data_evento');
        });

        Schema::table('perguntas', function (Blueprint $table) {
            // Autor da pergunta (Sprint 2).
            $table->foreignId('user_id')->nullable()->after('evento_id')->constrained('users')->nullOnDelete();
            // Moderação: a pergunta só aparece no telão depois de aprovada (Sprint 3).
            $table->boolean('is_public')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('perguntas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('is_public');
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('is_published');
        });
    }
};
