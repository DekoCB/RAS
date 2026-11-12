<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('horarios', 'periodo_academico_id')) {
            Schema::table('horarios', function (Blueprint $table) {
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->after('anio_escolar_id')
                    ->constrained('periodos_academicos')
                    ->nullOnDelete();

                $table->index(
                    ['anio_escolar_id', 'periodo_academico_id', 'seccion_id', 'dia_semana', 'hora_inicio'],
                    'horarios_periodo_slot_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('horarios', 'periodo_academico_id')) {
            Schema::table('horarios', function (Blueprint $table) {
                $table->dropForeign(['periodo_academico_id']);
                $table->dropColumn('periodo_academico_id');
            });

            try {
                Schema::table('horarios', function (Blueprint $table) {
                    $table->dropIndex('horarios_periodo_slot_index');
                });
            } catch (\Exception $e) {}
        }
    }
};
