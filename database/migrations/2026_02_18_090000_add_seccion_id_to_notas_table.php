<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->foreignId('seccion_id')
                ->nullable()
                ->after('curso_id')
                ->constrained('secciones')
                ->nullOnDelete();

            $table->unique(
                ['anio_escolar_id', 'seccion_id', 'curso_id', 'estudiante_id', 'periodo'],
                'notas_unq_bimestre'
            );
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE notas
                SET seccion_id = (
                    SELECT m.seccion_id
                    FROM matriculas m
                    WHERE m.estudiante_id = notas.estudiante_id
                      AND m.anio_escolar_id = notas.anio_escolar_id
                    LIMIT 1
                )
                WHERE seccion_id IS NULL
            ');
        } else {
            DB::statement('
                UPDATE notas n
                JOIN matriculas m
                  ON m.estudiante_id = n.estudiante_id
                 AND m.anio_escolar_id = n.anio_escolar_id
                SET n.seccion_id = m.seccion_id
                WHERE n.seccion_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->dropUnique('notas_unq_bimestre');
            $table->dropConstrainedForeignId('seccion_id');
        });
    }
};
