<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_ventas', function (Blueprint $table) {
            $table->json('pagos_detalle')->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('inventario_ventas', function (Blueprint $table) {
            $table->dropColumn('pagos_detalle');
        });
    }
};
