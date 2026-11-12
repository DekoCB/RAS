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
        Schema::create('nota_concepto_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nota_concepto_id')->constrained('nota_conceptos')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes');

            $table->decimal('nota', 5, 2)->nullable(); // 0-20 (nullable por si aún no lo llenan)
            $table->foreignId('docente_id')->constrained('docentes');

            $table->timestamps();

            // Un alumno solo debe tener 1 nota por concepto
            $table->unique(['nota_concepto_id', 'estudiante_id'], 'uq_detalle_concepto_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_concepto_detalles');
    }
};
