<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asistencias_auxiliar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->nullable()->constrained('anios_escolares');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('fecha');
            $table->enum('tipo', ['ENTRADA', 'SALIDA'])->default('ENTRADA');
            $table->foreignId('registrado_por')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['user_id', 'fecha', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_auxiliar');
    }
};
