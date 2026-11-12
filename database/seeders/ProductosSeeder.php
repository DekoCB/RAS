<?php

namespace Database\Seeders;

use App\Models\InventarioCategoria;
use App\Models\InventarioProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categoría: Material Educativo ─────────────────────────────────────
        $categoria = InventarioCategoria::firstOrCreate(
            ['slug' => 'material-educativo'],
            ['nombre' => 'Material Educativo', 'activo' => true]
        );

        // ── Libros por nivel ──────────────────────────────────────────────────
        $productos = [
            [
                'nombre'      => 'Libros Inicial 2 años',
                'descripcion' => 'Pack de libros para Inicial 2 años',
                'precio'      => 40.00,
                'sku'         => 'LIB-INICIAL-2',
            ],
            [
                'nombre'      => 'Libros Inicial',
                'descripcion' => 'Pack de libros para Inicial 3, 4 y 5 años',
                'precio'      => 230.00,
                'sku'         => 'LIB-INICIAL',
            ],
            [
                'nombre'      => 'Libros Primaria',
                'descripcion' => 'Pack de libros para nivel Primaria',
                'precio'      => 280.00,
                'sku'         => 'LIB-PRIMARIA',
            ],
            [
                'nombre'      => 'Libros Secundaria',
                'descripcion' => 'Pack de libros para nivel Secundaria',
                'precio'      => 290.00,
                'sku'         => 'LIB-SECUNDARIA',
            ],
        ];

        foreach ($productos as $data) {
            InventarioProducto::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'nombre'       => $data['nombre'],
                    'descripcion'  => $data['descripcion'],
                    'tipo'         => 'producto',
                    'categoria_id' => $categoria->id,
                    'precio'       => $data['precio'],
                    'stock'        => 60,
                    'activo'       => true,
                    'destacado'    => false,
                ]
            );
        }

        $this->command->info('Productos de Libros creados/actualizados (Inicial 2 años S/.40, Inicial 3-5 S/.230, Primaria S/.280, Secundaria S/.290).');
    }
}
