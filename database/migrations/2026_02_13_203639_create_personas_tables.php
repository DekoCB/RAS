<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apoderados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('dni')->nullable()->index();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 10)->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('direccion')->nullable();
            $table->timestamps();
        });

        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('codigo')->nullable()->index();
            $table->string('carnet_codigo')->nullable()->unique();
            $table->string('carnet_path')->nullable();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('dni')->nullable()->index();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 10)->nullable();
            $table->string('ciudad')->nullable();
            $table->string('direccion')->nullable();
            $table->string('estado')->default('activo');
            $table->timestamps();
        });

        Schema::create('apoderado_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apoderado_id')->constrained('apoderados')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->string('relacion')->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();

            $table->unique(['apoderado_id', 'estudiante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apoderado_estudiante');
        Schema::dropIfExists('estudiantes');
        Schema::dropIfExists('apoderados');
    }
};
