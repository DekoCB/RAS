<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InicialCincoAniosSeeder extends Seeder
{
    private const SECCION = 'A';

    private const ESTUDIANTES = [
        [
            'dni' => '91980595',
            'nombres' => 'GUSTAVO BENJAMIN',
            'apellidos' => 'ARANDA ABATUA',
            'padre' => [
                'dni' => '73389390',
                'nombres' => 'FREDDY JAVIER',
                'apellidos' => 'ARANDA CARDENAS',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '70254938',
                'nombres' => 'CRHISTINA ASTRID',
                'apellidos' => 'ABAUTA SAMAR',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92013196',
            'nombres' => 'MARIAFE RAFAELA',
            'apellidos' => 'BARDALES BOTTGER',
            'padre' => [
                'dni' => '45256467',
                'nombres' => 'JORGE ALBERTO',
                'apellidos' => 'BARDALES AGUERO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '43696569',
                'nombres' => 'DANNY GUISSELL',
                'apellidos' => 'BOTTGER CUADROS',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92174249',
            'nombres' => 'NATHAN KEITH LIU',
            'apellidos' => 'BLAS VENEGAS',
            'padre' => [
                'dni' => '45732586',
                'nombres' => 'ALEX',
                'apellidos' => 'BLAS ESPINOZA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '42478589',
                'nombres' => 'KELLY JUDITH',
                'apellidos' => 'VENEGAS MAURTUA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92113096',
            'nombres' => 'DANNY ADRIEL DOMNIC',
            'apellidos' => 'CASIMIRO ORE',
            'padre' => null,
            'madre' => [
                'dni' => '04341050',
                'nombres' => 'SANDRA LINN',
                'apellidos' => 'ABUELA-CASIMIRO LAGRAVERE',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '91862600',
            'nombres' => 'KYLIE KHLOE',
            'apellidos' => 'CUADROS CIRINEO',
            'padre' => [
                'dni' => '42603382',
                'nombres' => 'LEWIN DOYER',
                'apellidos' => 'CUADROS NANO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '71435182',
                'nombres' => 'ROSSY MARA',
                'apellidos' => 'CIRINEO CIRIACO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '91913883',
            'nombres' => 'ALESI AMELIE',
            'apellidos' => 'DE LA CUBA CHAMORRO',
            'padre' => [
                'dni' => '41737213',
                'nombres' => 'VICTOR MANUEL',
                'apellidos' => 'DE LA CUBA QUISPE',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44418664',
                'nombres' => 'YHOSELINA CEDINA',
                'apellidos' => 'CHAMORRO SOTOMAYOR',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '91990682',
            'nombres' => 'MATHEO NILTHON',
            'apellidos' => 'LLANOS CHAVEZ',
            'padre' => [
                'dni' => '44658321',
                'nombres' => 'NITON JERSSON',
                'apellidos' => 'LLANOS LOPEZ',
                'sexo' => 'M',
            ],
            'madre' => null,
        ],
        [
            'dni' => '91924471',
            'nombres' => 'ADRIAN ANTONIO',
            'apellidos' => 'LUNA ASTO',
            'padre' => null,
            'madre' => [
                'dni' => '62181174',
                'nombres' => 'DAYANA MARICIELO',
                'apellidos' => 'ASTO SOLANO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92163481',
            'nombres' => 'HANNA REBECCA',
            'apellidos' => 'PAREDES DIEGO',
            'padre' => [
                'dni' => '70255134',
                'nombres' => 'ELVIS ALEJANDRO',
                'apellidos' => 'PAREDES ROMERO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '47179088',
                'nombres' => 'RODE',
                'apellidos' => 'DIEGO DE LA CRUZ',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '91887591',
            'nombres' => 'ZOE',
            'apellidos' => 'QUISPE LOZANO',
            'padre' => [
                'dni' => '42273484',
                'nombres' => 'JORGE',
                'apellidos' => 'QUISPE NOBLEJAS',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '42410601',
                'nombres' => 'YOSSY EMILA',
                'apellidos' => 'LOZANO PALACIN DE QUISPE',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92163266',
            'nombres' => 'GIA ARIANNA',
            'apellidos' => 'MOROCHO ZELADA',
            'padre' => null,
            'madre' => [
                'dni' => '43627186',
                'nombres' => 'NELY ROXANA',
                'apellidos' => 'MOROCHO ZELADA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92250971',
            'nombres' => 'IKER BECKHAM',
            'apellidos' => 'VILLANUEVA QUINTANA',
            'padre' => [
                'dni' => '70801387',
                'nombres' => 'ALEX ALIN',
                'apellidos' => 'VILLANUEVA CAJAHUAMAN',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '70489583',
                'nombres' => 'SOFIA NILVIA',
                'apellidos' => 'QUINTANA AZAN',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '91804559',
            'nombres' => 'EVANS EDRIAN',
            'apellidos' => 'LA TORRE ORIHUELA',
            'padre' => [
                'dni' => '77697457',
                'nombres' => 'FRANKKLIN RICHARD',
                'apellidos' => 'LOPEZ JANAMPA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '76473207',
                'nombres' => 'SANDRA BEATRIZ',
                'apellidos' => 'ORIHUELA CHUCO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92045971',
            'nombres' => 'LUCIANA ANGELINA',
            'apellidos' => 'SANCHEZ PALOMINO',
            'padre' => [
                'dni' => '002012188',
                'nombres' => 'HUGO ARGENIS',
                'apellidos' => 'SANCHEZ MORALES',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '70782128',
                'nombres' => 'VILMA YANINA',
                'apellidos' => 'PALOMINO LA TORRE',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92127617',
            'nombres' => 'NICOLE SOFIA',
            'apellidos' => 'LLALLAHUI HIDALGO',
            'observacion' => 'H-',
            'padre' => null,
            'madre' => [
                'dni' => '48187931',
                'nombres' => 'SUSAN',
                'apellidos' => 'HIDALGO ORTIZ',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '91813929',
            'nombres' => 'LUCA GABRIEL',
            'apellidos' => 'ADARMES DESTEFANO',
            'padre' => [
                'dni' => '47397662',
                'nombres' => 'YERALD CESAR',
                'apellidos' => 'ADARMES ORNA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '71202342',
                'nombres' => 'KAREN JANETH',
                'apellidos' => 'DESTEFANO CARRERA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92040308',
            'nombres' => 'JOCABED SARAI',
            'apellidos' => 'CRISPIN LEON',
            'padre' => [
                'dni' => '23279626',
                'nombres' => 'HUMBERTO',
                'apellidos' => 'CRISPIN ATAYPOMA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44138314',
                'nombres' => 'PATRICIA',
                'apellidos' => 'LEON SALAZAR',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '91871903',
            'nombres' => 'DANNAE PAOLA',
            'apellidos' => 'LALE COLINA',
            'padre' => [
                'dni' => null,
                'nombres' => 'DANTE DANIEL',
                'apellidos' => 'LALE ALVAREZ',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '48211523',
                'nombres' => 'SHIRLY',
                'apellidos' => 'COLINA FRANCISCO',
                'sexo' => 'F',
            ],
        ],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar Inicial 5 anos.');
            return;
        }

        $this->asegurarPeriodosAcademicos($anio);

        $periodo = PeriodoAcademico::actualParaAnio($anio->id)
            ?? PeriodoAcademico::where('anio_escolar_id', $anio->id)->orderBy('orden')->first();

        if (! $periodo) {
            $this->command?->warn('No existe un periodo academico para el anio escolar activo.');
            return;
        }

        $grado = Grado::where('nivel', 'Inicial')
            ->where('orden', 4)
            ->first();

        if (! $grado) {
            $this->command?->warn('No se encontro el grado de Inicial 5 anos. Ejecuta primero CatalogoSeeder.');
            return;
        }

        $seccion = Seccion::firstOrCreate(
            [
                'anio_escolar_id' => $anio->id,
                'grado_id' => $grado->id,
                'nombre' => self::SECCION,
            ],
            [
                'turno' => 'Manana',
            ]
        );

        foreach (self::ESTUDIANTES as $estudianteData) {
            DB::transaction(function () use ($estudianteData, $anio, $periodo, $grado, $seccion) {
                $this->registrarEstudiante($estudianteData, $anio->id, $periodo->id, $grado->id, $seccion->id);
            });
        }

        $this->command?->info('Inicial 5 anos registrado con estudiantes y apoderados.');
    }

    private function asegurarPeriodosAcademicos(AnioEscolar $anio): void
    {
        if (PeriodoAcademico::where('anio_escolar_id', $anio->id)->exists()) {
            return;
        }

        $anioCalendario = (int) preg_replace('/\D+/', '', (string) $anio->nombre);
        if ($anioCalendario < 2000) {
            $anioCalendario = (int) date('Y');
        }

        $ahora = now();
        $codigoBase = trim((string) $anio->nombre) !== '' ? trim((string) $anio->nombre) : (string) $anioCalendario;

        PeriodoAcademico::insert([
            [
                'anio_escolar_id' => $anio->id,
                'codigo' => $codigoBase . '-0',
                'nombre' => 'Vacacional',
                'fecha_inicio' => "{$anioCalendario}-01-01",
                'fecha_fin' => "{$anioCalendario}-02-28",
                'es_vacacional' => true,
                'orden' => 0,
                'activo' => false,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'codigo' => $codigoBase . '-1',
                'nombre' => 'Periodo 1',
                'fecha_inicio' => "{$anioCalendario}-03-01",
                'fecha_fin' => "{$anioCalendario}-07-31",
                'es_vacacional' => false,
                'orden' => 1,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'codigo' => $codigoBase . '-2',
                'nombre' => 'Periodo 2',
                'fecha_inicio' => "{$anioCalendario}-08-01",
                'fecha_fin' => "{$anioCalendario}-12-31",
                'es_vacacional' => false,
                'orden' => 2,
                'activo' => false,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);
    }

    private function registrarEstudiante(
        array $estudianteData,
        int $anioEscolarId,
        int $periodoAcademicoId,
        int $gradoId,
        int $seccionId
    ): void {
        $dni = $this->normalizarDni($estudianteData['dni']);
        if (! $dni) {
            return;
        }

        $nombreCompleto = trim($estudianteData['apellidos'] . ' ' . $estudianteData['nombres']);

        $user = User::firstOrCreate(
            ['dni' => $dni],
            [
                'name' => $nombreCompleto,
                'email' => $this->emailEstudiante($dni),
                'password' => Hash::make($dni),
            ]
        );

        $userUpdates = [];
        if ($user->name !== $nombreCompleto) {
            $userUpdates['name'] = $nombreCompleto;
        }
        if (trim((string) $user->email) === '') {
            $userUpdates['email'] = $this->emailEstudiante($dni);
        }
        if (! empty($userUpdates)) {
            $user->update($userUpdates);
        }

        if (! $user->hasRole('Estudiante')) {
            $user->assignRole('Estudiante');
        }

        $estudiante = Estudiante::updateOrCreate(
            ['dni' => $dni],
            [
                'user_id' => $user->id,
                'codigo' => $dni,
                'nombres' => $estudianteData['nombres'],
                'apellidos' => $estudianteData['apellidos'],
                'estado' => 'activo',
            ]
        );

        if ((int) ($estudiante->user_id ?? 0) !== $user->id) {
            $estudiante->user_id = $user->id;
            $estudiante->save();
        }

        $padre = $this->registrarApoderado($estudianteData['padre'] ?? null);
        $madre = $this->registrarApoderado($estudianteData['madre'] ?? null);

        $this->sincronizarApoderados($estudiante, $padre, $madre);

        Matricula::updateOrCreate(
            [
                'anio_escolar_id' => $anioEscolarId,
                'periodo_academico_id' => $periodoAcademicoId,
                'estudiante_id' => $estudiante->id,
            ],
            [
                'grado_id' => $gradoId,
                'seccion_id' => $seccionId,
                'fecha_matricula' => now()->toDateString(),
                'estado' => Matricula::ESTADO_ACTIVA,
                'repitente' => false,
                'observacion' => $estudianteData['observacion'] ?? null,
            ]
        );
    }

    private function registrarApoderado(?array $apoderadoData): ?Apoderado
    {
        if (! $apoderadoData) {
            return null;
        }

        $dni = $this->normalizarDni($apoderadoData['dni'] ?? null);
        $nombres = trim((string) ($apoderadoData['nombres'] ?? ''));
        $apellidos = trim((string) ($apoderadoData['apellidos'] ?? ''));

        if ($nombres === '' || $apellidos === '') {
            return null;
        }

        $identificador = $dni
            ? ['dni' => $dni]
            : ['dni' => null, 'nombres' => $nombres, 'apellidos' => $apellidos];

        return Apoderado::updateOrCreate(
            $identificador,
            [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'dni' => $dni,
                'sexo' => $apoderadoData['sexo'] ?? null,
            ]
        );
    }

    private function sincronizarApoderados(Estudiante $estudiante, ?Apoderado $padre, ?Apoderado $madre): void
    {
        $syncData = [];
        $principalId = null;

        if ($padre) {
            $principalId = $padre->id;
            $syncData[$padre->id] = [
                'relacion' => 'Padre',
                'principal' => true,
            ];
        }

        if ($madre) {
            if (! $principalId) {
                $principalId = $madre->id;
            }

            $syncData[$madre->id] = [
                'relacion' => 'Madre',
                'principal' => $madre->id === $principalId,
            ];
        }

        if (empty($syncData)) {
            return;
        }

        $estudiante->apoderados()->sync($syncData, false);

        DB::table('apoderado_estudiante')
            ->where('estudiante_id', $estudiante->id)
            ->update(['principal' => false]);

        if ($principalId) {
            DB::table('apoderado_estudiante')
                ->where('estudiante_id', $estudiante->id)
                ->where('apoderado_id', $principalId)
                ->update(['principal' => true]);
        }
    }

    private function emailEstudiante(string $dni): string
    {
        return $dni . '@reyschool.edu.pe';
    }

    private function normalizarDni(?string $dni): ?string
    {
        $normalizado = preg_replace('/\D+/', '', trim((string) $dni)) ?? '';

        return $normalizado !== '' ? $normalizado : null;
    }
}
