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

class InicialDosAniosSeeder extends Seeder
{
    private const SECCION = 'A';

    private const ESTUDIANTES = [
        [
            'dni' => '93659269',
            'nombres' => 'SARIA IVANA',
            'apellidos' => 'HUAMANCHA SALVADOR',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '41467069',
                'nombres' => 'SEBASTIAN DARWIN',
                'apellidos' => 'HUAMANCHA AGUILAR',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44504447',
                'nombres' => 'MARIA ESTHER',
                'apellidos' => 'SALVADOR POMALAZA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93490927',
            'nombres' => 'ITZEL KENDALL',
            'apellidos' => 'ESPINOZA PUENTE',
            'padre' => [
                'dni' => '71459337',
                'nombres' => 'KEVIN SANDER',
                'apellidos' => 'ESPINOZA LOPEZ',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '73595083',
                'nombres' => 'JEYDI MAYELI',
                'apellidos' => 'PUENTE ROJAS',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93488689',
            'nombres' => 'FABRIZIO IKER',
            'apellidos' => 'CASTRO PAUCAR',
            'padre' => null,
            'madre' => [
                'dni' => '46440396',
                'nombres' => 'DEYSI KATIA',
                'apellidos' => 'PAUCAR JANAMPA',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93499963',
            'nombres' => 'MATHIAS MARIO OSWALDO',
            'apellidos' => 'MANTARI MAMANI',
            'padre' => [
                'dni' => null,
                'nombres' => 'YEISON',
                'apellidos' => 'MANTARI LOBATON',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '44534717',
                'nombres' => 'CRISTINA',
                'apellidos' => 'MAMANI RAMIREZ',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93343663',
            'nombres' => 'VALENTINO ADRIAN',
            'apellidos' => 'CARHUALLANQUI CANEPA',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '71328246',
                'nombres' => 'JUNIOR JHONATAN',
                'apellidos' => 'CARHUALLANQUI AGUIRRE',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '71090622',
                'nombres' => 'RODINA',
                'apellidos' => 'CANEPA MICHE',
                'sexo' => 'F',
            ],
        ],
        [
            'dni' => '93445611',
            'nombres' => 'ARIELA BELEN',
            'apellidos' => 'TARAZONA LAVADO',
            'observacion' => 'H-',
            'padre' => [
                'dni' => '42338047',
                'nombres' => 'CARLOS ALBERTO',
                'apellidos' => 'TARAZONA JANAMPA',
                'sexo' => 'M',
            ],
            'madre' => [
                'dni' => '42852590',
                'nombres' => 'ADA AGNELA',
                'apellidos' => 'LAVADO CONTRERAS',
                'sexo' => 'F',
            ],
        ],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar Inicial 2 anos.');
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
            ->where('orden', 1)
            ->first();

        if (! $grado) {
            $this->command?->warn('No se encontro el grado de Inicial 2 anos. Ejecuta primero CatalogoSeeder.');
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

        $this->command?->info('Inicial 2 anos registrado con estudiantes y apoderados.');
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
