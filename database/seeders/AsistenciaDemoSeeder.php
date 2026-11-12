<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Seeder;

class AsistenciaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (!$anio) {
            return;
        }

        $docente = User::where('email', 'docente@reyschool.test')->first()
            ?? User::first();

        $secciones = Seccion::where('anio_escolar_id', $anio->id)
            ->orderBy('id')
            ->take(3)
            ->get();

        if ($secciones->isEmpty()) {
            $secciones = Seccion::orderBy('id')->take(3)->get();
        }

        if ($secciones->isEmpty()) {
            return;
        }

        $nombres = [
            'Juan', 'Pedro', 'Maria', 'Lucia', 'Carlos', 'Ana', 'Luis', 'Rosa',
            'Jose', 'Elena', 'Miguel', 'Sofia', 'David', 'Camila', 'Jorge', 'Paula',
        ];
        $apellidos = [
            'Garcia', 'Lopez', 'Martinez', 'Sanchez', 'Perez', 'Gomez', 'Diaz', 'Torres',
            'Flores', 'Rivera', 'Vargas', 'Ramos', 'Castro', 'Ruiz', 'Mendoza', 'Rojas',
        ];
        $estados = ['presente', 'tardanza', 'falta', 'justificado'];
        $fechas = [
            now()->subDays(5)->toDateString(),
            now()->subDays(4)->toDateString(),
            now()->subDays(3)->toDateString(),
        ];

        $estudiantesPorSeccion = 12;
        $contador = 1;

        foreach ($secciones as $seccion) {
            for ($i = 1; $i <= $estudiantesPorSeccion; $i++) {
                $nombre = $nombres[($contador + $i) % count($nombres)];
                $apellido1 = $apellidos[($contador + $i) % count($apellidos)];
                $apellido2 = $apellidos[($contador + $i + 7) % count($apellidos)];

                $carnetCodigo = 'DEMO-' . $seccion->id . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $dni = '7' . str_pad((string) ($seccion->id * 100 + $i), 7, '0', STR_PAD_LEFT);
                $codigo = 'EST-' . $seccion->id . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);

                $estudiante = Estudiante::firstOrCreate(
                    ['carnet_codigo' => $carnetCodigo],
                    [
                        'codigo' => $codigo,
                        'nombres' => $nombre,
                        'apellidos' => trim($apellido1 . ' ' . $apellido2),
                        'dni' => $dni,
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

                foreach ($fechas as $fecha) {
                    Asistencia::firstOrCreate(
                        [
                            'fecha' => $fecha,
                            'estudiante_id' => $estudiante->id,
                        ],
                        [
                            'anio_escolar_id' => $anio->id,
                            'seccion_id' => $seccion->id,
                            'estado' => $estados[array_rand($estados)],
                            'registrado_por' => $docente?->id,
                        ]
                    );
                }

                $contador++;
            }
        }
    }
}
