<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ConceptosPagoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $conceptos = [
            [
                'nombre' => 'Matrícula',
                'tipo' => 'matricula',
                'monto_base' => 0.00, // el monto real se define en tarifas
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Pensión',
                'tipo' => 'mensual',
                'monto_base' => 0.00, // el monto real se define en tarifas
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($conceptos as $c) {
            DB::table('conceptos_pago')->updateOrInsert(
                ['nombre' => $c['nombre']], // clave única lógica
                $c
            );
        }
    }
}