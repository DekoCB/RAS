<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Plantilla de personal de R.A.S
 *
 * Origen: "PROFESORES R.A.S (2) (3).xlsx" entregado por el colegio.
 *
 * Criterios aplicados al pasar el Excel a datos:
 *  - Los nombres del Excel vienen en formatos mezclados ("APELLIDOS, Nombres"
 *    en Secundaria; sin coma y con el orden invertido en Primaria e Inicial),
 *    asi que nombres y apellidos se separan aqui uno por uno, no por regla.
 *  - Quien aparece repetido (docente + coordinador, por ejemplo) se registra
 *    una sola vez y acumula sus roles; el DNI es la identidad.
 *  - Email institucional DNI@ras.edu.pe y contrasena = DNI, igual que
 *    el resto de seeders del proyecto.
 *  - Solo el personal que dicta clases entra a la tabla `docentes`; el resto
 *    queda como usuario con su rol, como hacen DirectorSeeder y SecretariaSeeder.
 */
class PersonalRasSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->personal() as $data) {
            $this->registrar($data);
        }
    }

    private function registrar(array $data): void
    {
        $dni = $data['dni'] ?? null;
        $nombreCompleto = trim($data['nombres'] . ' ' . $data['apellidos']);

        // Sin DNI no hay email ni contrasena institucional: la cuenta se crea
        // con una clave aleatoria y queda sin acceso hasta que el colegio
        // complete el documento.
        $email = $dni
            ? $dni . '@ras.edu.pe'
            : $data['email_alterno'];

        $password = $dni ?: Str::random(40);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $nombreCompleto,
                'dni'      => $dni,
                'password' => Hash::make($password),
            ]
        );

        $user->update([
            'name' => $nombreCompleto,
            'dni'  => $dni,
        ]);

        foreach ($data['roles'] as $rol) {
            if (!$user->hasRole($rol)) {
                $user->assignRole($rol);
            }
        }

        // Aunque no dicte clases, si el sistema ya tiene su ficha de personal
        // se le enlaza la cuenta para no dejarla huerfana.
        if (empty($data['ensena'])) {
            if ($dni) {
                Docente::where('dni', $dni)->whereNull('user_id')->update(['user_id' => $user->id]);
            }

            return;
        }

        // La ficha puede venir ya cargada del sistema (sin cuenta asociada).
        // En ese caso solo se enlaza al usuario: los datos de produccion mandan.
        $docente = $dni ? Docente::where('dni', $dni)->first() : null;

        if ($docente) {
            $docente->update([
                'user_id'  => $user->id,
                'telefono' => $docente->telefono ?: ($data['telefono'] ?? null),
                'estado'   => $docente->estado ?: 'activo',
            ]);
        } else {
            $docente = Docente::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres'      => $data['nombres'],
                    'apellidos'    => $data['apellidos'],
                    'dni'          => $dni,
                    'telefono'     => $data['telefono'] ?? null,
                    'especialidad' => $data['especialidad'],
                    'estado'       => 'activo',
                ]
            );
        }

        if (empty($docente->codigo)) {
            $docente->update([
                'codigo' => 'DOC-' . str_pad((string) $docente->id, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }

    /**
     * Una fila por persona real del Excel.
     *
     * No se cargan dos filas que no corresponden a personas: el "CORREO
     * INSTITUCIONAL" (contacto@ras.edu.pe) y el "CONVENIO PSICOLOGICO"
     * (telefono 953272829).
     */
    private function personal(): array
    {
        return [
            // ── Secundaria ────────────────────────────────────────────────
            [
                'nombres' => 'Juan Luis', 'apellidos' => 'Mamani Quispe',
                'dni' => '44879130', 'telefono' => '907424769',
                'especialidad' => 'Matemática', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Juan Manuel', 'apellidos' => 'Ayala Ramos',
                'dni' => '76809070', 'telefono' => '913433617',
                'especialidad' => 'Matemática', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Max Pablo', 'apellidos' => 'Escobar Muñoz',
                'dni' => '42635737', 'telefono' => '963325327',
                'especialidad' => 'Matemática', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Eduardo', 'apellidos' => 'Cárdenas Apaza',
                'dni' => '43283489', 'telefono' => '910132067',
                'especialidad' => 'Matemática', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'José Antonio', 'apellidos' => 'Bravo Begazo',
                'dni' => '42697536', 'telefono' => '974825561',
                'especialidad' => 'Ciencia y Tecnología', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Jesús Ángel', 'apellidos' => 'Aquino Quispe',
                'dni' => '41524861', 'telefono' => '955798167',
                'especialidad' => 'Ciencia y Tecnología', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Brayan Omar', 'apellidos' => 'Anconeyra Apaza',
                'dni' => '70845342', 'telefono' => '974237341',
                'especialidad' => 'Ciencia y Tecnología', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'David Alonso', 'apellidos' => 'Ccarita Inca',
                'dni' => '70687876', 'telefono' => '923217529',
                'especialidad' => 'Comunicación', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Bienvenido Francisco', 'apellidos' => 'Mendoza Larico',
                'dni' => '71843352', 'telefono' => '949530100',
                'especialidad' => 'Comunicación', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Hubert Darby', 'apellidos' => 'Condori Flores',
                'dni' => '47329526', 'telefono' => '990632618',
                'especialidad' => 'Comunicación', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Dayana Geraldine', 'apellidos' => 'Barrios Ochoa',
                'dni' => '75612620', 'telefono' => '958975326',
                'especialidad' => 'Comunicación', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Wilian Daniel', 'apellidos' => 'Hancco Gutiérrez',
                'dni' => '72278214', 'telefono' => '929568371',
                'especialidad' => 'Ciencias Sociales', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Jimmy', 'apellidos' => 'Rojas Llerena',
                'dni' => '72895589', 'telefono' => '960839572',
                'especialidad' => 'Inglés', 'ensena' => true, 'roles' => ['Docente'],
            ],

            // ── Primaria (tutoras de aula) ────────────────────────────────
            [
                // Tambien figura como coordinadora de Primaria.
                'nombres' => 'Gaby Juth', 'apellidos' => 'Huarca Banda',
                'dni' => '71636799', 'telefono' => '958139117',
                'especialidad' => 'Tutora 4to Primaria', 'ensena' => true,
                'roles' => ['Docente', 'CoordinadorNivel'],
            ],
            [
                'nombres' => 'Ruth Noemi', 'apellidos' => 'Cacya Andia',
                'dni' => '75844412', 'telefono' => '941704970',
                'especialidad' => 'Tutora 3ro Primaria', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Victoria Lorena', 'apellidos' => 'Rojas Gonzales',
                'dni' => '71483324', 'telefono' => '959938365',
                'especialidad' => 'Tutora 5to Primaria', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                'nombres' => 'Roxana Enda', 'apellidos' => 'Seña Quispe',
                'dni' => '42979678', 'telefono' => '990077599',
                'especialidad' => 'Tutora 1ro Primaria', 'ensena' => true, 'roles' => ['Docente'],
            ],
            [
                // El Excel truncaba los ceros: produccion trae el DNI completo.
                'nombres' => 'Maribel Gisela', 'apellidos' => 'Preciado Silva',
                'dni' => '00256801', 'telefono' => '945758406',
                'especialidad' => 'Tutora 2do Primaria', 'ensena' => true, 'roles' => ['Docente'],
            ],

            // ── Inicial ───────────────────────────────────────────────────
            [
                // Misma persona que la promotora y la coordinadora de Inicial.
                'nombres' => 'Carmen', 'apellidos' => 'Huayllani Hancco',
                'dni' => '42739801', 'telefono' => '958683851',
                'especialidad' => 'Tutora Inicial 4 años', 'ensena' => true,
                'roles' => ['Docente', 'Promotora', 'CoordinadorNivel'],
            ],
            [
                // Tambien figura como coordinador de Secundaria.
                'nombres' => 'Cristhian Rosendo', 'apellidos' => 'Lavado Viza',
                'dni' => '74212284', 'telefono' => '973733642',
                'especialidad' => 'Educación Física', 'ensena' => true,
                'roles' => ['Docente', 'CoordinadorNivel'],
            ],

            // ── Direccion y personal de apoyo ─────────────────────────────
            [
                // El Excel truncaba los ceros: produccion trae el DNI completo.
                'nombres' => 'Pedro Iván', 'apellidos' => 'Arez Yucra',
                'dni' => '02419403', 'telefono' => '958683851',
                'especialidad' => 'Dirección', 'ensena' => false, 'roles' => ['Director'],
            ],
            [
                'nombres' => 'Dionisio Alejandro', 'apellidos' => 'Fernandez Tanga',
                'dni' => '44115112', 'telefono' => '910087346',
                'especialidad' => 'Administración', 'ensena' => false, 'roles' => ['Administracion'],
            ],
            [
                'nombres' => 'Naribel Rosmery', 'apellidos' => 'Rivera Yucra',
                'dni' => '75052187', 'telefono' => '948982567',
                'especialidad' => 'Psicología', 'ensena' => false, 'roles' => ['Psicologia'],
            ],
            [
                'nombres' => 'Damaris Kandy', 'apellidos' => 'Rojas Huayllani',
                'dni' => '61460435', 'telefono' => '910087346',
                'especialidad' => 'Secretaria', 'ensena' => false, 'roles' => ['Secretaria'],
            ],
            [
                // El Excel solo da el nombre "ROXANA" y su telefono, sin DNI
                // ni apellidos: entra sin acceso hasta que el colegio complete.
                'nombres' => 'Roxana', 'apellidos' => '(apellidos por completar)',
                'dni' => null, 'telefono' => '970007099',
                'email_alterno' => 'especialista.ugel@ras.edu.pe',
                'especialidad' => 'Especialista UGEL', 'ensena' => false,
                'roles' => ['EspecialistaUGEL'],
            ],
        ];
    }
}
