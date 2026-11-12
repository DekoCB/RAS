<?php

namespace App\Console\Commands;

use App\Models\AnioEscolar;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Seccion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga los cursos del colegio y asigna su docente en cada seccion.
 *
 * Origen: el cuadro "PROFESORES REAL SCHOOL" que entrego el colegio, donde
 * cada profesor tiene su area y los cursos que dicta.
 *
 * El cuadro no dice en que secciones dicta cada uno, asi que se aplica el
 * criterio acordado: el profesor de secundaria cubre las secciones de su
 * nivel, y cada tutora de primaria o inicial queda en su propio grado.
 * Cuando varios profesores comparten un curso (Algebra la dictan tres), las
 * secciones se reparten entre ellos, porque una seccion solo admite un
 * docente por curso.
 */
class CargarAsignacionDocente extends Command
{
    protected $signature = 'docentes:cargar-asignaciones
                            {--anio= : Nombre del ano escolar (por defecto, el activo)}
                            {--dry-run : Muestra lo que haria, sin escribir nada}';

    protected $description = 'Crea los cursos del colegio y asigna el docente de cada seccion';

    /** Cursos de secundaria y quien los dicta, por DNI, en el orden del cuadro. */
    private const SECUNDARIA = [
        'Aritmetica'               => ['44879130'],
        'Algebra'                  => ['44879130', '42635737', '43283489'],
        'Geometria'                => ['76809070', '43283489'],
        'Trigonometria'            => ['76809070', '43283489'],
        'Razonamiento Matematico'  => ['42635737'],
        'Fisica'                   => ['43283489', '41524861'],
        'Biologia'                 => ['42697536'],
        'Anatomia'                 => ['42697536'],
        'Quimica'                  => ['42697536', '70845342'],
        'Literatura'               => ['70687876'],
        'Lenguaje'                 => ['71843352', '75612620'],
        'Razonamiento Verbal'      => ['71843352'],
        'Persona y Familia'        => ['47329526'],
        'Comprension Lectora'      => ['47329526'],
        'Historia'                 => ['72278214'],
        'Geografia'                => ['72278214'],
        'Civica'                   => ['72278214'],
        'Ingles'                   => ['72895589'],
    ];

    /** Tutoras de aula: un solo docente para todo el grado (unidocencia). */
    private const TUTORES = [
        '1ro de Primaria' => '42979678',
        '2do de Primaria' => '00256801',
        '3ro de Primaria' => '75844412',
        '4to de Primaria' => '71636799',
        '5to de Primaria' => '71483324',
        'Inicial 4 años'  => '42739801',
    ];

    /** Educacion Fisica la dicta una sola persona en todos los grados. */
    private const EDUCACION_FISICA = '74212284';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $anio = $this->option('anio')
            ? AnioEscolar::where('nombre', $this->option('anio'))->first()
            : AnioEscolar::where('activo', true)->first();

        if (!$anio) {
            $this->error('No se encontro el ano escolar.');

            return self::FAILURE;
        }

        // La asignacion vale para los periodos lectivos, no para el vacacional.
        $periodos = $anio->periodosAcademicos()->where('es_vacacional', false)->orderBy('orden')->get();

        if ($periodos->isEmpty()) {
            $this->error("El ano {$anio->nombre} no tiene periodos lectivos.");

            return self::FAILURE;
        }

        $this->info("Ano escolar: {$anio->nombre}");
        $this->info('Periodos: ' . $periodos->pluck('nombre')->implode(', '));

        $secciones = Seccion::with('grado')->where('anio_escolar_id', $anio->id)->get();

        if ($secciones->isEmpty()) {
            $this->error('No hay secciones en este ano escolar.');

            return self::FAILURE;
        }

        // DNI -> id del usuario, que es a quien apunta la asignacion.
        $usuarios = Docente::whereNotNull('user_id')->pluck('user_id', 'dni');

        $plan = $this->armarPlan($secciones, $usuarios);

        $this->newLine();
        $this->line('Cursos a crear: ' . count($plan['cursos']));
        $this->line('Asignaciones por periodo: ' . count($plan['asignaciones']));
        $this->line('Total de filas: ' . count($plan['asignaciones']) * $periodos->count());

        if ($plan['sinDocente']) {
            $this->newLine();
            $this->warn('Cursos que quedan sin docente (' . count($plan['sinDocente']) . '):');
            foreach ($plan['sinDocente'] as $detalle) {
                $this->line('  - ' . $detalle);
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('[simulacion] No se escribio nada.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan, $periodos, $anio) {
            $cursos = [];

            foreach ($plan['cursos'] as $nombre) {
                $cursos[$nombre] = Curso::firstOrCreate(['nombre' => $nombre])->id;
            }

            foreach ($periodos as $periodo) {
                foreach ($plan['asignaciones'] as $a) {
                    DB::table('seccion_curso')->updateOrInsert(
                        [
                            'anio_escolar_id'      => $anio->id,
                            'periodo_academico_id' => $periodo->id,
                            'seccion_id'           => $a['seccion_id'],
                            'curso_id'             => $cursos[$a['curso']],
                        ],
                        [
                            'docente_id' => $a['user_id'],
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        });

        $this->newLine();
        $this->info('Cursos en el sistema: ' . Curso::count());
        $this->info('Asignaciones guardadas: ' . DB::table('seccion_curso')->count());
        $this->info('Con docente: ' . DB::table('seccion_curso')->whereNotNull('docente_id')->count());

        return self::SUCCESS;
    }

    /** @return array{cursos:array,asignaciones:array,sinDocente:array} */
    private function armarPlan($secciones, $usuarios): array
    {
        $cursos = [];
        $asignaciones = [];
        $sinDocente = [];

        $deSecundaria = $secciones->filter(fn ($s) => $s->grado?->nivel === 'Secundaria')->values();

        // ── Secundaria: cada curso reparte sus secciones entre quienes lo dictan
        foreach (self::SECUNDARIA as $curso => $dnis) {
            $cursos[] = $curso;

            $profes = array_values(array_filter(array_map(
                fn ($dni) => $usuarios[$dni] ?? null,
                $dnis
            )));

            if (!$profes) {
                $sinDocente[] = "{$curso} (secundaria): ningun profesor del cuadro tiene cuenta";
                continue;
            }

            foreach ($deSecundaria as $i => $seccion) {
                $asignaciones[] = [
                    'seccion_id' => $seccion->id,
                    'curso'      => $curso,
                    // Reparto en rueda: con tres profesores, cada uno toma dos
                    // de las seis secciones.
                    'user_id'    => $profes[$i % count($profes)],
                ];
            }
        }

        // ── Primaria e inicial: unidocencia, una tutora por grado
        $cursos[] = 'Unidocencia';

        foreach ($secciones as $seccion) {
            $nivel = $seccion->grado?->nivel;

            if (!in_array($nivel, ['Primaria', 'Inicial'], true)) {
                continue;
            }

            $grado = $seccion->grado->nombre;
            $dni = self::TUTORES[$grado] ?? null;
            $userId = $dni ? ($usuarios[$dni] ?? null) : null;

            if (!$userId) {
                $sinDocente[] = "Unidocencia en {$grado} {$seccion->nombre}: el cuadro no indica tutor";
            }

            $asignaciones[] = [
                'seccion_id' => $seccion->id,
                'curso'      => 'Unidocencia',
                'user_id'    => $userId,
            ];
        }

        // ── Educacion Fisica: todas las secciones, un solo profesor
        $cursos[] = 'Educacion Fisica';
        $efe = $usuarios[self::EDUCACION_FISICA] ?? null;

        if (!$efe) {
            $sinDocente[] = 'Educacion Fisica: el profesor del cuadro no tiene cuenta';
        }

        foreach ($secciones as $seccion) {
            $asignaciones[] = [
                'seccion_id' => $seccion->id,
                'curso'      => 'Educacion Fisica',
                'user_id'    => $efe,
            ];
        }

        return [
            'cursos'       => array_values(array_unique($cursos)),
            'asignaciones' => $asignaciones,
            'sinDocente'   => $sinDocente,
        ];
    }
}
