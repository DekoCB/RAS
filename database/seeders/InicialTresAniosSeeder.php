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

class InicialTresAniosSeeder extends Seeder
{
    private const SECCION = 'A';

    private const ESTUDIANTES = [
        [
            'dni' => '93254748',
            'nombres' => 'ROMINA NARET',
            'apellidos' => 'QUISPE TRINIDAD',
            'padre' => [
                'dni' => '42448254',
                'nombres' => 'BENITO',
                'apellidos' => 'QUISPE VELASQUE',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '40258358',
                'nombres' => 'ELVA KARIN',
                'apellidos' => 'TRINIDAD PONCE',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93040768',
            'nombres' => 'PEDRO MATHIAS',
            'apellidos' => 'VALLE GAMES',
            'padre' => [
                'dni' => '46406449',
                'nombres' => 'PEDRO WILDER',
                'apellidos' => 'VALLE RIVERA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '70221909',
                'nombres' => 'MARITZA MARIBEL',
                'apellidos' => 'GAMEZ ANTAY',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93163314',
            'nombres' => 'VANNIA ANTONELLA',
            'apellidos' => 'RAMOS APAZA',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '04338353',
                'nombres' => 'WALTER RUDY',
                'apellidos' => 'RAMOS TRUJILLO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '41674150',
                'nombres' => 'VANESSA GRIPS',
                'apellidos' => 'APAZA MEDRANO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93011521',
            'nombres' => 'RUTH ALEXANDRA',
            'apellidos' => 'SOSA HURTADO',
            'observacion' => 'H',
            'padre' => [
                'dni' => '42187175',
                'nombres' => 'EDER FELIX',
                'apellidos' => 'SOSA TOLENTINO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '42935435',
                'nombres' => 'RUTH',
                'apellidos' => 'HURTADO LOAYZA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93142910',
            'nombres' => 'NOA MATEO',
            'apellidos' => 'TRINIDAD HUAMAN',
            'observacion' => 'H',
            'padre' => [
                'dni' => '43095494',
                'nombres' => 'JOEL DANTE',
                'apellidos' => 'TRINIDAD QUISPE',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '45882022',
                'nombres' => 'LUZMILA ZARAIDA',
                'apellidos' => 'HUAMAN ALANIA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92862360',
            'nombres' => 'KATZUMI ANAIZ',
            'apellidos' => 'AZA' . "\u{00D1}" . 'A CHANCHARI',
            'padre' => [
                'dni' => '45650129',
                'nombres' => 'WILIAM GIL',
                'apellidos' => 'AZA' . "\u{00D1}" . 'A GAVIDIA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '61902436',
                'nombres' => 'LADDY',
                'apellidos' => 'CHANCHARI ROJAS',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93069385',
            'nombres' => 'ARIANA ESTEFANIA',
            'apellidos' => 'AZA' . "\u{00D1}" . 'A DIAZ',
            'padre' => [
                'dni' => '47965284',
                'nombres' => 'ELOY ADERLYN',
                'apellidos' => 'AZA' . "\u{00D1}" . 'A GAVIDIA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '77339690',
                'nombres' => 'KATHERINE FIORELA',
                'apellidos' => 'DIAZ LEQUERICA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93061986',
            'nombres' => 'ENDERS DOMINICK',
            'apellidos' => 'ARROYO SANTOS',
            'padre' => [
                'dni' => '71202145',
                'nombres' => 'PEDRITO AURELIO',
                'apellidos' => 'ARROYO LOPEZ',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '72611808',
                'nombres' => 'CANDY FABIOLA',
                'apellidos' => 'SANTOS ROJAS',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92859333',
            'nombres' => 'NOAH STING',
            'apellidos' => 'PANDURO GARAY',
            'padre' => [
                'dni' => '70329473',
                'nombres' => 'LUIS ANTONIO STING',
                'apellidos' => 'PANDURO CONTRERAS',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '47021438',
                'nombres' => 'ANGELICA MARIA',
                'apellidos' => 'GARAY ROQUE',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93263627',
            'nombres' => 'BELLA MAHIDEVRAN',
            'apellidos' => 'LEON HERRERA',
            'observacion' => 'H',
            'padre' => [
                'dni' => '22670111',
                'nombres' => 'GILBERT ALFREDO',
                'apellidos' => 'LEON HUAMANI',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '76637120',
                'nombres' => 'YUSANA',
                'apellidos' => 'HERRERA PEREZ',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92871851',
            'nombres' => 'JUAN SALVATORE',
            'apellidos' => 'RAMIREZ AGUILAR',
            'padre' => [
                'dni' => '44897324',
                'nombres' => 'JUAN RAMON',
                'apellidos' => 'RAMIREZ SERQUEN',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '46242303',
                'nombres' => 'NATALI MILUSKA',
                'apellidos' => 'AGUILAR IPARRAGUIRRE',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92998658',
            'nombres' => 'HAYLEN SHERYL',
            'apellidos' => 'LEON ZELAYA',
            'padre' => [
                'dni' => '45892016',
                'nombres' => 'KEVIN JESUS',
                'apellidos' => 'LEON TEMBLADERA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '61561912',
                'nombres' => 'MIRIAM MIRELLA',
                'apellidos' => 'ZELAYA ORTIZ',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93006372',
            'nombres' => 'NOSLY ELISA',
            'apellidos' => 'TARAZANA DIEGO',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '43205649',
                'nombres' => 'RULO LINCOLN',
                'apellidos' => 'TARAZONA JANAMPA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '77247628',
                'nombres' => 'CRIS FLOR',
                'apellidos' => 'DIEGO AQUINO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93019778',
            'nombres' => 'ASIRI RAFFAELLA',
            'apellidos' => 'TIRADO FERRARI',
            'padre' => [
                'dni' => '43133831',
                'nombres' => 'JORGE ANTONIO',
                'apellidos' => 'TIRADO SHERADER',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '10819395',
                'nombres' => 'URSULA VANESSA',
                'apellidos' => 'FERRARI GALVEZ',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92878580',
            'nombres' => 'GABRIEL EDGAR JAMES',
            'apellidos' => 'TRINIDAD URETA',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '46717216',
                'nombres' => 'JOEL DANTE',
                'apellidos' => 'TRINIDAD QUISPE',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '45077069',
                'nombres' => 'CINTYA IRMA',
                'apellidos' => 'URETA VILLAR',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93400540',
            'nombres' => 'EITHAN',
            'apellidos' => 'FRANCO CLEMENTE',
            'padre' => [
                'dni' => '71202292',
                'nombres' => 'KENNETH',
                'apellidos' => 'FRANCO FLORES',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '71206394',
                'nombres' => 'ROSMERY MARIA',
                'apellidos' => 'CLEMENTE DURAN',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93257600',
            'nombres' => 'EMILY RAFFAELLA',
            'apellidos' => 'VARGAS ALARCON',
            'padre' => [
                'dni' => null,
                'nombres' => 'JOSE LUIS',
                'apellidos' => 'VARGAS GUTARRA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44761039',
                'nombres' => 'ISABEL STEPHANIE',
                'apellidos' => 'ALARCON PORTILLO',
                'sexo' => 'F',
            ],
        ],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar Inicial 3 anos.');
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
            ->where('orden', 2)
            ->first();

        if (! $grado) {
            $this->command?->warn('No se encontro el grado de Inicial 3 anos. Ejecuta primero CatalogoSeeder.');
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

        $this->command?->info('Inicial 3 anos registrado con estudiantes y apoderados.');
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
        return $dni . '@ras.edu.pe';
    }

    private function normalizarDni(?string $dni): ?string
    {
        $normalizado = preg_replace('/\D+/', '', trim((string) $dni)) ?? '';

        return $normalizado !== '' ? $normalizado : null;
    }
}
