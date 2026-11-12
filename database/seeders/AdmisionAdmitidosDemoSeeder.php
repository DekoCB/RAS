<?php

namespace Database\Seeders;

use App\Models\AdmisionExamen;
use App\Models\AnioEscolar;
use App\Models\Grado;
use App\Models\Postulante;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Datos de prueba: 20 postulantes que ya pasaron todo el proceso de admision
 * (pago validado, etapas completadas, examen aprobado y resultado publicado
 * como Admitido). Quedan listos para "Cargar desde Admision" en Matriculas.
 */
class AdmisionAdmitidosDemoSeeder extends Seeder
{
    /** [nombres, apellidos, sexo, nivel, nombre del grado, apoderado nombres, apoderado apellidos, parentesco] */
    private const POSTULANTES = [
        ['Valentina',      'Huamani Ccorahua',  'F', 'Inicial',    'Inicial 3 años',    'Rosa',      'Ccorahua Mamani',  'Madre'],
        ['Mateo',          'Quispe Chambi',     'M', 'Inicial',    'Inicial 3 años',    'Julio',     'Quispe Apaza',     'Padre'],
        ['Luciana',        'Condori Flores',    'F', 'Inicial',    'Inicial 4 años',    'Maria',     'Flores Zapana',    'Madre'],
        ['Thiago',         'Mamani Puma',       'M', 'Inicial',    'Inicial 4 años',    'Elena',     'Puma Vilca',       'Madre'],
        ['Camila',         'Apaza Ticona',      'F', 'Inicial',    'Inicial 5 años',    'Carlos',    'Apaza Choque',     'Padre'],
        ['Santiago',       'Choque Larico',     'M', 'Inicial',    'Inicial 5 años',    'Ana',       'Larico Cutipa',    'Madre'],
        ['Isabella',       'Vilca Ramos',       'F', 'Primaria',   '1ro de Primaria',   'Pedro',     'Vilca Huanca',     'Padre'],
        ['Sebastian',      'Cutipa Mendoza',    'M', 'Primaria',   '1ro de Primaria',   'Lucia',     'Mendoza Arias',    'Madre'],
        ['Mariana',        'Zapana Torres',     'F', 'Primaria',   '2do de Primaria',   'Jorge',     'Zapana Ruiz',      'Padre'],
        ['Gael',           'Huanca Paredes',    'M', 'Primaria',   '2do de Primaria',   'Silvia',    'Paredes Luna',     'Madre'],
        ['Antonella',      'Arias Rivera',      'F', 'Primaria',   '3ro de Primaria',   'Miguel',    'Arias Soto',       'Padre'],
        ['Benjamin',       'Luna Salazar',      'M', 'Primaria',   '3ro de Primaria',   'Patricia',  'Salazar Rojas',    'Madre'],
        ['Emilia',         'Rojas Medina',      'F', 'Primaria',   '4to de Primaria',   'Raul',      'Rojas Perez',      'Padre'],
        ['Joaquin',        'Perez Cardenas',    'M', 'Primaria',   '5to de Primaria',   'Carmen',    'Cardenas Gomez',   'Madre'],
        ['Renata',         'Gomez Castillo',    'F', 'Primaria',   '6to de Primaria',   'Luis',      'Gomez Diaz',       'Padre'],
        ['Adrian',         'Diaz Morales',      'M', 'Secundaria', '1ro de Secundaria', 'Gloria',    'Morales Vega',     'Madre'],
        ['Fernanda',       'Vega Rios',         'F', 'Secundaria', '1ro de Secundaria', 'Oscar',     'Vega Campos',      'Padre'],
        ['Nicolas',        'Campos Ortiz',      'M', 'Secundaria', '2do de Secundaria', 'Teresa',    'Ortiz Navarro',    'Madre'],
        ['Daniela',        'Navarro Silva',     'F', 'Secundaria', '3ro de Secundaria', 'Hector',    'Navarro Ponce',    'Padre'],
        ['Rodrigo',        'Ponce Aguilar',     'M', 'Secundaria', '4to de Secundaria', 'Rocio',     'Aguilar Reyes',    'Madre'],
    ];

    private const COLEGIOS = ['IE 40001 Ciudad de Dios', 'IE 40073 Ampliacion Paucarpata', 'IEP San Juan Bautista', 'IE Jose Carlos Mariategui', 'Ninguno'];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first() ?? AnioEscolar::orderByDesc('id')->first();
        if (!$anio) {
            $this->command->warn('No hay año escolar. Ejecuta CatalogoSeeder primero.');
            return;
        }

        $usuario = User::role('SuperAdmin')->first() ?? User::first();
        $anioNum = (int) $anio->nombre ?: (int) date('Y');

        $examenes = [];
        foreach (['Inicial', 'Primaria', 'Secundaria'] as $nivel) {
            $examenes[$nivel] = AdmisionExamen::firstOrCreate(
                ['anio_escolar_id' => $anio->id, 'nivel' => $nivel, 'titulo' => "Examen de admision {$nivel} {$anioNum}"],
                ['puntaje_maximo' => 20, 'puntaje_minimo' => 11, 'activo' => true,
                 'descripcion' => 'Evaluacion de conocimientos diferenciada por nivel (RF-17).']
            );
        }

        $sedes = Sede::orderBy('orden')->get();
        $creados = 0;

        foreach (self::POSTULANTES as $i => [$nombres, $apellidos, $sexo, $nivel, $gradoNombre, $apoNombres, $apoApellidos, $parentesco]) {
            $dni = (string) (72000100 + $i);
            if (Postulante::where('dni', $dni)->exists()) {
                continue;
            }

            $grado = Grado::where('nombre', $gradoNombre)->first();
            $sede  = $sedes->first(fn ($s) => in_array($nivel, $s->niveles ?? [], true)) ?? $sedes->first();
            // Edad aproximada segun el grado: Inicial "N años", Primaria 6-11, Secundaria 12-16
            $edad  = match ($nivel) { 'Inicial' => (int) $gradoNombre[8], 'Primaria' => 5 + (int) $gradoNombre[0], default => 11 + (int) $gradoNombre[0] };
            $publicado = Carbon::create($anioNum, 2, 10)->addDays($i);

            $p = Postulante::create([
                'anio_escolar_id'      => $anio->id,
                'codigo'               => Postulante::siguienteCodigo($anio->id, $anio->nombre),
                'sede_id'              => $sede?->id,
                'nivel'                => $nivel,
                'grado_id'             => $grado?->id,
                'nombres'              => $nombres,
                'apellidos'            => $apellidos,
                'dni'                  => $dni,
                'fecha_nacimiento'     => Carbon::create($anioNum - $edad, 1 + ($i % 12), 1 + ($i % 27)),
                'sexo'                 => $sexo,
                'direccion'            => 'Ciudad de Dios, Zona ' . (1 + $i % 5) . ', Mz. ' . chr(65 + $i % 8) . ', Lt. ' . (1 + $i % 20),
                'colegio_procedencia'  => $nivel === 'Inicial' && str_contains($gradoNombre, '3') ? 'Ninguno' : self::COLEGIOS[$i % count(self::COLEGIOS)],
                'apoderado_nombres'    => $apoNombres,
                'apoderado_apellidos'  => $apoApellidos,
                'apoderado_dni'        => (string) (42000100 + $i),
                'apoderado_parentesco' => $parentesco,
                'apoderado_fecha_nacimiento' => Carbon::create($anioNum - 28 - ($i % 15), 1 + ($i % 12), 1 + ($i * 3 % 27)),
                'apoderado_sexo'       => $parentesco === 'Madre' ? 'F' : 'M',
                'apoderado_estado_civil' => ['Casado(a)', 'Conviviente', 'Soltero(a)'][$i % 3],
                'apoderado_ciudad'     => 'Arequipa',
                'apoderado_direccion'  => 'Ciudad de Dios, Zona ' . (1 + $i % 5) . ', Mz. ' . chr(65 + $i % 8) . ', Lt. ' . (1 + $i % 20),
                'apoderado_telefono'   => '9' . str_pad((string) (51000000 + $i * 7919), 8, '0', STR_PAD_LEFT),
                'apoderado_email'      => strtolower($apoNombres . '.' . explode(' ', $apoApellidos)[0]) . '@gmail.com',
                'estado'               => 'admitido',
                'paso_actual'          => Postulante::TOTAL_PASOS,
                'resultado_publicado_at' => $publicado,
                'resultado_canal'      => $i % 3 === 0 ? 'WhatsApp' : ($i % 3 === 1 ? 'Llamada' : 'Correo'),
                'registrado_por'       => $usuario?->id,
            ]);

            // Pago de la cuota de ingreso, validado
            $p->pagos()->create([
                'monto'            => 80.00,
                'metodo'           => ['yape', 'efectivo', 'transferencia'][$i % 3],
                'numero_operacion' => $i % 3 === 1 ? null : 'OP-' . (100200 + $i),
                'fecha'            => $publicado->copy()->subDays(12)->toDateString(),
                'estado'           => 'validado',
                'registrado_por'   => $usuario?->id,
                'validado_por'     => $usuario?->id,
            ]);

            // Las 7 etapas completadas en orden
            foreach (array_keys(Postulante::ETAPAS) as $n => $etapa) {
                $fecha = $publicado->copy()->subDays(10 - $n)->setTime(9 + $n % 3, 0);
                $p->etapas()->create([
                    'etapa'            => $etapa,
                    'estado'           => 'completada',
                    'fecha_programada' => $fecha,
                    'fecha_realizada'  => $fecha,
                ]);
            }

            // Examen aprobado (entre 12 y 19 sobre 20)
            $p->examenes()->create([
                'examen_id'   => $examenes[$nivel]->id,
                'fecha'       => $publicado->copy()->subDays(3)->setTime(10, 0),
                'puntaje'     => 12 + ($i * 7) % 8,
                'evaluado_por'=> $usuario?->id,
            ]);

            $creados++;
        }

        $this->command->info("Postulantes admitidos de prueba creados: {$creados}.");
    }
}
