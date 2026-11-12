<?php

use App\Models\InventarioCategoria;
use App\Models\InventarioProducto;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('slug')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('inventario_productos', function (Blueprint $table) {
            $table->foreignId('categoria_id')
                ->nullable()
                ->after('tipo')
                ->constrained('inventario_categorias')
                ->nullOnDelete();
        });

        if (Schema::hasTable('inventario_productos')) {
            $tipos = InventarioProducto::query()
                ->select('tipo')
                ->distinct()
                ->pluck('tipo')
                ->filter()
                ->values();

            foreach ($tipos as $tipo) {
                $slug = Str::slug($tipo);
                $categoria = InventarioCategoria::firstOrCreate(
                    ['slug' => $slug],
                    ['nombre' => $tipo, 'activo' => true]
                );

                InventarioProducto::where('tipo', $tipo)
                    ->whereNull('categoria_id')
                    ->update(['categoria_id' => $categoria->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('inventario_productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_id');
        });

        Schema::dropIfExists('inventario_categorias');
    }
};
