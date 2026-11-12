<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo')->default('producto');
            $table->string('sku')->nullable()->index();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('precio', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_producto_id')->constrained('inventario_productos')->cascadeOnDelete();
            $table->string('tipo');
            $table->unsignedInteger('cantidad');
            $table->decimal('costo_unitario', 10, 2)->nullable();
            $table->string('referencia')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_movimiento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('inventario_productos');
    }
};
