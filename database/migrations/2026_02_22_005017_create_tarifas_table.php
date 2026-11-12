<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();

            // FK: año escolar
            $table->unsignedBigInteger('anio_escolar_id');

            // FK: concepto (Matrícula, Pensión, etc.)
            $table->unsignedBigInteger('concepto_pago_id');

            // Nivel como texto: Inicial / Primaria / Secundaria
            $table->string('nivel', 30);

            // Monto oficial
            $table->decimal('monto', 10, 2);

            // Control de vigencia (opcional pero recomendado)
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();

            // Estado simple
            $table->boolean('activo')->default(true);

            // Auditoría básica (opcional)
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('aprobado_por')->nullable();

            $table->timestamps();

            // Índice/Regla: evitar duplicados de tarifa activa por año+nivel+concepto
            // (NO garantiza "solo una activa" si usas vigencias, pero ayuda mucho)
            $table->unique(['anio_escolar_id', 'concepto_pago_id', 'nivel'], 'uq_tarifas_anio_concepto_nivel');

            // FKs
            $table->foreign('anio_escolar_id')
                ->references('id')->on('anios_escolares')
                ->onDelete('cascade');

            $table->foreign('concepto_pago_id')
                ->references('id')->on('conceptos_pago')
                ->onDelete('restrict');

            $table->foreign('creado_por')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('aprobado_por')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};