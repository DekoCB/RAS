<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar campos de retiro a matriculas
        Schema::table('matriculas', function (Blueprint $table) {
            if (!Schema::hasColumn('matriculas', 'fecha_retiro')) {
                $table->date('fecha_retiro')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('matriculas', 'motivo_retiro')) {
                $table->string('motivo_retiro')->nullable()->after('fecha_retiro');
            }
        });

        // 2. Insertar concepto "Vacacional" si no existe
        $existe = DB::table('conceptos_pago')->where('tipo', 'vacacional')->exists();
        if (!$existe) {
            DB::table('conceptos_pago')->insert([
                'nombre'     => 'Vacacional',
                'tipo'       => 'vacacional',
                'monto_base' => 0,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn(['fecha_retiro', 'motivo_retiro']);
        });

        $ids = DB::table('conceptos_pago')->where('tipo', 'vacacional')->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('deudas')->whereIn('concepto_pago_id', $ids)->delete();
            DB::table('tarifas')->whereIn('concepto_pago_id', $ids)->delete();
            DB::table('conceptos_pago')->whereIn('id', $ids)->delete();
        }
    }
};
