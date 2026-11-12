<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        // Todos los docentes y personal del colegio REYSCHOOL - 2026
        // Contraseña: DNI de cada docente
        // Email generado (*@reyschool.edu.pe) para quienes no tienen email registrado
        $docentes = [

            // ── INICIAL - Tutoras de aula ──────────────────────────────────────────
            [
                'email'       => 'nikoltapiayalico8@gmail.com',
                'name'        => 'Nikole Briseth Tapia Yalico',
                'nombres'     => 'Nikole Briseth',
                'apellidos'   => 'Tapia Yalico',
                'dni'         => '71117528',
                'especialidad'=> 'Inicial',
                'cargo'       => 'Tutora Inicial 2 años',
            ],
            [
                'email'       => 'anamariabuendia17@gmail.com',
                'name'        => 'Ana Maria Buendia Allpas',
                'nombres'     => 'Ana Maria',
                'apellidos'   => 'Buendia Allpas',
                'dni'         => '41135538',
                'especialidad'=> 'Inicial',
                'cargo'       => 'Tutora Inicial 3 años',
            ],
            [
                // Sin email registrado - se usa DNI@reyschool.edu.pe
                'email'       => '48418283@reyschool.edu.pe',
                'name'        => 'Milagros Laura Torres Sosa',
                'nombres'     => 'Milagros Laura',
                'apellidos'   => 'Torres Sosa',
                'dni'         => '48418283',
                'especialidad'=> 'Inicial',
                'cargo'       => 'Tutora Inicial 4 años',
            ],
            [
                'email'       => 'angeladavilarroyo@gmail.com',
                'name'        => 'Angela Pilar Davila Arroyo',
                'nombres'     => 'Angela Pilar',
                'apellidos'   => 'Davila Arroyo',
                'dni'         => '42244254',
                'especialidad'=> 'Inicial',
                'cargo'       => 'Tutora Inicial 5 años',
            ],

            // ── INICIAL - Auxiliares ───────────────────────────────────────────────
            [
                'email'       => 'oxaadams2023@gmail.com',
                'name'        => 'Luz Eloiza Adams Soto',
                'nombres'     => 'Luz Eloiza',
                'apellidos'   => 'Adams Soto',
                'dni'         => '70786599',
                'especialidad'=> 'Auxiliar',
                'cargo'       => 'Auxiliar de Aula',
            ],
            [
                'email'       => 'eyriorella@gmail.com',
                'name'        => 'Maricielo Orellana Gonzales',
                'nombres'     => 'Maricielo',
                'apellidos'   => 'Orellana Gonzales',
                'dni'         => '77287714',
                'especialidad'=> 'Auxiliar',
                'cargo'       => 'Auxiliar de Aula',
            ],
            [
                'email'       => 'milagrosminaya30@gmail.com',
                'name'        => 'Maura Milagros Minaya Trinidad',
                'nombres'     => 'Maura Milagros',
                'apellidos'   => 'Minaya Trinidad',
                'dni'         => '71098122',
                'especialidad'=> 'Auxiliar',
                'cargo'       => 'Auxiliar de Aula',
            ],

            // ── PRIMARIA - Tutores de aula ─────────────────────────────────────────
            [
                'email'       => 'andreacastroluna2@gmail.com',
                'name'        => 'Andrea Castro Luna',
                'nombres'     => 'Andrea',
                'apellidos'   => 'Castro Luna',
                'dni'         => '71319830',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 1ro A',
            ],
            [
                'email'       => 'milagrosvillenasinchi@gmail.com',
                'name'        => 'Milagros Villena Sinchi',
                'nombres'     => 'Milagros',
                'apellidos'   => 'Villena Sinchi',
                'dni'         => '70261280',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 1ro B',
            ],
            [
                'email'       => 'canozacariasyarira@gmail.com',
                'name'        => 'Yarira Cano Zacarias',
                'nombres'     => 'Yarira',
                'apellidos'   => 'Cano Zacarias',
                'dni'         => '74213467',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 2do A',
            ],
            [
                'email'       => 'carolina12oxa@gmail.com',
                'name'        => 'Carolina De La Torre Jesus',
                'nombres'     => 'Carolina',
                'apellidos'   => 'De La Torre Jesus',
                'dni'         => '46780131',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 2do B',
            ],
            [
                'email'       => 'pilarlatorremiche@gmail.com',
                'name'        => 'Pilar La Torre Miche',
                'nombres'     => 'Pilar',
                'apellidos'   => 'La Torre Miche',
                'dni'         => '72797612',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 3ro A',
            ],
            [
                'email'       => 'cristina.oxapampa@gmail.com',
                'name'        => 'Cristina Carbajal Eslava',
                'nombres'     => 'Cristina',
                'apellidos'   => 'Carbajal Eslava',
                'dni'         => '41831906',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 3ro B',
            ],
            [
                'email'       => 'kalestefanocarrera.170795@gmail.com',
                'name'        => 'Karen Janeth Destefano Carrera',
                'nombres'     => 'Karen Janeth',
                'apellidos'   => 'Destefano Carrera',
                'dni'         => '71202342',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 4to A',
            ],
            [
                'email'       => 'rouse.oxa.94@gmail.com',
                'name'        => 'Rosa Angelica Toledo Anglas',
                'nombres'     => 'Rosa Angelica',
                'apellidos'   => 'Toledo Anglas',
                'dni'         => '73583680',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 4to B',
            ],
            [
                'email'       => 'miossottymildreva@gmail.com',
                'name'        => 'Mildred Vargas Acosta',
                'nombres'     => 'Mildred',
                'apellidos'   => 'Vargas Acosta',
                'dni'         => '73273849',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 5to',
            ],
            [
                'email'       => 'juanitayosselinevargasacosta@gmail.com',
                'name'        => 'Juana Yosseline Vargas Acosta',
                'nombres'     => 'Juana Yosseline',
                'apellidos'   => 'Vargas Acosta',
                'dni'         => '73270656',
                'especialidad'=> 'Primaria',
                'cargo'       => 'Tutora 6to',
            ],
            [
                'email'       => 'edelysalazarmontes@gmail.com',
                'name'        => 'Dania Edely Salazar Montes',
                'nombres'     => 'Dania Edely',
                'apellidos'   => 'Salazar Montes',
                'dni'         => '73956304',
                'especialidad'=> 'Computacion',
                'cargo'       => 'Docente Computacion',
            ],

            // ── DOCENTES ESPECIALISTAS ─────────────────────────────────────────────
            [
                'email'       => 'rbmp0709@gmail.com',
                'name'        => 'Ricardo Bruno Mendoza Pena',
                'nombres'     => 'Ricardo Bruno',
                'apellidos'   => 'Mendoza Pena',
                'dni'         => '71485982',
                'especialidad'=> 'Comunicacion',
                'cargo'       => 'Docente Comunicacion',
            ],
            [
                'email'       => 'omarb8518@gmail.com',
                'name'        => 'Hector Bejar',
                'nombres'     => 'Hector',
                'apellidos'   => 'Bejar',
                'dni'         => '75382286',
                'especialidad'=> 'Matematica',
                'cargo'       => 'Docente Matematica I',
            ],
            [
                'email'       => 'jobarteaga7@gmail.com',
                'name'        => 'Job Jesua Arteaga Ore',
                'nombres'     => 'Job Jesua',
                'apellidos'   => 'Arteaga Ore',
                'dni'         => '72254931',
                'especialidad'=> 'Matematica',
                'cargo'       => 'Docente Matematica II',
            ],
            [
                'email'       => 'juanjva1995@gmail.com',
                'name'        => 'Juan Jordy Vargas Acosta',
                'nombres'     => 'Juan Jordy',
                'apellidos'   => 'Vargas Acosta',
                'dni'         => '73273850',
                'especialidad'=> 'Personal Social',
                'cargo'       => 'Docente Sociales',
            ],
            [
                'email'       => 'miriam8chavez95@gmail.com',
                'name'        => 'Miriam Chavez Eunofre',
                'nombres'     => 'Miriam',
                'apellidos'   => 'Chavez Eunofre',
                'dni'         => '70880659',
                'especialidad'=> 'Ciencia y Tecnologia',
                'cargo'       => 'Docente CTA',
            ],
            [
                'email'       => 'stacyjoannfer@gmail.com',
                'name'        => 'Stacy Fernandez',
                'nombres'     => 'Stacy',
                'apellidos'   => 'Fernandez',
                // DNI no registrado en la lista - pendiente de actualizar
                'dni'         => '00000001',
                'especialidad'=> 'Ingles',
                'cargo'       => 'Docente Ingles',
            ],
            [
                'email'       => 'yucraespinoza29@gmail.com',
                'name'        => 'Josias Juan Yucra Espinoza',
                'nombres'     => 'Josias Juan',
                'apellidos'   => 'Yucra Espinoza',
                'dni'         => '77241459',
                'especialidad'=> 'Educacion Fisica',
                'cargo'       => 'Docente Educacion Fisica',
            ],
            [
                // Sin email registrado - se usa DNI@reyschool.edu.pe
                'email'       => '4304740@reyschool.edu.pe',
                'name'        => 'Heddy Luz Ilave Morales',
                'nombres'     => 'Heddy Luz',
                'apellidos'   => 'Ilave Morales',
                'dni'         => '4304740',
                'especialidad'=> 'Religion',
                'cargo'       => 'Docente Religion',
            ],
            [
                'email'       => 'jhoselinlarahuaman@gmail.com',
                'name'        => 'Jhoselin Fiama Lara Huaman',
                'nombres'     => 'Jhoselin Fiama',
                'apellidos'   => 'Lara Huaman',
                'dni'         => '71224039',
                'especialidad'=> 'Psicologia',
                'cargo'       => 'Psicologa',
            ],

            // ── PERSONAL DE APOYO ──────────────────────────────────────────────────
            [
                'email'       => 'sillojimenez9@gmail.com',
                'name'        => 'Elvis Fermin Sillo Jimenez',
                'nombres'     => 'Elvis Fermin',
                'apellidos'   => 'Sillo Jimenez',
                'dni'         => '42491674',
                'especialidad'=> 'Supervision',
                'cargo'       => 'Supervisor',
            ],
            [
                'email'       => 'valeromendozalizbeth40@gmail.com',
                'name'        => 'Liz Valero Mendoza',
                'nombres'     => 'Liz',
                'apellidos'   => 'Valero Mendoza',
                'dni'         => '41615244',
                'especialidad'=> 'Cocina',
                'cargo'       => 'Cocina',
            ],
            [
                'email'       => 'rosaluzugartetrujillo@gmail.com',
                'name'        => 'Rosa Luz Ugarte Trujillo',
                'nombres'     => 'Rosa Luz',
                'apellidos'   => 'Ugarte Trujillo',
                'dni'         => '46441722',
                'especialidad'=> 'Cocina',
                'cargo'       => 'Auxiliar Cocina',
            ],
        ];

        foreach ($docentes as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'dni'      => $data['dni'],
                    'password' => Hash::make($data['dni']),
                ]
            );

            $user->update([
                'name'     => $data['name'],
                'dni'      => $data['dni'],
                'password' => Hash::make($data['dni']),
            ]);

            $esAuxiliar = ($data['especialidad'] === 'Auxiliar');
            $rolAsignar = $esAuxiliar ? 'Auxiliar' : 'Docente';
            $rolRemover = $esAuxiliar ? 'Docente' : 'Auxiliar';

            if (!$user->hasRole($rolAsignar)) {
                $user->assignRole($rolAsignar);
            }
            if ($user->hasRole($rolRemover)) {
                $user->removeRole($rolRemover);
            }

            $docente = Docente::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres'      => $data['nombres'],
                    'apellidos'    => $data['apellidos'],
                    'dni'          => $data['dni'],
                    'especialidad' => $data['especialidad'],
                    'estado'       => 'activo',
                ]
            );

            // Generar código automático si no tiene
            if (empty($docente->codigo)) {
                $docente->codigo = 'DOC-' . str_pad((string) $docente->id, 5, '0', STR_PAD_LEFT);
                $docente->save();
            }
        }
    }
}
