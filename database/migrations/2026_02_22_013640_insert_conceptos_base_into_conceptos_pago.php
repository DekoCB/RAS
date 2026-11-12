<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        // OJO: asumimos que la tabla conceptos_pago ya existe
        DB::table('conceptos_pago')->updateOrInsert(
            ['nombre' => 'Matrícula'],
            [
                'tipo' => 'matricula',
                'monto_base' => 0.00,  // el monto real lo defines en tarifas
                'activo' => 1,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('conceptos_pago')->updateOrInsert(
            ['nombre' => 'Pensión'],
            [
                'tipo' => 'mensual',
                'monto_base' => 0.00,  // el monto real lo defines en tarifas
                'activo' => 1,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        $ids = DB::table('conceptos_pago')->whereIn('nombre', ['Matrícula', 'Pensión'])->pluck('id');
        DB::table('deudas')->whereIn('concepto_pago_id', $ids)->delete();
        DB::table('tarifas')->whereIn('concepto_pago_id', $ids)->delete();
        DB::table('conceptos_pago')->whereIn('id', $ids)->delete();
    }
};