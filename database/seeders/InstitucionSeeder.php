<?php

namespace Database\Seeders;

use App\Models\HorarioInstitucional;
use App\Models\Institucion;
use App\Models\InstitucionFrase;
use App\Models\InstitucionValor;
use App\Models\Sede;
use Illuminate\Database\Seeder;

/**
 * Datos institucionales tomados del documento de requerimientos v1.0
 * (secciones 2.1 a 2.9). Todo es editable despues desde el panel, asi que
 * este seeder solo deja el punto de partida.
 */
class InstitucionSeeder extends Seeder
{
    public function run(): void
    {
        $this->institucion();
        $this->valores();
        $this->cultura();
        $this->sedes();
        $this->horarios();
    }

    private function institucion(): void
    {
        $institucion = Institucion::firstOrNew([]);

        $institucion->fill([
            'nombre'  => 'R.A.S',
            'sigla'   => 'R.A.S',
            'lema'    => 'La Real Educación es revelada sobrenaturalmente por la inspiración Divina',
            'ciudad'  => 'Arequipa',
            'vision'  => 'Desarrollar el potencial de los estudiantes desde la infancia, formando líderes '
                . 'con visión de desarrollo sostenible y apertura al mundo globalizado, a través de proyectos '
                . 'productivos con identidad creativa, intercultural, ambientalista, emprendedora y espiritual, '
                . 'con un alto índice de calidad pedagógica y uso de la ciencia y la tecnología moderna. '
                . 'El colegio busca consolidarse como institución líder de la zona norte de Arequipa, brindando '
                . 'una educación integral preuniversitaria basada en la formación bíblica, orientada a formar '
                . 'estudiantes líderes, íntegros y justos para el servicio de Cristo Jesús y la sociedad.',
            'mision'  => 'Preparar una generación de líderes tomando como modelo a Jesucristo, formando '
                . 'estudiantes íntegros y justos sostenidos en la fe bíblica. Ofrecer una educación íntegra, '
                . 'innovadora y de calidad, capaz de desarrollar las áreas del saber en un proceso personalizado, '
                . 'investigativo, social y ecológico, contribuyendo a la conservación del medio ambiente, al '
                . 'desarrollo de una familia unida e íntegra, y a una sociedad solidaria en el marco de la '
                . 'justicia y la paz.',
            'telefono_central'     => '908 857 347',
            'telefono_secundaria'  => '958 683 851',
            'telefono_adicional'   => '910 087 346',
            'codigos_modulares'    => [
                'Inicial'    => '1719434',
                'Primaria'   => '1755172',
                'Secundaria' => '1799857',
            ],
            'himno_titulo' => 'Himno R.A.S',
            'himno_autor'  => 'Dionisio Alejandro Fernández Tanga',
        ]);

        $institucion->save();
    }

    /** Seccion 2.4 del documento. */
    private function valores(): void
    {
        $valores = [
            ['Obediencia', 'Se inicia en la Biblia; saber oír y poner en práctica la integridad.'],
            ['Responsabilidad', 'Responder por los actos generados en la toma de decisiones, previniendo los efectos del propio comportamiento.'],
            ['Disciplina', 'Dirigir y regular las acciones de la institución conforme al marco jurídico y las políticas institucionales y estatales.'],
            ['Respeto', 'Fomentar el reconocimiento de la dignidad e integridad de las personas y de la diversidad de pensamiento.'],
            ['Liderazgo', 'Fortalecer la capacidad para prever, resolver y atender problemas conforme a principios de justicia y ética.'],
            ['Igualdad', 'Ofrecer un servicio diferenciado para cada grupo social, con el objetivo común de mejorar su calidad de vida.'],
            ['Democracia', 'Ofrecer servicios diferenciados a cada segmento social, independientemente de sus cualidades o patrimonio.'],
            ['Actitud de servicio', 'Enfocar la energía personal en atender a los usuarios con oportunidad, eficiencia y actitud positiva.'],
            ['Honestidad', 'Congruencia entre el sentir, pensar, decir y actuar, en concordancia con los objetivos de la educación.'],
            ['Honradez', 'Trabajar con claridad, constancia, eficiencia y transparencia en el manejo de los recursos.'],
        ];

        foreach ($valores as $i => [$nombre, $descripcion]) {
            InstitucionValor::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion, 'orden' => $i + 1, 'activo' => true]
            );
        }
    }

    /** Seccion 2.5: frases generales y las variantes por area del letrero fisico. */
    private function cultura(): void
    {
        $frases = [
            ['Motiven, no humillen', null],
            ['Inspiren, no humillen', null],
            ['Familia unida, estudiante íntegro', null],
            ['Una mente fuerte, decisión sana', null],
            ['La fe persevera, una generación de legados', null],
            ['Familia R.A.S unida', 'Restaurante'],
            ['Olviden el pasado, no humillen', 'Psicología'],
            ['Enseñen, no humillen', 'Karate'],
        ];

        // Frases que se guardaron antes sin tildes: se corrigen en su lugar
        // para que updateOrCreate no las duplique.
        $sinTildes = [
            'Familia unida, estudiante integro'          => 'Familia unida, estudiante íntegro',
            'Una mente fuerte, decision sana'            => 'Una mente fuerte, decisión sana',
            'La fe persevera, una generacion de legados' => 'La fe persevera, una generación de legados',
            'Ensenen, no humillen'                       => 'Enseñen, no humillen',
        ];

        foreach ($sinTildes as $antes => $ahora) {
            if (! InstitucionFrase::where('frase', $ahora)->exists()) {
                InstitucionFrase::where('frase', $antes)->update(['frase' => $ahora]);
            }
        }

        foreach ($frases as $i => [$frase, $area]) {
            InstitucionFrase::updateOrCreate(
                ['frase' => $frase],
                ['area' => $area, 'orden' => $i + 1, 'activo' => true]
            );
        }
    }

    /** Seccion 2.1: los dos locales. */
    private function sedes(): void
    {
        Sede::updateOrCreate(
            ['nombre' => 'Local 1'],
            [
                'direccion' => 'Ciudad de Dios, Km 16, Mz. A, Lt. 9, Zona 3',
                'referencia' => 'Sede de Inicial',
                'telefono' => '908 857 347',
                'niveles' => ['Inicial'],
                'orden' => 1,
                'activo' => true,
            ]
        );

        Sede::updateOrCreate(
            ['nombre' => 'Local 2'],
            [
                'direccion' => 'Asoc. Ciudad de Dios, Zona 4, Comite 21, Mz. C, Lt. 15',
                'referencia' => 'Sede de Primaria y Secundaria',
                'telefono' => '958 683 851',
                'niveles' => ['Primaria', 'Secundaria'],
                'orden' => 2,
                'activo' => true,
            ]
        );
    }

    /**
     * Art. 153 del Reglamento Interno (jornada semanal, turno manana):
     * ingreso, tolerancia, salida y tiempo maximo de espera por nivel.
     * El almuerzo (seccion 2.9 del documento de requerimientos) se mantiene.
     */
    private function horarios(): void
    {
        $niveles = [
            'Inicial'    => ['ingreso' => '08:30', 'tolerancia' => 10, 'salida' => '13:20', 'espera' => 10, 'almuerzo' => null],
            'Primaria'   => ['ingreso' => '07:50', 'tolerancia' => 10, 'salida' => '14:00', 'espera' => 15, 'almuerzo' => ['11:30', '12:00']],
            'Secundaria' => ['ingreso' => '07:40', 'tolerancia' => 10, 'salida' => '15:20', 'espera' => 15, 'almuerzo' => ['12:00', '12:30']],
        ];

        foreach ($niveles as $nivel => $h) {
            HorarioInstitucional::updateOrCreate(
                ['nivel' => $nivel, 'grado_id' => null],
                [
                    'hora_ingreso'          => $h['ingreso'],
                    'tolerancia_minutos'    => $h['tolerancia'],
                    'hora_salida'           => $h['salida'],
                    'espera_maxima_minutos' => $h['espera'],
                    'almuerzo_inicio'       => $h['almuerzo'][0] ?? null,
                    'almuerzo_fin'          => $h['almuerzo'][1] ?? null,
                    'activo'                => true,
                ]
            );
        }

        // El reglamento fija una sola hora de salida por nivel, asi que las
        // salidas escalonadas por grado del documento anterior ya no aplican.
        HorarioInstitucional::whereNotNull('grado_id')->delete();
    }
}
