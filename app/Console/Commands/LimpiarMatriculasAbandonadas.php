<?php

namespace App\Console\Commands;

use App\Models\Deuda;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\MatriculaCargo;
use App\Models\MatriculaDescuento;
use App\Models\Voucher;
use App\Models\Pago;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LimpiarMatriculasAbandonadas extends Command
{
    protected $signature = 'matriculas:limpiar-abandonadas
                            {--horas=2 : Horas de antigüedad mínima para considerar abandonada}
                            {--dry-run : Solo muestra qué se eliminaría, sin borrar nada}';

    protected $description = 'Elimina matrículas EN_PROCESO abandonadas (sin actividad en X horas) junto con sus deudas';

    public function handle(): int
    {
        $horas  = (int) $this->option('horas');
        $dryRun = $this->option('dry-run');
        $corte  = Carbon::now()->subHours($horas);

        $matriculas = Matricula::where('estado', Matricula::ESTADO_EN_PROCESO)
            ->where('updated_at', '<', $corte)
            ->get();

        if ($matriculas->isEmpty()) {
            $this->info("No hay matrículas EN_PROCESO abandonadas (más de {$horas}h sin actividad).");
            return 0;
        }

        $this->info("Matrículas EN_PROCESO abandonadas encontradas: {$matriculas->count()}");

        $eliminadas = 0;

        foreach ($matriculas as $matricula) {
            $estudiante   = $matricula->estudiante;
            $nombreAlumno = $estudiante
                ? "{$estudiante->apellidos}, {$estudiante->nombres} (DNI: {$estudiante->dni})"
                : "Estudiante ID {$matricula->estudiante_id}";

            $this->line("  → Matrícula ID {$matricula->id} | {$nombreAlumno} | Creada: {$matricula->created_at}");

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($matricula, $estudiante) {
                $deudaIds = Deuda::where('matricula_id', $matricula->id)->pluck('id');

                if ($deudaIds->isNotEmpty()) {
                    $pagoIds = DB::table('deuda_pagos')
                        ->whereIn('deuda_id', $deudaIds)
                        ->pluck('pago_id')
                        ->unique();

                    Voucher::whereIn('deuda_id', $deudaIds)->delete();

                    if ($pagoIds->isNotEmpty()) {
                        Voucher::whereIn('pago_id', $pagoIds)->delete();
                    }

                    Deuda::whereIn('id', $deudaIds)->delete();

                    if ($pagoIds->isNotEmpty()) {
                        Pago::whereIn('id', $pagoIds)
                            ->whereDoesntHave('deudas')
                            ->delete();
                    }
                }

                MatriculaCargo::where('matricula_id', $matricula->id)->delete();
                MatriculaDescuento::where('matricula_id', $matricula->id)->delete();
                $matricula->delete();

                if ($estudiante) {
                    $tieneOtrasMatriculas = Matricula::where('estudiante_id', $estudiante->id)->exists();
                    if (!$tieneOtrasMatriculas && $estudiante->estado === 'en_proceso') {
                        $estudiante->delete();
                    }
                }
            });

            $eliminadas++;
        }

        if ($dryRun) {
            $this->warn("--dry-run activo: no se eliminó nada. Quita la opción para ejecutar el borrado real.");
        } else {
            $this->info("Limpieza completada. Matrículas eliminadas: {$eliminadas}");
        }

        return 0;
    }
}
