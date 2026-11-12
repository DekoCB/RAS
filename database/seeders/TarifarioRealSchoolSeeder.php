<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\Tarifa;
use Illuminate\Database\Seeder;

/**
 * Tarifario de la seccion 7 del documento de requerimientos.
 *
 * Deliberadamente NO toca la matricula: el documento reporta montos
 * contradictorios (220 estandar / 220-250-260 / 240 / 270) y su propia
 * seccion 8 pide validarlos con el colegio antes de cargarlos.
 */
class TarifarioRealSchoolSeeder extends Seeder
{
    /** [clave interna, nombre visible, monto] */
    private const CONCEPTOS = [
        ['admision',         'Cuota de ingreso (por única vez)',       80], // Art. 54 del Reglamento Interno
        ['guias_agenda',     'Guias y agenda escolar',                420],
        ['comedor',          'Comedor Realino - estudiante',            5],
        ['comedor_docente',  'Comedor Realino - docente',               5],
        ['fotocheck',        'Carnet / fotocheck con QR',              20],
        ['recuperacion',     'Examen de recuperacion bimestral',       10],
        ['academia',         'Real Academia - pension alumno',        100],
        ['academia_libre',   'Real Academia - pension estudiante libre', 200],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', 1)->first() ?? AnioEscolar::orderByDesc('id')->first();

        foreach (self::CONCEPTOS as [$tipo, $nombre, $monto]) {
            $concepto = ConceptoPago::updateOrCreate(
                ['tipo' => $tipo],
                ['nombre' => $nombre, 'monto_base' => $monto, 'activo' => 1]
            );

            if (!$anio) {
                continue;
            }

            // La tabla exige nivel; 'General' es el valor que ya usa el sistema
            // para las tarifas que no dependen del nivel educativo.
            Tarifa::updateOrCreate(
                [
                    'anio_escolar_id'  => $anio->id,
                    'concepto_pago_id' => $concepto->id,
                    'nivel'            => 'General',
                    'mes'              => 0,
                ],
                ['monto' => $monto, 'activo' => 1]
            );
        }
    }
}
