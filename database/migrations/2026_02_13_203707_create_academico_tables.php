<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('estado')->default('presente');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['fecha', 'estudiante_id']);
        });

        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('curso_id')->constrained('cursos');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('periodo');
            $table->decimal('valor', 5, 2)->nullable();
            $table->string('estado')->default('abierta');
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
        });

        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('reportado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_reporte');
            $table->string('estado')->default('abierta');
            $table->text('descripcion');
            $table->text('seguimiento')->nullable();
            $table->timestamps();
        });

        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->foreignId('curso_id')->constrained('cursos');
            $table->foreignId('docente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('incidencias');
        Schema::dropIfExists('notas');
        Schema::dropIfExists('asistencias');
    }
};
