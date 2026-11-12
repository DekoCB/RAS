<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anios_escolares', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(false);
            $table->timestamps();
        });

        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nivel')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedInteger('capacidad')->nullable();
            $table->timestamps();
        });

        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('grado_id')->constrained('grados');
            $table->foreignId('aula_id')->nullable()->constrained('aulas');
            $table->string('nombre');
            $table->string('turno')->nullable();
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'grado_id', 'nombre']);
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('secciones');
        Schema::dropIfExists('aulas');
        Schema::dropIfExists('grados');
        Schema::dropIfExists('anios_escolares');
    }
};
