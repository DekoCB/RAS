<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El colegio no trabaja con lonchera: se retira el concepto y todo lo que
 * cuelga de el (tarifas, deudas y sus pagos aplicados, cargos de matricula).
 * La migracion que lo creaba (2026_02_23_190202) fue eliminada del repo.
 */
return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('conceptos_pago')
            ->where('tipo', 'lonchera')
            ->orWhere('nombre', 'like', '%onchera%')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        $deudaIds = DB::table('deudas')->whereIn('concepto_pago_id', $ids)->pluck('id');

        DB::table('vouchers')->whereIn('deuda_id', $deudaIds)->update(['deuda_id' => null]);
        DB::table('deuda_pagos')->whereIn('deuda_id', $deudaIds)->delete();
        DB::table('deudas')->whereIn('id', $deudaIds)->delete();
        DB::table('tarifas')->whereIn('concepto_pago_id', $ids)->delete();
        DB::table('matricula_cargos')->where('concepto', 'Lonchera')->delete();
        DB::table('conceptos_pago')->whereIn('id', $ids)->delete();
    }

    public function down(): void
    {
        // No se restaura: el concepto ya no forma parte del sistema.
    }
};
