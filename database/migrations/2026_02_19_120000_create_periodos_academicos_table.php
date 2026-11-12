<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares')->cascadeOnDelete();
            $table->string('codigo');
            $table->string('nombre');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('es_vacacional')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(false);
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'codigo']);
            $table->unique(['anio_escolar_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
