<?php

namespace Database\Seeders;

use App\Models\DefensaCivilActividad;
use App\Models\Sede;
use Illuminate\Database\Seeder;

/**
 * Actividades de Defensa Civil del año (RF-71): simulacros del calendario
 * nacional, inspecciones ITSE, senalizacion y extintores en ambos locales.
 * Idempotente por titulo + fecha.
 */
class DefensaCivilDemoSeeder extends Seeder
{
    /** [sede (null=ambos|1|2), tipo, titulo, fecha, responsable, participantes, resultado, observacion, proxima_fecha] */
    private const ACTIVIDADES = [
        // ── Extintores ────────────────────────────────────────────────
        [2, 'extintores', 'Recarga anual de extintores PQS (10 unidades)', '2026-03-01', 'Extintores Arequipa E.I.R.L.', null, 'conforme',
            'Recarga y cambio de manometros. Tarjetas de control actualizadas.', '2027-03-01'],
        [1, 'extintores', 'Recarga anual de extintores PQS (4 unidades)', '2026-03-01', 'Extintores Arequipa E.I.R.L.', null, 'conforme',
            null, '2027-03-01'],
        [2, 'extintores', 'Inspeccion mensual de extintores y gabinetes', '2026-08-28', 'Brigada de seguridad - Prof. C. Lavado', null, 'observado',
            'Extintor del laboratorio con precinto roto; se reemplazo el 02/09.', '2026-09-30'],

        // ── Senalizacion ──────────────────────────────────────────────
        [2, 'senalizacion', 'Renovacion de senaletica de evacuacion y zonas seguras', '2026-02-20', 'Administracion - D. Fernandez', null, 'conforme',
            'Se instalaron 18 senales fotoluminiscentes y se repintaron los circulos de seguridad del patio.', null],
        [1, 'senalizacion', 'Senalizacion de rutas de evacuacion en aulas de Inicial', '2026-02-21', 'Administracion - D. Fernandez', null, 'conforme',
            'Senales a la altura de los ninos y croquis de evacuacion en cada aula.', null],
        [2, 'senalizacion', 'Reposicion de senal en escalera del 3er piso', '2026-04-06', 'Mantenimiento', null, 'conforme',
            'Atiende la observacion de la ITSE de marzo.', null],

        // ── Inspecciones ──────────────────────────────────────────────
        [2, 'inspeccion', 'Inspeccion Tecnica de Seguridad en Edificaciones (ITSE)', '2026-03-20', 'Municipalidad Distrital - Sub Gerencia de Defensa Civil', null, 'observado',
            'Observacion: faltaba senal de evacuacion en escalera del 3er piso. Subsanada el 06/04. Certificado emitido el 15/04/2026.', '2028-03-20'],
        [1, 'inspeccion', 'Inspeccion Tecnica de Seguridad en Edificaciones (ITSE)', '2026-03-20', 'Municipalidad Distrital - Sub Gerencia de Defensa Civil', null, 'conforme',
            'Certificado ITSE vigente por dos anos.', '2028-03-20'],
        [null, 'inspeccion', 'Revision del sistema electrico y tableros', '2026-01-19', 'Electricista R. Quispe', null, 'conforme',
            'Cambio de llaves termicas y rotulado de circuitos en ambos locales.', '2027-01-18'],
        [2, 'inspeccion', 'Inspeccion de cocina y balones de gas del comedor', '2026-04-08', 'Gas Sur', null, 'conforme',
            'Sin fugas. Valvulas y mangueras en buen estado.', '2026-10-08'],
        [null, 'inspeccion', 'Revision de botiquines y camillas de emergencia', '2026-07-14', 'Brigada de primeros auxilios - Psicologia', null, 'observado',
            'Botiquin de Inicial sin gasas ni alcohol; repuesto el 16/07.', '2026-10-14'],

        // ── Simulacros (calendario nacional y ejercicios internos) ─────
        [null, 'simulacro', 'Simulacro Nacional Multipeligro - 1er ejercicio', '2026-05-29', 'Brigada escolar - Prof. C. Lavado', 405, 'conforme',
            'Evacuacion completa en 2 min 40 s en Local 2 y 1 min 50 s en Local 1.', '2026-08-14'],
        [null, 'simulacro', 'Simulacro Nacional Multipeligro - 2do ejercicio', '2026-08-14', 'Brigada escolar - Prof. C. Lavado', 412, 'conforme',
            'Se mejoro el tiempo del 3er piso. Participaron padres de familia de Inicial.', '2026-11-06'],
        [2, 'simulacro', 'Simulacro interno de sismo - turno manana', '2026-04-17', 'Coordinacion de Primaria - Miss Gabi', 260, 'observado',
            'Alumnos de 2do B salieron por la ruta equivocada. Se reforzo con charla el 20/04.', null],
        [1, 'simulacro', 'Simulacro de sismo con ninos de 3, 4 y 5 anos', '2026-04-24', 'Coordinacion de Inicial - C. Huayllani', 96, 'conforme',
            'Ejercicio ludico con canciones de evacuacion.', null],
        [2, 'simulacro', 'Simulacro de incendio y uso de extintores', '2026-06-19', 'Bomberos Voluntarios - Cia. Arequipa', 48, 'conforme',
            'Capacitacion practica a docentes y personal de comedor.', '2027-06-18'],
        [null, 'simulacro', 'Simulacro Nacional Multipeligro - 3er ejercicio', '2026-11-06', 'Brigada escolar - Prof. C. Lavado', null, 'pendiente',
            'Programado segun calendario del INDECI.', null],
    ];

    public function run(): void
    {
        $sedes = Sede::orderBy('orden')->pluck('id')->values();
        $creadas = 0;

        foreach (self::ACTIVIDADES as [$sedeN, $tipo, $titulo, $fecha, $responsable, $participantes, $resultado, $obs, $proxima]) {
            $sedeId = $sedeN ? ($sedes[$sedeN - 1] ?? null) : null;

            $existe = DefensaCivilActividad::where('titulo', $titulo)->whereDate('fecha', $fecha)
                ->where('sede_id', $sedeId)->exists();
            if ($existe) {
                continue;
            }

            DefensaCivilActividad::create([
                'sede_id'       => $sedeId,
                'tipo'          => $tipo,
                'titulo'        => $titulo,
                'fecha'         => $fecha,
                'responsable'   => $responsable,
                'participantes' => $participantes,
                'resultado'     => $resultado,
                'observacion'   => $obs,
                'proxima_fecha' => $proxima,
            ]);
            $creadas++;
        }

        $this->command->info("Defensa Civil: {$creadas} actividades registradas.");
    }
}
