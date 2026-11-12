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

class InicialCuatroAniosSeeder extends Seeder
{
    private const SECCION = 'A';

    private const ESTUDIANTES = [
        [
            'dni' => '92670094',
            'nombres' => 'LUIS ANDRE',
            'apellidos' => 'AVILA AGUILAR',
            'observacion' => 'H',
            'padre' => [
                'dni' => '70288311',
                'nombres' => 'HODI KIARS',
                'apellidos' => 'PUENTE HUARANGA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '70784575',
                'nombres' => 'FIORELLA OLINDA',
                'apellidos' => 'AGUILAR BARRA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92616344',
            'nombres' => 'DYLAN ZAHID',
            'apellidos' => 'CHIMBIRI ORDO' . "\u{00D1}" . 'EZ',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '43365558',
                'nombres' => 'JUAN MOISES',
                'apellidos' => 'CHIMBIRI PAREJA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44667333',
                'nombres' => 'EVELYN DEISY',
                'apellidos' => 'ORDO' . "\u{00D1}" . 'EZ ESPINOZA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92578173',
            'nombres' => 'YADIEL ANDERS',
            'apellidos' => 'JESUS VILLENA',
            'padre' => [
                'dni' => '47764932',
                'nombres' => 'JUAN CRISTIAN',
                'apellidos' => 'JESUS CASTILLEJOS',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '46510004',
                'nombres' => 'JUDITH ANGELA',
                'apellidos' => 'VILLENA CANTALICIO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92444723',
            'nombres' => 'GAELA ALIZE',
            'apellidos' => 'JUAREZ ARIAS',
            'observacion' => 'H',
            'padre' => [
                'dni' => '41227940',
                'nombres' => 'MARCO AURELIO',
                'apellidos' => 'JUAREZ PARRAGA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '43706608',
                'nombres' => 'LIZZETH KATERINE',
                'apellidos' => 'ARIAS YALICO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92544750',
            'nombres' => 'ETHAN ROY LORENZO',
            'apellidos' => 'LOAYZA ARIZOLA',
            'padre' => [
                'dni' => '44547988',
                'nombres' => 'ROYER',
                'apellidos' => 'LOAYZA GRANADOS',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '47013997',
                'nombres' => 'SHARON ANDREINA',
                'apellidos' => 'ARIZOLA NAZAR',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92425657',
            'nombres' => 'VALERIA CAMILA',
            'apellidos' => 'MARTINEZ RUBIO',
            'padre' => [
                'dni' => '71325270',
                'nombres' => 'MIGUEL ANGEL',
                'apellidos' => 'MARTINEZ MEDRANO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '71103796',
                'nombres' => 'KAREN ASTRID',
                'apellidos' => 'RUBIO ORTIZ',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92683498',
            'nombres' => 'SANTIAGO',
            'apellidos' => 'MEZA SALVATIERRA',
            'observacion' => 'H-',
            'padre' => null,
            'madre' => [
                'dni' => '04869322',
                'nombres' => 'KATTY',
                'apellidos' => 'SALVATIERRA JACINTO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92796606',
            'nombres' => 'DYLAN JOSE',
            'apellidos' => 'SALAZAR BORONDA',
            'padre' => [
                'dni' => '70261080',
                'nombres' => 'JOSE LUIS',
                'apellidos' => 'SALAZAR TORRES',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '70489576',
                'nombres' => 'LETICIA YAMILA',
                'apellidos' => 'BORONDA CLAUDIO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92335569',
            'nombres' => 'ETHAN GABRIEL',
            'apellidos' => 'TARAZONA HUERTA',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '46002628',
                'nombres' => 'OSCAR OSWALDO',
                'apellidos' => 'TARAZONA JANAMPA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44161600',
                'nombres' => 'ANA MARIA',
                'apellidos' => 'HUERTA OLLERO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92566993',
            'nombres' => 'ADAIAH BELEN',
            'apellidos' => 'TRUJILLO UGARTE',
            'padre' => [
                'dni' => '71206002',
                'nombres' => 'JUAN GONZALO',
                'apellidos' => 'TRUJILLO CARRERA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '73208460',
                'nombres' => 'RUTH KARINA',
                'apellidos' => 'UGARTE PACHECO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92480305',
            'nombres' => 'ANDREW ETHAN',
            'apellidos' => 'VOGT GARCIA',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '41712537',
                'nombres' => 'RYNNER ANTONHY',
                'apellidos' => 'VOGT CHURRANGO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '42801053',
                'nombres' => 'STEFANIE YENNIFER',
                'apellidos' => 'GARCIA ABREGU',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92503524',
            'nombres' => 'JOSUE SAMIN',
            'apellidos' => 'CARDENAS FARFAN',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '44925245',
                'nombres' => 'DARWIN JERISH',
                'apellidos' => 'CARDENAS ALCANTARA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '42916985',
                'nombres' => 'AMANDA MAGALY',
                'apellidos' => 'FARFAN PIAVIZAN',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92535554',
            'nombres' => 'XAVI EMILIO',
            'apellidos' => 'ARRIETA OREZANO',
            'padre' => [
                'dni' => '77091627',
                'nombres' => 'ABIMAEL',
                'apellidos' => 'ARRIETA MUNGUIA',
                'sexo' => 'M',
            ],
            'madre' => null,
        ],
        [
            'dni' => '92454723',
            'nombres' => 'DOREAN ROBIN',
            'apellidos' => 'RAMOS TROCONES',
            'padre' => [
                'dni' => '76870342',
                'nombres' => 'LINDER LUIS',
                'apellidos' => 'RAMOS RAQUI',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44805677',
                'nombres' => 'CRISTINA',
                'apellidos' => 'TROCONES BRAVO',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92546756',
            'nombres' => 'RUBERT GERARDO',
            'apellidos' => 'SIMON MINAYA',
            'padre' => null,
            'madre' => [
                'dni' => '41457055',
                'nombres' => 'DIANA SADITH',
                'apellidos' => 'MINAYA CARDENAS',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '92310644',
            'nombres' => 'JOSUE MAXIMILIANO',
            'apellidos' => 'SILVA ALBERTO',
            'observacion' => 'H-',
            'padre' => [
                'dni' => null,
                'nombres' => 'JOSUE ANTONY',
                'apellidos' => 'SILVA CHALLCO',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => null,
                'nombres' => 'FLORELA BETRIZ',
                'apellidos' => 'ALBERTO VALLE',
                'sexo' => 'F',
            ],
        ],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar Inicial 4 anos.');
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
            ->where('orden', 3)
            ->first();

        if (! $grado) {
            $this->command?->warn('No se encontro el grado de Inicial 4 anos. Ejecuta primero CatalogoSeeder.');
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

        $this->command?->info('Inicial 4 anos registrado con estudiantes y apoderados.');
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
