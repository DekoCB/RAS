<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Añadir campos extra a notas_cierres
        Schema::table('notas_cierres', function (Blueprint $table) {
            if (!Schema::hasColumn('notas_cierres', 'seccion_id')) {
                $table->foreignId('seccion_id')
                    ->nullable()
                    ->after('curso_id')
                    ->constrained('secciones')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('notas_cierres', 'motivo')) {
                $table->text('motivo')->nullable()->after('fecha_cierre');
            }
            if (!Schema::hasColumn('notas_cierres', 'es_forzado')) {
                $table->boolean('es_forzado')->default(false)->after('motivo');
            }
        });

        // Tabla de historial de cierres/reaperturas
        if (!Schema::hasTable('historial_notas_cierres')) {
            Schema::create('historial_notas_cierres', function (Blueprint $table) {
                $table->id();
                $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
                $table->foreignId('periodo_academico_id')
                    ->nullable()
                    ->constrained('periodos_academicos')
                    ->nullOnDelete();
                $table->string('periodo', 50);
                $table->foreignId('seccion_id')
                    ->nullable()
                    ->constrained('secciones')
                    ->nullOnDelete();
                $table->foreignId('curso_id')
                    ->nullable()
                    ->constrained('cursos')
                    ->nullOnDelete();
                $table->string('accion', 30); // cerrar, reabrir, cierre_forzado
                $table->foreignId('realizado_por')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->text('motivo')->nullable();
                $table->json('datos_resumen')->nullable();
                $table->timestamps();

                $table->index(['anio_escolar_id', 'periodo_academico_id', 'periodo'], 'historial_nc_anio_periodo_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_notas_cierres');

        Schema::table('notas_cierres', function (Blueprint $table) {
            if (Schema::hasColumn('notas_cierres', 'es_forzado')) {
                $table->dropColumn('es_forzado');
            }
            if (Schema::hasColumn('notas_cierres', 'motivo')) {
                $table->dropColumn('motivo');
            }
            if (Schema::hasColumn('notas_cierres', 'seccion_id')) {
                $table->dropConstrainedForeignId('seccion_id');
            }
        });
    }
};
