<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos institucionales editables (RF-03, RF-04, RF-10).
 * Sustituyen al nombre y la ciudad que hasta ahora estaban escritos
 * a mano en cada vista.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institucion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('sigla')->nullable();
            $table->string('lema')->nullable();
            $table->text('vision')->nullable();
            $table->text('mision')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('telefono_central')->nullable();
            $table->string('telefono_secundaria')->nullable();
            $table->string('telefono_adicional')->nullable();
            $table->string('email')->nullable();
            $table->json('codigos_modulares')->nullable();
            $table->string('himno_titulo')->nullable();
            $table->string('himno_autor')->nullable();
            $table->string('himno_audio')->nullable();
            $table->string('video_institucional')->nullable();
            $table->timestamps();
        });

        // RF-03: valores institucionales
        Schema::create('institucion_valores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // RF-03: frases de cultura institucional
        Schema::create('institucion_frases', function (Blueprint $table) {
            $table->id();
            $table->string('frase');
            $table->string('area')->nullable();   // Restaurante, Psicologia, Karate...
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institucion_frases');
        Schema::dropIfExists('institucion_valores');
        Schema::dropIfExists('institucion');
    }
};
