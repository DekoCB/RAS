<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $anios = DB::table('anios_escolares')
            ->select('id', 'nombre')
            ->get();

        foreach ($anios as $anioRow) {
            $anio = (int) preg_replace('/\D+/', '', (string) $anioRow->nombre);
            if ($anio < 2000) {
                $anio = (int) date('Y');
            }

            $periodos = DB::table('periodos_academicos')
                ->where('anio_escolar_id', $anioRow->id)
                ->select('id', 'codigo', 'orden', 'es_vacacional', 'fecha_inicio', 'fecha_fin')
                ->get();

            $p0 = $periodos->first(fn ($p) => str_ends_with((string) $p->codigo, '-0') || (int) $p->orden === 0);
            $p1 = $periodos->first(fn ($p) => str_ends_with((string) $p->codigo, '-1') || (int) $p->orden === 1);
            $p2 = $periodos->first(fn ($p) => str_ends_with((string) $p->codigo, '-2') || (int) $p->orden === 2);

            $requiereAjuste = false;

            foreach ([$p0, $p1, $p2] as $periodo) {
                if (!$periodo) {
                    continue;
                }
                if (empty($periodo->fecha_inicio) || empty($periodo->fecha_fin)) {
                    $requiereAjuste = true;
                    break;
                }
            }

            if (!$requiereAjuste && $p1 && $p2) {
                $p1Inicio = Carbon::parse($p1->fecha_inicio)->startOfDay();
                $p1Fin = Carbon::parse($p1->fecha_fin)->startOfDay();
                $p2Inicio = Carbon::parse($p2->fecha_inicio)->startOfDay();
                $p2Fin = Carbon::parse($p2->fecha_fin)->startOfDay();

                if ($this->rangosSeTraslapan($p1Inicio, $p1Fin, $p2Inicio, $p2Fin)) {
                    $requiereAjuste = true;
                }
            }

            if (!$requiereAjuste) {
                continue;
            }

            $actualizar = [];
            if ($p0) {
                $actualizar[] = [
                    'id' => $p0->id,
                    'fecha_inicio' => "{$anio}-01-01",
                    'fecha_fin' => Carbon::create($anio, 2, 1)->endOfMonth()->toDateString(),
                ];
            }
            if ($p1) {
                $actualizar[] = [
                    'id' => $p1->id,
                    'fecha_inicio' => "{$anio}-03-01",
                    'fecha_fin' => "{$anio}-06-30",
                ];
            }
            if ($p2) {
                $actualizar[] = [
                    'id' => $p2->id,
                    'fecha_inicio' => "{$anio}-08-01",
                    'fecha_fin' => "{$anio}-12-31",
                ];
            }

            foreach ($actualizar as $item) {
                DB::table('periodos_academicos')
                    ->where('id', $item['id'])
                    ->update([
                        'fecha_inicio' => $item['fecha_inicio'],
                        'fecha_fin' => $item['fecha_fin'],
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Sin rollback automatico.
    }

    private function rangosSeTraslapan(Carbon $inicioA, Carbon $finA, Carbon $inicioB, Carbon $finB): bool
    {
        return $inicioA->lte($finB) && $inicioB->lte($finA);
    }
};

