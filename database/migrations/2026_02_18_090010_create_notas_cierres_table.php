<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_cierres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->string('periodo');
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'periodo', 'curso_id'], 'notas_cierres_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_cierres');
    }
};
