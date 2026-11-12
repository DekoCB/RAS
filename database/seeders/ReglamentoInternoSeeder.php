<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\CronogramaPension;
use App\Models\PoliticaPago;
use App\Models\Tarifa;
use Illuminate\Database\Seeder;

/**
 * Reglamento Interno, periodo lectivo 2026:
 *
 *  - Art. 54  cuota de ingreso (S/ 80, por unica vez, enero a marzo)
 *  - Art. 61  cronograma de vencimiento de las 10 cuotas de pension
 *  - Art. 64  derechos de tramite documentario
 *  - Art. 69  formula del interes moratorio
 *  - Anexos   oportunidad de pago de la matricula (05/01 al 18/02)
 *
 * Los montos de matricula y pension por nivel viven en TarifasSeeder y los
 * horarios (Art. 153) en InstitucionSeeder.
 */
class ReglamentoInternoSeeder extends Seeder
{
    /** Art. 64: [tipo, nombre, costo] */
    private const TRAMITES = [
        ['tramite_certificado_estudio',   'Derecho de trámite de certificado de estudio',         50.00],
        ['tramite_constancia_estudio',    'Derecho de trámite de constancia de estudio',          20.00],
        ['tramite_resolucion_traslado',   'Derecho de trámite de Resolución de traslado',         20.00],
        ['tramite_constancia_conducta',   'Derecho de trámite de constancia de conducta',         20.00],
        ['tramite_constancia_no_adeudo',  'Derecho de trámite de constancia de no adeudo',        20.00],
        ['tramite_tercio_superior',       'Derecho de trámite de constancia de tercio superior',  15.00],
        ['tramite_cambio_apellidos',      'Derecho de trámite Cambio de apellidos',               50.00],
        ['tramite_exoneracion',           'Derecho de trámite de exoneración',                    10.00],
        ['tramite_duplicado',             'Duplicado de documentos emitidos',                      5.00],
    ];

    /** Art. 61: mes => fecha de pago (periodo lectivo 2026). */
    private const CRONOGRAMA_2026 = [
        3  => '2026-03-31',
        4  => '2026-04-30',
        5  => '2026-05-29',
        6  => '2026-06-30',
        7  => '2026-07-31',
        8  => '2026-08-31',
        9  => '2026-09-30',
        10 => '2026-10-30',
        11 => '2026-11-30',
        12 => '2026-12-15',
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first() ?? AnioEscolar::orderByDesc('id')->first();

        if (!$anio) {
            $this->command->warn('No hay año escolar. Ejecuta CatalogoSeeder primero.');
            return;
        }

        $this->tramites($anio);
        $this->cuotaIngreso($anio);
        $this->politicaYCronograma($anio);

        $this->command->info("Reglamento Interno cargado para el año {$anio->nombre}.");
    }

    /** Art. 64: cada tramite es un concepto con tarifa General (no depende del nivel). */
    private function tramites(AnioEscolar $anio): void
    {
        foreach (self::TRAMITES as [$tipo, $nombre, $costo]) {
            $concepto = ConceptoPago::updateOrCreate(
                ['tipo' => $tipo],
                ['nombre' => $nombre, 'monto_base' => $costo, 'activo' => true]
            );

            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anio->id,
                    'concepto_pago_id' => $concepto->id,
                    'nivel'            => 'General',
                    'mes'              => 0,
                ],
                ['monto' => $costo, 'activo' => true]
            );
        }
    }

    /**
     * Art. 54: S/ 80 en los tres niveles. Reutiliza el tipo 'admision' porque
     * el modulo de admision ya cobra por ese concepto.
     */
    private function cuotaIngreso(AnioEscolar $anio): void
    {
        $concepto = ConceptoPago::updateOrCreate(
            ['tipo' => 'admision'],
            ['nombre' => 'Cuota de ingreso (por única vez)', 'monto_base' => 80.00, 'activo' => true]
        );

        foreach (['Inicial', 'Primaria', 'Secundaria'] as $nivel) {
            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anio->id,
                    'concepto_pago_id' => $concepto->id,
                    'nivel'            => $nivel,
                    'mes'              => 0,
                ],
                ['monto' => 80.00, 'activo' => true]
            );
        }
    }

    private function politicaYCronograma(AnioEscolar $anio): void
    {
        $y = (int) $anio->nombre ?: (int) date('Y');

        PoliticaPago::updateOrCreate(
            ['anio_escolar_id' => $anio->id],
            [
                'pension_cuotas'      => 10,
                'pension_mes_inicio'  => 3,
                'pension_mes_fin'     => 12,
                'matricula_desde'     => "{$y}-01-05",
                'matricula_hasta'     => "{$y}-02-18",
                'cuota_ingreso_desde' => "{$y}-01-01",
                'cuota_ingreso_hasta' => "{$y}-03-31",
                'mora_porcentaje'     => 15.00,
                'mora_divisor'        => 360,
                'observaciones'       => 'Art. 69: interés moratorio diario equivalente al 15% del producto de la pensión '
                    . 'por la tasa máxima de interés convencional compensatorio del BCR, dividido entre 360. '
                    . 'La tasa del BCR debe registrarse a mano cuando se publique.',
            ]
        );

        // El cronograma del reglamento es del 2026; para otros anios se usa la
        // misma regla (ultimo dia habil del mes, diciembre el 15).
        foreach (range(3, 12) as $mes) {
            $fecha = $y === 2026
                ? self::CRONOGRAMA_2026[$mes]
                : CronogramaPension::fechaPorDefecto($y, $mes)->toDateString();

            CronogramaPension::updateOrCreate(
                ['anio_escolar_id' => $anio->id, 'mes' => $mes],
                ['fecha_vencimiento' => $fecha]
            );
        }
    }
}
