<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULO 2 - Admision (RF-11 a RF-20).
 *
 * El expediente del postulante vive en `postulantes`; el resto de tablas
 * cuelga de el: documentos adjuntos, etapas del proceso, el pago del
 * derecho de admision y el examen de conocimientos.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Expediente del postulante (RF-11, RF-12)
        Schema::create('postulantes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->string('nivel');
            $table->foreignId('grado_id')->nullable()->constrained('grados')->nullOnDelete();

            // Postulante
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('dni', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('direccion')->nullable();
            $table->string('colegio_procedencia')->nullable();

            // Apoderado que presenta la postulacion
            $table->string('apoderado_nombres')->nullable();
            $table->string('apoderado_apellidos')->nullable();
            $table->string('apoderado_dni', 20)->nullable();
            $table->string('apoderado_parentesco')->nullable();
            $table->string('apoderado_telefono', 40)->nullable();
            $table->string('apoderado_email')->nullable();

            // borrador | en_proceso | admitido | no_admitido | desistido
            $table->string('estado')->default('borrador');
            $table->unsignedTinyInteger('paso_actual')->default(1);
            $table->text('observacion')->nullable();
            $table->timestamp('resultado_publicado_at')->nullable();
            $table->string('resultado_canal')->nullable();     // como se comunico (RF-18)

            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['anio_escolar_id', 'estado']);
            $table->index(['nivel', 'grado_id']);
        });

        // RF-14: documentos adjuntos del postulante
        Schema::create('postulante_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulantes')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('archivo');
            $table->string('nombre_original')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['postulante_id', 'tipo']);
        });

        // RF-15, RF-16 y RF-19: etapas formales del proceso
        Schema::create('postulante_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulantes')->cascadeOnDelete();
            $table->string('etapa');
            // pendiente | programada | completada | no_asistio
            $table->string('estado')->default('pendiente');
            $table->dateTime('fecha_programada')->nullable();
            $table->dateTime('fecha_realizada')->nullable();
            $table->string('responsable')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['postulante_id', 'etapa']);
        });

        // RF-13: pago del derecho de admision
        Schema::create('postulante_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulantes')->cascadeOnDelete();
            $table->decimal('monto', 8, 2);
            $table->string('metodo')->default('yape');          // yape | efectivo | transferencia
            $table->string('numero_operacion')->nullable();
            $table->date('fecha');
            $table->string('voucher')->nullable();
            $table->string('estado')->default('pendiente');     // pendiente | validado | rechazado
            $table->text('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['postulante_id', 'estado']);
        });

        // RF-17: definicion del examen, diferenciado por nivel
        Schema::create('admision_examenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->string('nivel');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->decimal('puntaje_maximo', 6, 2)->default(20);
            $table->decimal('puntaje_minimo', 6, 2)->default(11);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['anio_escolar_id', 'nivel']);
        });

        // RF-17: aplicacion del examen a un postulante
        Schema::create('postulante_examenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulantes')->cascadeOnDelete();
            $table->foreignId('examen_id')->constrained('admision_examenes')->cascadeOnDelete();
            $table->dateTime('fecha')->nullable();
            $table->decimal('puntaje', 6, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('evaluado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['postulante_id', 'examen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulante_examenes');
        Schema::dropIfExists('admision_examenes');
        Schema::dropIfExists('postulante_pagos');
        Schema::dropIfExists('postulante_etapas');
        Schema::dropIfExists('postulante_documentos');
        Schema::dropIfExists('postulantes');
    }
};
