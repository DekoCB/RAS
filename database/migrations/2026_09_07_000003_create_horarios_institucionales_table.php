<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Horarios de ingreso y salida por nivel/grado (RF-29) y horario de
 * recepcion de almuerzo por nivel (RF-30).
 *
 * grado_id nulo = la regla aplica a todo el nivel; con grado_id la regla
 * del grado tiene prioridad, porque el cliente definio la entrada por
 * nivel y la salida grado por grado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_institucionales', function (Blueprint $table) {
            $table->id();
            $table->string('nivel');                        // Inicial | Primaria | Secundaria
            $table->foreignId('grado_id')->nullable()->constrained('grados')->cascadeOnDelete();
            $table->time('hora_ingreso')->nullable();
            $table->time('hora_salida')->nullable();
            $table->time('almuerzo_inicio')->nullable();
            $table->time('almuerzo_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['nivel', 'grado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_institucionales');
    }
};
