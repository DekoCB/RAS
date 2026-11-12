<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocenteOtraSeccionSeeder extends Seeder
{
    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (!$anio) {
            return;
        }

        $docente = User::where('email', 'docente@reyschool.test')->first()
            ?? User::role('Docente')->orderBy('id')->first();

        if (!$docente) {
            return;
        }

        $seccionBase = Seccion::where('anio_escolar_id', $anio->id)
            ->where('nombre', 'A')
            ->whereHas('grado', function ($query) {
                $query->where('nivel', 'Inicial')
                    ->where('nombre', 'like', 'Inicial 4%');
            })
            ->first();

        $preferencias = [
            ['nivel' => 'Inicial', 'grado_like' => 'Inicial 5%', 'seccion' => 'A'],
            ['nivel' => 'Primaria', 'grado_like' => '1ro de Primaria', 'seccion' => 'A'],
        ];

        $seccion = null;

        foreach ($preferencias as $pref) {
            $query = Seccion::with('grado')
                ->where('anio_escolar_id', $anio->id)
                ->where('nombre', $pref['seccion'])
                ->whereHas('grado', function ($gradoQuery) use ($pref) {
                    $gradoQuery->where('nivel', $pref['nivel'])
                        ->where('nombre', 'like', $pref['grado_like']);
                });

            if ($seccionBase) {
                $query->where('id', '!=', $seccionBase->id);
            }

            $seccion = $query->first();
            if ($seccion) {
                break;
            }
        }

        if (!$seccion) {
            $query = Seccion::with('grado')
                ->where('anio_escolar_id', $anio->id);

            if ($seccionBase) {
                $query->where('id', '!=', $seccionBase->id);
            }

            $seccion = $query->orderBy('id')->first();
        }

        if (!$seccion) {
            return;
        }

        $cursosPorNivel = [
            'Inicial' => [
                'Comunicacion',
                'Matematica',
                'Psicomotricidad',
                'Arte',
                'Personal Social',
            ],
            'Primaria' => [
                'Comunicacion',
                'Matematica',
                'Ciencia y Tecnologia',
                'Personal Social',
                'Educacion Fisica',
                'Arte y Cultura',
                'Ingles',
                'Religion',
            ],
            'Secundaria' => [
                'Comunicacion',
                'Matematica',
                'Ciencia y Tecnologia',
                'Historia Geografia y Economia',
                'Formacion Ciudadana y Civica',
                'Educacion Fisica',
                'Arte y Cultura',
                'Ingles',
                'Educacion para el Trabajo',
            ],
        ];

        $nivel = $seccion->grado?->nivel;
        if ($nivel && isset($cursosPorNivel[$nivel])) {
            foreach ($cursosPorNivel[$nivel] as $nombreCurso) {
                Curso::firstOrCreate(['nombre' => $nombreCurso]);
            }
        }

        $cursoIds = $seccion->cursos()
            ->wherePivot('anio_escolar_id', $anio->id)
            ->pluck('cursos.id')
            ->values();

        if ($cursoIds->isEmpty() && $nivel && isset($cursosPorNivel[$nivel])) {
            $cursoIds = Curso::whereIn('nombre', $cursosPorNivel[$nivel])->pluck('id');
            foreach ($cursoIds as $cursoId) {
                $seccion->cursos()->syncWithoutDetaching([
                    $cursoId => ['anio_escolar_id' => $anio->id],
                ]);
            }
        }

        foreach ($cursoIds as $cursoId) {
            $seccion->cursos()->updateExistingPivot($cursoId, [
                'anio_escolar_id' => $anio->id,
                'docente_id' => $docente->id,
            ]);
        }

        $nombres = [
            'Paula', 'Diego', 'Valentina', 'Mateo', 'Luciana', 'Thiago',
            'Alessia', 'Fabian', 'Ariana', 'Samuel', 'Emma', 'Jeronimo',
        ];
        $apellidos = [
            'Torres', 'Ramirez', 'Silva', 'Mendoza', 'Soto', 'Gutierrez',
            'Paredes', 'Rios', 'Quispe', 'Luna', 'Diaz', 'Navarro',
        ];

        $cantidad = 10;
        for ($i = 1; $i <= $cantidad; $i++) {
            $nombre = $nombres[$i % count($nombres)];
            $apellido1 = $apellidos[$i % count($apellidos)];
            $apellido2 = $apellidos[($i + 4) % count($apellidos)];

            $dni = '8' . str_pad((string) ($seccion->id * 100 + $i), 7, '0', STR_PAD_LEFT);
            $codigo = 'SEC-' . $seccion->id . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $carnet = 'SEC-' . $seccion->id . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            $estudiante = Estudiante::firstOrCreate(
                ['dni' => $dni],
                [
                    'codigo' => $codigo,
                    'carnet_codigo' => $carnet,
                    'nombres' => $nombre,
                    'apellidos' => trim($apellido1 . ' ' . $apellido2),
                    'estado' => 'activo',
                ]
            );

            Matricula::firstOrCreate(
                [
                    'anio_escolar_id' => $anio->id,
                    'estudiante_id' => $estudiante->id,
                ],
                [
                    'grado_id' => $seccion->grado_id,
                    'seccion_id' => $seccion->id,
                    'fecha_matricula' => now()->toDateString(),
                    'estado' => 'registrada',
                ]
            );
        }
    }
}
