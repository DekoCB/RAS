<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\Tarifa;
use Illuminate\Database\Seeder;

/**
 * Siembra las tarifas base por nivel para el año escolar activo.
 *
 * Montos de matrícula y pensión: Reglamento Interno, periodo lectivo 2026
 * (cuadros "Pago de matrícula" y "Pago de la pensión").
 *
 * Lógica de matrícula en el sistema:
 *  - Primer hijo  → usa $tarifaMatricula->monto como cargo único completo
 *  - Hermanos     → pagan S/100 (gastos internos, valor fijo en el código)
 */
class TarifasSeeder extends Seeder
{
    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first();
        if (!$anio) {
            $this->command->warn('No hay año escolar activo. Ejecuta CatalogoSeeder primero.');
            return;
        }

        $conceptoMatricula       = ConceptoPago::where('tipo', 'matricula')->first();
        $conceptoMatriculaHermano = ConceptoPago::where('tipo', 'matricula_hermano')->first();
        $conceptoPension         = ConceptoPago::where('tipo', 'pension')
            ->orWhere('tipo', 'mensual')
            ->first();

        if (!$conceptoMatricula || !$conceptoPension) {
            $this->command->warn('Faltan conceptos de pago base. Ejecuta ConceptosPagoSeeder primero.');
            return;
        }

        // ─── Tarifas por nivel (Reglamento Interno 2026) ────────────────
        // matricula: cargo único para el PRIMER hijo de la familia.
        //            Los hermanos pagan S/100 fijo (lógica en MatriculaController).
        // pension:   cuota mensual, 10 cuotas de marzo a diciembre (Art. 61),
        //            el mismo monto todos los meses (mes=0).
        // ────────────────────────────────────────────────────────────────
        $niveles = [
            'Inicial'    => ['matricula' => 210.00, 'pension' => 210.00],
            'Primaria'   => ['matricula' => 220.00, 'pension' => 220.00],
            'Secundaria' => ['matricula' => 230.00, 'pension' => 230.00],
        ];

        foreach ($niveles as $nivel => $montos) {
            // Matrícula primer hijo
            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anio->id,
                    'nivel'            => $nivel,
                    'concepto_pago_id' => $conceptoMatricula->id,
                    'mes'              => 0,
                ],
                ['monto' => $montos['matricula'], 'activo' => true]
            );

            // Matrícula hermano (S/100 para todos los niveles)
            if ($conceptoMatriculaHermano) {
                Tarifa::updateOrCreate(
                    [
                        'anio_escolar_id'  => $anio->id,
                        'nivel'            => $nivel,
                        'concepto_pago_id' => $conceptoMatriculaHermano->id,
                        'mes'              => 0,
                    ],
                    ['monto' => 100.00, 'activo' => true]
                );
            }

            // Pensión base — todos los meses normales (mes=0)
            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anio->id,
                    'nivel'            => $nivel,
                    'concepto_pago_id' => $conceptoPension->id,
                    'mes'              => 0,
                ],
                ['monto' => $montos['pension'], 'activo' => true]
            );
        }

        // ─── Cobros de retiro (nivel General, aplican a todos los niveles) ───
        $conceptoTraslado = ConceptoPago::where('tipo', 'retiro_traslado')->first();
        $conceptoFut      = ConceptoPago::where('tipo', 'retiro_fut')->first();

        if ($conceptoTraslado) {
            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anio->id,
                    'nivel'            => 'General',
                    'concepto_pago_id' => $conceptoTraslado->id,
                    'mes'              => 0,
                ],
                ['monto' => 120.00, 'activo' => true]
            );
        }

        if ($conceptoFut) {
            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anio->id,
                    'nivel'            => 'General',
                    'concepto_pago_id' => $conceptoFut->id,
                    'mes'              => 0,
                ],
                ['monto' => 1.00, 'activo' => true]
            );
        }

        $this->command->info('Tarifas sembradas correctamente para el año ' . $anio->nombre . '.');
    }
}
