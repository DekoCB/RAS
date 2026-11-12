<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULO 7 - Biblioteca.
 * RF-45: seguimiento de lectura por grado en los tres niveles.
 * RF-46: ficha de resumen con autor, tiempo de lectura, editorial y resumen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biblioteca_libros', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('autor')->nullable();
            $table->string('editorial')->nullable();
            $table->string('codigo')->nullable()->unique();
            $table->string('nivel')->nullable();             // Inicial | Primaria | Secundaria
            $table->foreignId('grado_id')->nullable()->constrained('grados')->nullOnDelete();
            $table->unsignedInteger('ejemplares')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['nivel', 'grado_id']);
        });

        Schema::create('biblioteca_lecturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            // El libro puede no estar en el catalogo (lectura libre): se guarda el titulo suelto.
            $table->foreignId('libro_id')->nullable()->constrained('biblioteca_libros')->nullOnDelete();
            $table->string('titulo');
            $table->string('autor')->nullable();
            $table->string('editorial')->nullable();
            $table->unsignedInteger('tiempo_lectura')->nullable();   // minutos (RF-46)
            $table->text('resumen')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado')->default('en_curso');            // en_curso | terminado
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['estudiante_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biblioteca_lecturas');
        Schema::dropIfExists('biblioteca_libros');
    }
};
