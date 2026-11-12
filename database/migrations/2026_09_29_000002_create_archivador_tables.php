<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archivador: carpetas libres creadas por el colegio.
 *
 * A diferencia del repositorio documental (tabla `documentos`), donde las
 * carpetas son categorias fijas escritas en codigo, aqui la direccion crea
 * las carpetas que necesite y puede subir una carpeta entera del PC
 * conservando su estructura.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpetas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            // Nulo = carpeta en la raiz del archivador.
            $table->foreignId('parent_id')->nullable()->constrained('carpetas')->cascadeOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Dos carpetas hermanas no pueden llamarse igual.
            $table->unique(['parent_id', 'nombre']);
        });

        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            // Nulo = archivo suelto en la raiz.
            $table->foreignId('carpeta_id')->nullable()->constrained('carpetas')->cascadeOnDelete();
            $table->string('nombre');                    // como lo ve el usuario
            $table->string('ruta');                      // donde vive en disco (nombre generado)
            $table->string('extension', 20)->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('tamano')->default(0);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['carpeta_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos');
        Schema::dropIfExists('carpetas');
    }
};
