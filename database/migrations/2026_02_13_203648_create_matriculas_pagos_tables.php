<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('grado_id')->constrained('grados');
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->string('situacion_alumno')->nullable();
            $table->string('institucion_procedencia')->nullable();
            $table->boolean('repitente')->default(false);
            $table->date('fecha_matricula')->nullable();
            $table->string('estado')->default('registrada');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'estudiante_id']);
        });

        Schema::create('conceptos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo')->default('otro');
            $table->decimal('monto_base', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('deudas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('concepto_pago_id')->constrained('conceptos_pago');
            $table->string('periodo')->nullable();
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
            $table->string('estado')->default('pendiente');
            $table->timestamps();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_pago');
            $table->string('metodo')->nullable();
            $table->string('estado')->default('pendiente');
            $table->foreignId('usuario_registra_id')->constrained('users');
            $table->text('comentario')->nullable();
            $table->timestamps();
        });

        Schema::create('deuda_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deuda_id')->constrained('deudas')->cascadeOnDelete();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
            $table->decimal('monto_aplicado', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['deuda_id', 'pago_id']);
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deuda_id')->nullable()->constrained('deudas')->nullOnDelete();
            $table->foreignId('pago_id')->nullable()->constrained('pagos')->nullOnDelete();
            $table->string('archivo_path');
            $table->string('estado')->default('pendiente');
            $table->text('comentario_validacion')->nullable();
            $table->foreignId('usuario_valida_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('deuda_pagos');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('deudas');
        Schema::dropIfExists('conceptos_pago');
        Schema::dropIfExists('matriculas');
    }
};
