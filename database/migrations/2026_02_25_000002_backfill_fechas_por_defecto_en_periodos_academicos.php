<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $periodos = DB::table('periodos_academicos as p')
            ->join('anios_escolares as a', 'a.id', '=', 'p.anio_escolar_id')
            ->where(function ($q) {
                $q->whereNull('p.fecha_inicio')
                    ->orWhereNull('p.fecha_fin');
            })
            ->select(
                'p.id',
                'p.codigo',
                'p.orden',
                'p.es_vacacional',
                'a.nombre as anio_nombre'
            )
            ->get();

        foreach ($periodos as $periodo) {
            $anio = (int) preg_replace('/\D+/', '', (string) $periodo->anio_nombre);
            if ($anio < 2000) {
                $anio = (int) date('Y');
            }

            $codigo = trim((string) $periodo->codigo);
            $orden = (int) $periodo->orden;
            $esVacacional = (bool) $periodo->es_vacacional;

            [$mesInicio, $mesFin] = [3, 12];
            if ($esVacacional || $orden === 0 || str_ends_with($codigo, '-0')) {
                [$mesInicio, $mesFin] = [1, 2];
            } elseif ($orden === 1 || str_ends_with($codigo, '-1')) {
                [$mesInicio, $mesFin] = [3, 6];
            } elseif ($orden === 2 || str_ends_with($codigo, '-2')) {
                [$mesInicio, $mesFin] = [8, 12];
            }

            $inicio = Carbon::create($anio, $mesInicio, 1)->toDateString();
            $fin = Carbon::create($anio, $mesFin, 1)->endOfMonth()->toDateString();

            DB::table('periodos_academicos')
                ->where('id', $periodo->id)
                ->update([
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Sin rollback automatico para no perder configuraciones reales.
    }
};

