<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Infraestructura compartida por varios modulos del documento.
 *
 * Buena parte de los requerimientos pide "mantener" o "registrar"
 * documentos, reuniones o capacitaciones. En vez de repetir la misma
 * tabla en cada modulo, se centralizan y se separan por `modulo`.
 *
 * Documentos  -> RF-41 (coordinacion), RF-57, RF-61, RF-62, RF-63 (RRHH),
 *                RF-65 (padres), RF-71 (defensa civil), RF-72 (legales)
 * Reuniones   -> RF-43, RF-44 (coordinacion), RF-64 (padres)
 * Capacitaciones -> RF-60 (docentes y pasantias), RF-66 (padres)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            // coordinacion | rrhh | padres | legal | defensa_civil | inmobiliario | pastoral | psicologia
            $table->string('modulo');
            $table->string('categoria');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('nivel')->nullable();
            $table->year('anio')->nullable();
            $table->string('archivo')->nullable();
            $table->string('nombre_original')->nullable();
            // Un documento puede pertenecer a una persona concreta (CV, declaracion jurada)
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->cascadeOnDelete();
            $table->boolean('confidencial')->default(false);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['modulo', 'categoria']);
            $table->index(['modulo', 'anio']);
        });

        Schema::create('reuniones', function (Blueprint $table) {
            $table->id();
            // coordinacion_docentes | docentes_padres | padres_familia
            $table->string('tipo');
            $table->string('titulo');
            $table->text('agenda')->nullable();
            $table->dateTime('fecha');
            $table->string('lugar')->nullable();
            $table->string('responsable')->nullable();
            $table->string('nivel')->nullable();
            $table->foreignId('grado_id')->nullable()->constrained('grados')->nullOnDelete();
            // programada | realizada | cancelada
            $table->string('estado')->default('programada');
            $table->text('acuerdos')->nullable();
            $table->string('acta_archivo')->nullable();
            $table->unsignedInteger('asistentes')->nullable();
            $table->boolean('notificada')->default(false);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
        });

        Schema::create('capacitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('dirigido_a');                 // docentes | padres
            $table->string('tipo')->default('capacitacion'); // capacitacion | pasantia
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('lugar')->nullable();
            $table->string('facilitador')->nullable();
            $table->unsignedInteger('horas')->nullable();
            $table->string('evidencia')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['dirigido_a', 'fecha_inicio']);
        });

        // RF-65: cartas de compromiso firmadas por el padre de familia
        Schema::create('cartas_compromiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('apoderado_id')->nullable()->constrained('apoderados')->nullOnDelete();
            $table->year('anio');
            $table->string('titulo')->default('Carta de compromiso');
            $table->text('compromisos')->nullable();
            $table->boolean('firmada')->default(false);
            $table->date('fecha_firma')->nullable();
            $table->string('archivo')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['estudiante_id', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartas_compromiso');
        Schema::dropIfExists('capacitaciones');
        Schema::dropIfExists('reuniones');
        Schema::dropIfExists('documentos');
    }
};
