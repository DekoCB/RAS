<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_ventas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_venta');
            $table->string('tipo_comprador', 20);
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->nullOnDelete();
            $table->string('comprador_nombre')->nullable();
            $table->string('comprador_documento', 20)->nullable();
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago', 20);
            $table->string('estado', 20)->default('pagado');
            $table->string('voucher_codigo')->unique();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->timestamps();
        });

        Schema::create('inventario_venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_venta_id')->constrained('inventario_ventas')->cascadeOnDelete();
            $table->foreignId('inventario_producto_id')->constrained('inventario_productos')->cascadeOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_venta_detalles');
        Schema::dropIfExists('inventario_ventas');
    }
};
