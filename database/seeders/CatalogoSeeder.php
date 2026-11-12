<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\ConceptoPago;
use App\Models\Curso;
use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $anioActual = date('Y');
        $anio = AnioEscolar::firstOrCreate(
            ['nombre' => (string) $anioActual],
            [
                'fecha_inicio' => "{$anioActual}-03-01",
                'fecha_fin'    => "{$anioActual}-12-31",
                'activo'       => true,
            ]
        );

        if (!$anio->activo) {
            $anio->update(['activo' => true]);
        }

        // Grados y secciones reales del colegio R.A.S
        // Inicial: 4 grados, 1 sección cada uno (A)
        // Primaria: 6 grados, 1ro-4to con 2 secciones (A y B), 5to-6to con 1 sección (A)
        $mallaAcademica = [
            'Inicial' => [
                ['nombre' => 'Inicial 2 años', 'orden' => 1, 'secciones' => ['A']],
                ['nombre' => 'Inicial 3 años', 'orden' => 2, 'secciones' => ['A']],
                ['nombre' => 'Inicial 4 años', 'orden' => 3, 'secciones' => ['A']],
                ['nombre' => 'Inicial 5 años', 'orden' => 4, 'secciones' => ['A']],
            ],
            'Primaria' => [
                ['nombre' => '1ro de Primaria', 'orden' => 1, 'secciones' => ['A', 'B']],
                ['nombre' => '2do de Primaria', 'orden' => 2, 'secciones' => ['A', 'B']],
                ['nombre' => '3ro de Primaria', 'orden' => 3, 'secciones' => ['A', 'B']],
                ['nombre' => '4to de Primaria', 'orden' => 4, 'secciones' => ['A', 'B']],
                ['nombre' => '5to de Primaria', 'orden' => 5, 'secciones' => ['A']],
                ['nombre' => '6to de Primaria', 'orden' => 6, 'secciones' => ['A']],
            ],
            'Secundaria' => [
                ['nombre' => '1ro de Secundaria', 'orden' => 1, 'secciones' => ['A']],
                ['nombre' => '2do de Secundaria', 'orden' => 2, 'secciones' => ['A']],
                ['nombre' => '3ro de Secundaria', 'orden' => 3, 'secciones' => ['A']],
                ['nombre' => '4to de Secundaria', 'orden' => 4, 'secciones' => ['A']],
                ['nombre' => '5to de Secundaria', 'orden' => 5, 'secciones' => ['A']],
            ],
        ];

        foreach ($mallaAcademica as $nivel => $grados) {
            foreach ($grados as $gradoConfig) {
                $grado = Grado::firstOrCreate(
                    ['nombre' => $gradoConfig['nombre'], 'nivel' => $nivel],
                    ['orden' => $gradoConfig['orden']]
                );

                if ($grado->orden !== $gradoConfig['orden']) {
                    $grado->update(['orden' => $gradoConfig['orden']]);
                }

                foreach ($gradoConfig['secciones'] as $nombreSeccion) {
                    $aula = Aula::firstOrCreate(
                        ['nombre' => "{$gradoConfig['nombre']} - {$nombreSeccion}"],
                        ['capacidad' => 30]
                    );

                    if (is_null($aula->capacidad)) {
                        $aula->update(['capacidad' => 30]);
                    }

                    Seccion::firstOrCreate(
                        [
                            'anio_escolar_id' => $anio->id,
                            'grado_id'        => $grado->id,
                            'nombre'          => $nombreSeccion,
                        ],
                        [
                            'aula_id' => $aula->id,
                            'turno'   => 'Manana',
                        ]
                    );
                }
            }
        }

        // Cursos del colegio R.A.S
        $cursosInicial = [
            'Comunicacion',
            'Matematica',
            'Ciencia y Ambiente',
            'Personal Social',
            'Psicomotricidad',
            'Arte',
            'Religion',
        ];

        $cursosPrimaria = [
            'Comunicacion',
            'Matematica I',
            'Matematica II',
            'Personal Social',
            'Ciencia y Tecnologia',
            'Ingles',
            'Educacion Fisica',
            'Religion',
            'Computacion',
            'Psicologia',
        ];

        foreach (array_unique(array_merge($cursosInicial, $cursosPrimaria)) as $nombreCurso) {
            Curso::firstOrCreate(['nombre' => $nombreCurso]);
        }

        // Conceptos de pago
        ConceptoPago::updateOrCreate(
            ['tipo' => 'matricula'],
            ['nombre' => 'Matrícula', 'monto_base' => 260, 'activo' => true]
        );
        ConceptoPago::updateOrCreate(
            ['tipo' => 'matricula_hermano'],
            ['nombre' => 'Matrícula Hermano', 'monto_base' => 100, 'activo' => true]
        );
        ConceptoPago::updateOrCreate(
            ['tipo' => 'gastos_internos'],
            ['nombre' => 'Matrícula (Gastos Internos)', 'monto_base' => 100, 'activo' => true]
        );
        ConceptoPago::firstOrCreate(
            ['nombre' => 'Pension'],
            ['tipo' => 'pension', 'monto_base' => 80]
        );

        // Cobros de retiro
        ConceptoPago::updateOrCreate(
            ['tipo' => 'retiro_traslado'],
            ['nombre' => 'Traslado', 'monto_base' => 120.00, 'activo' => true]
        );
        ConceptoPago::updateOrCreate(
            ['tipo' => 'retiro_fut'],
            ['nombre' => 'F.U.T', 'monto_base' => 1.00, 'activo' => true]
        );
    }
}
