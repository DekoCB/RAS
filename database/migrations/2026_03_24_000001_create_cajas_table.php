<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anios_escolares');
            $table->foreignId('usuario_id')->constrained('users');
            $table->decimal('saldo_inicial', 10, 2)->default(0);
            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('estado')->default('abierta'); // abierta | cerrada
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('caja_denominaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->decimal('denominacion', 8, 2); // 0.10, 0.20, 0.50, 1, 2, 5, 10, 20, 50, 100, 200
            $table->integer('cantidad')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_denominaciones');
        Schema::dropIfExists('cajas');
    }
};
