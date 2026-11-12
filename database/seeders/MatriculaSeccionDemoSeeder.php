<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class MatriculaSeccionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (!$anio) {
            return;
        }

        $seccion = Seccion::with('grado')
            ->where('anio_escolar_id', $anio->id)
            ->where('nombre', 'A')
            ->whereHas('grado', function ($query) {
                $query->where('nivel', 'Inicial')
                    ->where('nombre', 'like', 'Inicial 4%');
            })
            ->first();

        if (!$seccion) {
            return;
        }

        $nombres = [
            'Lucas', 'Valeria', 'Mateo', 'Danna', 'Thiago', 'Sofia',
            'Joaquin', 'Camila', 'Gael', 'Zoe', 'Maximo', 'Renata',
            'Bruno', 'Mia', 'Benjamin', 'Isabella',
        ];
        $apellidos = [
            'Gomez', 'Diaz', 'Flores', 'Rojas', 'Sanchez', 'Vargas',
            'Castro', 'Silva', 'Torres', 'Ruiz', 'Mendoza', 'Lopez',
        ];

        $cantidad = 12;
        for ($i = 1; $i <= $cantidad; $i++) {
            $nombre = $nombres[$i % count($nombres)];
            $apellido1 = $apellidos[$i % count($apellidos)];
            $apellido2 = $apellidos[($i + 3) % count($apellidos)];

            $dni = '6' . str_pad((string) ($seccion->id * 100 + $i), 7, '0', STR_PAD_LEFT);
            $codigo = 'INI4A-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $carnet = 'INI4A-' . $seccion->id . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);

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
