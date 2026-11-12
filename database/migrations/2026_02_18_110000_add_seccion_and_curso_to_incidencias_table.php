<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->foreignId('seccion_id')
                ->nullable()
                ->after('anio_escolar_id')
                ->constrained('secciones')
                ->nullOnDelete();

            $table->foreignId('curso_id')
                ->nullable()
                ->after('seccion_id')
                ->constrained('cursos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curso_id');
            $table->dropConstrainedForeignId('seccion_id');
        });
    }
};
