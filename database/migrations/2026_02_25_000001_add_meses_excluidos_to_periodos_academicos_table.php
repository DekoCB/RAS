<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('periodos_academicos', 'meses_excluidos')) {
            Schema::table('periodos_academicos', function (Blueprint $table) {
                $table->json('meses_excluidos')->nullable()->after('fecha_fin');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('periodos_academicos', 'meses_excluidos')) {
            Schema::table('periodos_academicos', function (Blueprint $table) {
                $table->dropColumn('meses_excluidos');
            });
        }
    }
};

