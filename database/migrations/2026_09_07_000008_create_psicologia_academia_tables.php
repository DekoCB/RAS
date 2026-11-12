<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULO 9 - Psicologia (RF-50, RF-51)
 * MODULO 10 - Academia R.A.S (RF-52 a RF-55)
 * MODULO 11 - Pastoral (RF-56)
 */
return new class extends Migration
{
    public function up(): void
    {
        // RF-50: atenciones organizadas por las siete areas del departamento.
        // RNF-05: el contenido es sensible, de ahi el marcado de confidencial.
        Schema::create('psicologia_atenciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->cascadeOnDelete();
            $table->string('area');
            $table->string('motivo');
            $table->date('fecha');
            $table->string('responsable')->nullable();
            // abierta | en_seguimiento | cerrada
            $table->string('estado')->default('abierta');
            $table->text('observacion')->nullable();
            $table->text('acuerdos')->nullable();
            $table->date('proxima_cita')->nullable();
            $table->boolean('confidencial')->default(true);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['area', 'estado']);
            $table->index(['estudiante_id', 'fecha']);
        });

        // RF-52 a RF-55: alumnos de la academia preuniversitaria.
        Schema::create('academia_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            // regular = alumno del colegio | libre = externo (RF-54)
            $table->string('tipo')->default('regular');
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->nullOnDelete();
            // Datos propios para los estudiantes libres, que no estan en `estudiantes`
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('area')->nullable();          // Ciencias, Letras, Ingenierias...
            $table->decimal('pension', 8, 2)->default(0);
            $table->string('universidad_destino')->nullable();
            $table->boolean('ingreso_universidad')->default(false);
            $table->string('evidencia_ingreso')->nullable();   // RF-53
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['anio_escolar_id', 'tipo']);
        });

        Schema::create('academia_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('academia_alumnos')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('estado')->default('presente');   // presente | tardanza | falta | justificado
            $table->string('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['alumno_id', 'fecha']);
        });

        Schema::create('academia_evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('academia_alumnos')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('curso')->nullable();
            $table->date('fecha');
            $table->decimal('puntaje', 6, 2)->nullable();
            $table->decimal('puntaje_maximo', 6, 2)->default(20);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['alumno_id', 'fecha']);
        });

        // RF-55: materiales de estudio entregados
        Schema::create('academia_materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->nullable()->constrained('academia_alumnos')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('curso')->nullable();
            $table->date('fecha_entrega');
            $table->unsignedInteger('cantidad')->default(1);
            $table->string('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // RF-53: horario por area, y RF-52 el bloque de 4:00 a 5:30 p.m.
        Schema::create('academia_horarios', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->string('curso');
            $table->unsignedTinyInteger('dia');          // 1 = lunes ... 5 = viernes
            $table->time('hora_inicio')->default('16:00');
            $table->time('hora_fin')->default('17:30');
            $table->string('docente')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['area', 'dia']);
        });

        // RF-56 y RF-15: test pastoral y carta de declaracion de fe.
        // Se vincula al postulante cuando nace en admision, o al estudiante
        // cuando el area trabaja con alumnos ya matriculados.
        Schema::create('pastoral_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->nullable()->constrained('postulantes')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->cascadeOnDelete();
            $table->string('tipo');                      // test_pastoral | carta_fe | acompanamiento
            $table->date('fecha');
            $table->string('responsable')->nullable();
            $table->text('observacion')->nullable();
            $table->string('resultado')->nullable();     // favorable | con_observaciones | pendiente
            $table->string('archivo')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pastoral_registros');
        Schema::dropIfExists('academia_horarios');
        Schema::dropIfExists('academia_materiales');
        Schema::dropIfExists('academia_evaluaciones');
        Schema::dropIfExists('academia_asistencias');
        Schema::dropIfExists('academia_alumnos');
        Schema::dropIfExists('psicologia_atenciones');
    }
};
