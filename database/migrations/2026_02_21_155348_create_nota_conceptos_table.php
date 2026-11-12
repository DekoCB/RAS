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
        Schema::create('nota_conceptos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos');
            $table->foreignId('curso_id')->constrained('cursos');
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->foreignId('docente_id')->constrained('docentes');

            // Este es el “Mensual 1 / Bimestre 1 / …” (misma lógica que notas.periodo)
            $table->string('periodo');

            $table->string('nombre');               // FastTest, Simulacro, etc.
            $table->decimal('porcentaje', 5, 2);    // 0.00 - 100.00

            $table->timestamps();

            // Evita duplicados: mismo concepto repetido dentro de la misma evaluación
            $table->unique([
                'anio_escolar_id',
                'periodo_academico_id',
                'curso_id',
                'seccion_id',
                'docente_id',
                'periodo',
                'nombre'
            ], 'uq_concepto_por_eval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_conceptos');
    }
};
