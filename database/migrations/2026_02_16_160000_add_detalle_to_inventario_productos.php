<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_productos', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('precio');
            $table->string('imagen_url')->nullable()->after('descripcion');
            $table->boolean('destacado')->default(false)->after('imagen_url');
        });
    }

    public function down(): void
    {
        Schema::table('inventario_productos', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'imagen_url', 'destacado']);
        });
    }
};
