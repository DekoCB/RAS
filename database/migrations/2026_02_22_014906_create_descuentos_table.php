<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descuentos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('anio_escolar_id')->nullable();
            $table->unsignedBigInteger('concepto_pago_id'); // normalmente Pensión
            $table->string('nivel', 30)->nullable(); // null = todos los niveles

            $table->string('tipo', 30); // HERMANOS, PRONTO_PAGO, BECA, OTRO
            $table->string('aplica_a', 30)->nullable(); // MENOR, SEGUNDO_EN_ADELANTE, TODOS (para hermanos)

            $table->decimal('porcentaje', 5, 2)->nullable(); // 5.00
            $table->decimal('monto_fijo', 10, 2)->nullable(); // si fuera fijo
            $table->unsignedTinyInteger('dia_limite')->nullable(); // pronto pago (ej: 5)

            $table->boolean('activo')->default(true);

            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->foreign('anio_escolar_id')
                ->references('id')->on('anios_escolares')
                ->nullOnDelete();

            $table->foreign('concepto_pago_id')
                ->references('id')->on('conceptos_pago')
                ->onDelete('restrict');

            $table->foreign('creado_por')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descuentos');
    }
};
