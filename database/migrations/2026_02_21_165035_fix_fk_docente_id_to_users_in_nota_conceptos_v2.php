<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- nota_conceptos: docente_id -> users.id
        Schema::table('nota_conceptos', function (Blueprint $table) {
            $table->dropForeign(['docente_id']); // quita FK actual (probablemente a docentes)
        });

        Schema::table('nota_conceptos', function (Blueprint $table) {
            $table->foreign('docente_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        // --- nota_concepto_detalles: docente_id -> users.id
        Schema::table('nota_concepto_detalles', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
        });

        Schema::table('nota_concepto_detalles', function (Blueprint $table) {
            $table->foreign('docente_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Eliminar filas con docente_id que no existen en docentes (fueron migrados a users)
        $docenteIds = DB::table('docentes')->pluck('id');
        DB::table('nota_conceptos')->whereNotIn('docente_id', $docenteIds)->delete();
        DB::table('nota_concepto_detalles')->whereNotIn('docente_id', $docenteIds)->delete();

        try {
            Schema::table('nota_conceptos', function (Blueprint $table) {
                $table->dropForeign(['docente_id']);
            });
        } catch (\Exception $e) {}

        Schema::table('nota_conceptos', function (Blueprint $table) {
            $table->foreign('docente_id')
                ->references('id')
                ->on('docentes')
                ->cascadeOnDelete();
        });

        try {
            Schema::table('nota_concepto_detalles', function (Blueprint $table) {
                $table->dropForeign(['docente_id']);
            });
        } catch (\Exception $e) {}

        Schema::table('nota_concepto_detalles', function (Blueprint $table) {
            $table->foreign('docente_id')
                ->references('id')
                ->on('docentes')
                ->cascadeOnDelete();
        });
    }
};