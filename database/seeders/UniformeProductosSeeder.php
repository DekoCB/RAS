<?php

namespace Database\Seeders;

use App\Models\InventarioCategoria;
use App\Models\InventarioProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UniformeProductosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear o encontrar la categoría UNIFORME
        $categoria = InventarioCategoria::firstOrCreate(
            ['slug' => 'uniforme'],
            ['nombre' => 'UNIFORME', 'activo' => true]
        );

        // 2. Datos de productos: [ nombre => [ talla => precio, ... ] ]
        //    null = sin talla (producto único)
        $productos = [
            'YAMPER' => [
                'T-06' => 79.00,
                'T-08' => 81.00,
                'T-10' => 83.00,
                'T-12' => 85.00,
                'T-14' => 88.00,
                'T-16' => 90.00,
                'T-S'  => 92.00,
                'T-M'  => 98.00,
                'T-L'  => 101.00,
            ],
            'PANTALÓN' => [
                'T-06' => 69.00,
                'T-08' => 70.00,
                'T-10' => 73.00,
                'T-12' => 74.00,
                'T-14' => 76.00,
                'T-16' => 77.00,
                'T-28' => 81.00,
                'T-30' => 82.00,
                'T-32' => 83.00,
                'T-34' => 84.00,
            ],
            'CHOMPA' => [
                'T-06' => 45.00,
                'T-08' => 46.00,
                'T-10' => 47.00,
                'T-12' => 48.00,
                'T-14' => 49.00,
                'T-16' => 50.00,
                'T-S'  => 51.00,
                'T-M'  => 53.00,
                'T-L'  => 54.00,
                'T-XL' => 56.00,
            ],
            'CAMISA BORDADA' => [
                'T-06' => 40.00,
                'T-08' => 41.00,
                'T-10' => 42.00,
                'T-12' => 43.00,
                'T-14' => 44.00,
                'T-16' => 45.00,
                'T-S'  => 45.00,
                'T-M'  => 46.00,
                'T-L'  => 47.00,
            ],
            'BLUSA' => [
                'T-06' => 40.00,
                'T-08' => 41.00,
                'T-10' => 42.00,
                'T-12' => 43.00,
                'T-14' => 44.00,
                'T-16' => 45.00,
                'T-S'  => 45.00,
                'T-M'  => 46.00,
            ],
            'PULOVER' => [
                'T-06' => 40.00,
                'T-08' => 41.00,
                'T-10' => 42.00,
                'T-12' => 43.00,
                'T-14' => 44.00,
                'T-16' => 45.00,
                'T-S'  => 46.00,
                'T-M'  => 48.00,
                'T-L'  => 49.00,
                'T-XL' => 51.00,
            ],
            'GUARDAPOLVO' => [
                'T-2' => 50.00,
                'T-4' => 50.00,
                'T-6' => 55.00,
                'T-8' => 50.00,
            ],
            // Productos sin talla (precio único)
            'CORBATA JUNIOR' => [null => 14.00],
            'CORBATA NORMAL' => [null => 16.00],
            'SOMBREROS'      => [null => 20.00],
            'JOCKEY'         => [null => 18.00],
        ];

        $now = now();
        $insertados = 0;
        $omitidos   = 0;

        foreach ($productos as $nombreBase => $tallas) {
            foreach ($tallas as $talla => $precio) {
                // Nombre del producto: "YAMPER T-06" o "CORBATA JUNIOR"
                $nombre = $talla !== null
                    ? "{$nombreBase} {$talla}"
                    : $nombreBase;

                // SKU: "UNIF-YAMPER-T06" o "UNIF-CORBATA-JUNIOR"
                $skuBase = strtoupper(Str::slug($nombreBase, '-'));
                $sku     = 'UNIF-' . $skuBase . ($talla !== null ? '-' . str_replace('-', '', $talla) : '');

                // Evitar duplicados
                $existe = InventarioProducto::where('sku', $sku)->exists();
                if ($existe) {
                    $omitidos++;
                    continue;
                }

                InventarioProducto::create([
                    'nombre'       => $nombre,
                    'tipo'         => 'UNIFORME',
                    'categoria_id' => $categoria->id,
                    'sku'          => $sku,
                    'stock'        => 0,
                    'precio'       => $precio,
                    'descripcion'  => $talla !== null ? "Talla: {$talla}" : null,
                    'activo'       => true,
                    'destacado'    => false,
                ]);

                $insertados++;
            }
        }

        $this->command->info("Uniforme seeder completado: {$insertados} productos insertados, {$omitidos} omitidos (ya existían).");
    }
}
