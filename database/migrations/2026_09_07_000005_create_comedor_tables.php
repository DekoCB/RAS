<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULO 8 - Comedor R.A.S.
 * RF-47: cobro del servicio a estudiantes y docentes.
 * RF-48: recepcion diaria de almuerzos, incluido quien trae el suyo.
 * RF-49: evidencia en foto y video del servicio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comedor_registros', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            // Un registro es de un estudiante o de un docente, nunca de ambos.
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->cascadeOnDelete();
            $table->string('origen')->default('comedor');     // comedor | propio (RF-48)
            $table->time('hora_recepcion')->nullable();
            $table->decimal('monto', 8, 2)->default(0);
            $table->boolean('pagado')->default(false);
            $table->string('observacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['fecha', 'origen']);
            $table->index(['estudiante_id', 'fecha']);
        });

        Schema::create('comedor_evidencias', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('tipo')->default('foto');          // foto | video
            $table->string('archivo');
            $table->string('descripcion')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comedor_evidencias');
        Schema::dropIfExists('comedor_registros');
    }
};
