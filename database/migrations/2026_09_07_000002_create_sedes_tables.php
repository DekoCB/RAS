<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sedes o locales del colegio (RF-08, RF-10, RF-11) y los documentos
 * versionados por anio que cada sede publica (RF-08, RF-20).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                       // Local 1, Local 2
            $table->string('direccion');
            $table->string('referencia')->nullable();
            $table->string('telefono')->nullable();
            $table->json('niveles')->nullable();            // ["Inicial"] / ["Primaria","Secundaria"]
            $table->string('imagen_portada')->nullable();
            $table->string('mapa_url')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('sede_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            // reglamento_admision | informacion_general | reglamento_interno | informativo | utiles
            $table->string('tipo');
            $table->string('titulo');
            $table->year('anio');
            $table->string('nivel')->nullable();            // para la lista de utiles (RF-09)
            $table->string('archivo');                      // ruta en storage
            $table->boolean('publicado')->default(true);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sede_id', 'tipo', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sede_documentos');
        Schema::dropIfExists('sedes');
    }
};
