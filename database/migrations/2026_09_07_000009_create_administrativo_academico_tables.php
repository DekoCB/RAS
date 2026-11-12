<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULO 14 - Administrativo (RF-70, RF-71)
 * MODULO 5  - Academico, lo que faltaba (RF-31, RF-33, RF-34, RF-35, RF-37, RF-38, RF-39)
 */
return new class extends Migration
{
    public function up(): void
    {
        // RF-70: modulo inmobiliario y de operaciones de infraestructura
        Schema::create('activos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->unique();
            $table->string('nombre');
            $table->string('categoria');                 // inmueble | mobiliario | equipo | vehiculo
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->string('ubicacion')->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('valor', 12, 2)->nullable();
            $table->string('estado')->default('operativo'); // operativo | mantenimiento | baja
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['categoria', 'estado']);
        });

        Schema::create('activo_mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('tipo')->default('preventivo'); // preventivo | correctivo
            $table->string('responsable')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->text('detalle')->nullable();
            $table->timestamps();
        });

        // RF-71: defensa civil
        Schema::create('defensa_civil_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->string('tipo');                      // simulacro | inspeccion | senalizacion | extintores
            $table->string('titulo');
            $table->date('fecha');
            $table->string('responsable')->nullable();
            $table->unsignedInteger('participantes')->nullable();
            $table->string('resultado')->nullable();     // conforme | observado | pendiente
            $table->text('observacion')->nullable();
            $table->date('proxima_fecha')->nullable();
            $table->string('evidencia')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
        });

        // RF-34: informes descriptivos de Inicial, mensuales y bimestrales
        Schema::create('informes_descriptivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->string('tipo')->default('mensual');  // mensual | bimestral
            $table->string('periodo');                   // Marzo, Bimestre I...
            $table->text('logros')->nullable();
            $table->text('dificultades')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'anio_escolar_id', 'tipo', 'periodo'], 'informe_unico');
        });

        // RF-35: rubricas de evaluacion
        Schema::create('rubricas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->string('nivel')->nullable();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            // Cada criterio guarda su descriptor por nivel de desempeno
            $table->json('criterios')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // RF-37: examenes mensuales y bimestrales, base del ranking
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->foreignId('seccion_id')->nullable()->constrained('secciones')->nullOnDelete();
            $table->string('tipo')->default('mensual');  // mensual | bimestral | diagnostico
            $table->string('titulo');
            $table->string('periodo')->nullable();
            $table->date('fecha');
            $table->decimal('puntaje_maximo', 6, 2)->default(20);
            $table->timestamps();

            $table->index(['anio_escolar_id', 'tipo']);
        });

        Schema::create('evaluacion_resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained('evaluaciones')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->decimal('puntaje', 6, 2)->nullable();
            $table->string('observacion')->nullable();
            $table->timestamps();

            $table->unique(['evaluacion_id', 'estudiante_id']);
        });

        // RF-38: recuperacion, refuerzo y nivelacion
        Schema::create('recuperaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->string('tipo')->default('recuperacion'); // recuperacion | refuerzo | nivelacion
            $table->string('periodo')->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('nota_original', 5, 2)->nullable();
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->decimal('costo', 8, 2)->default(0);
            $table->boolean('pagado')->default(false);
            $table->text('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['estudiante_id', 'tipo']);
        });

        // RF-39: metodos de aprendizaje y proyectos pedagogicos
        Schema::create('proyectos_pedagogicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('tipo')->default('proyecto');  // proyecto | metodo
            $table->string('nivel')->nullable();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('responsable')->nullable();
            $table->string('estado')->default('en_curso'); // en_curso | terminado
            $table->timestamps();
        });

        // RF-31: modalidad docente por grado (unidocencia / polidocencia)
        Schema::table('grados', function (Blueprint $table) {
            $table->string('modalidad')->nullable()->after('nivel');
        });

        // RF-33: nivel de logro 1 a 4 para Secundaria
        Schema::table('notas', function (Blueprint $table) {
            $table->unsignedTinyInteger('nivel_logro')->nullable()->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('notas', fn (Blueprint $t) => $t->dropColumn('nivel_logro'));
        Schema::table('grados', fn (Blueprint $t) => $t->dropColumn('modalidad'));

        Schema::dropIfExists('proyectos_pedagogicos');
        Schema::dropIfExists('recuperaciones');
        Schema::dropIfExists('evaluacion_resultados');
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('rubricas');
        Schema::dropIfExists('informes_descriptivos');
        Schema::dropIfExists('defensa_civil_actividades');
        Schema::dropIfExists('activo_mantenimientos');
        Schema::dropIfExists('activos');
    }
};
