<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles y permisos que pide el documento de requerimientos (RNF-01 y
 * seccion 6) y que el sistema aun no tenia, mas los permisos de los
 * modulos nuevos.
 *
 * Se anade sobre lo existente: no elimina ni reasigna los roles previos
 * (SuperAdmin, Director, Secretaria, Auxiliar, Docente, Estudiante).
 */
class RolesRealSchoolSeeder extends Seeder
{
    private const PERMISOS_NUEVOS = [
        // Modulo institucional / sedes
        'institucion.gestionar',
        'sede.ver',
        'sede.gestionar',
        'documento.ver',
        'documento.gestionar',
        // Modulo 2 - Admision
        'admision.ver',
        'admision.registrar',
        'admision.evaluar',
        'admision.resultado',
        // Modulo 7 - Biblioteca
        'biblioteca.ver',
        'biblioteca.gestionar',
        'biblioteca.lectura_registrar',
        // Modulo 8 - Comedor
        'comedor.ver',
        'comedor.registrar',
        'comedor.cobrar',
        'comedor.evidencia',
        // Modulos 6, 12, 13 y 14 - repositorio documental y reuniones
        'reunion.ver',
        'reunion.gestionar',
        // Modulo 9 - Psicologia (RNF-05: acceso restringido)
        'psicologia.ver',
        'psicologia.gestionar',
        // Modulo 10 - Real Academia
        'academia.ver',
        'academia.gestionar',
        // Modulo 11 - Pastoral
        'pastoral.ver',
        'pastoral.gestionar',
        // Modulo 14 - Inmobiliario y Defensa Civil
        'patrimonio.ver',
        'patrimonio.gestionar',
        // Modulo 5 - Academico
        'academico.ver',
        'academico.gestionar',
    ];

    private const ROLES_NUEVOS = [
        'Administracion' => [
            'dashboard.ver', 'institucion.gestionar', 'sede.ver', 'sede.gestionar',
            'documento.ver', 'documento.gestionar', 'reporte.financiero', 'reporte.mensual',
            'biblioteca.ver', 'comedor.ver', 'comedor.cobrar',
            'admision.ver', 'admision.registrar', 'admision.evaluar', 'admision.resultado',
            'reunion.ver', 'reunion.gestionar',
            'academia.ver', 'academia.gestionar',
            'patrimonio.ver', 'patrimonio.gestionar',
        ],
        'Promotora' => [
            'dashboard.ver', 'institucion.gestionar', 'sede.ver',
            'documento.ver', 'reporte.academico', 'reporte.financiero',
        ],
        'CoordinadorGeneral' => [
            'dashboard.ver', 'documento.ver', 'documento.gestionar', 'horario.ver',
            'nota.ver', 'reporte.academico', 'biblioteca.ver', 'biblioteca.gestionar',
            'reunion.ver', 'reunion.gestionar',
            'academico.ver', 'academico.gestionar',
        ],
        'CoordinadorNivel' => [
            'dashboard.ver', 'documento.ver', 'horario.ver', 'nota.ver',
            'reporte.academico', 'biblioteca.ver',
            'reunion.ver', 'reunion.gestionar', 'academico.ver',
        ],
        'CoordinadorAcademico' => [
            'dashboard.ver', 'nota.ver', 'reporte.academico', 'horario.ver',
            'biblioteca.ver', 'biblioteca.gestionar',
            'academico.ver', 'academico.gestionar', 'reunion.ver',
        ],
        'Psicologia' => [
            'dashboard.ver', 'estudiante.ver', 'incidencia.ver', 'incidencia.crear',
            'psicologia.ver', 'psicologia.gestionar', 'reunion.ver',
        ],
        'Pastoral' => [
            'dashboard.ver', 'estudiante.ver', 'documento.ver',
            // La entrevista pastoral es una etapa del proceso de admision
            'admision.ver', 'admision.evaluar',
            'pastoral.ver', 'pastoral.gestionar',
        ],
        // RF-68: modulo del contador
        'Contador' => [
            'dashboard.ver', 'pago.ver', 'deuda.ver', 'ingreso_egreso.ver',
            'reporte.financiero', 'reporte.mensual', 'reporte.pagos_dia', 'reporte.morosos',
            'documento.ver', 'patrimonio.ver',
        ],
        // RF-73: acceso externo de supervision, solo de lectura
        'EspecialistaUGEL' => [
            'dashboard.ver', 'nota.ver', 'reporte.academico', 'documento.ver',
            'academico.ver',
        ],
        'PadreFamilia' => [
            'dashboard.ver', 'deuda.ver', 'pago.ver', 'nota.ver', 'asistencia.ver', 'documento.ver',
        ],
        'Bibliotecario' => [
            'dashboard.ver', 'biblioteca.ver', 'biblioteca.gestionar', 'biblioteca.lectura_registrar',
        ],
        'Comedor' => [
            'dashboard.ver', 'comedor.ver', 'comedor.registrar', 'comedor.cobrar', 'comedor.evidencia',
        ],
        'Academia' => [
            'dashboard.ver', 'asistencia.ver', 'asistencia.registrar', 'nota.ver',
            'academia.ver', 'academia.gestionar',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISOS_NUEVOS as $permiso) {
            Permission::findOrCreate($permiso);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES_NUEVOS as $nombre => $permisos) {
            $rol = Role::findOrCreate($nombre);
            // syncPermissions se limita a los permisos que existen de verdad
            $rol->syncPermissions(array_filter(
                $permisos,
                fn ($p) => Permission::where('name', $p)->exists()
            ));
        }

        // El SuperAdmin conserva todo, incluidos los permisos nuevos.
        $superAdmin = Role::where('name', 'SuperAdmin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(self::PERMISOS_NUEVOS);
        }

        // Los modulos nuevos que la secretaria opera en el dia a dia.
        $secretaria = Role::where('name', 'Secretaria')->first();
        if ($secretaria) {
            $secretaria->givePermissionTo([
                'sede.ver', 'documento.ver',
                'biblioteca.ver', 'comedor.ver', 'comedor.registrar', 'comedor.cobrar',
                'admision.ver', 'admision.registrar',
            ]);
        }

        // El director supervisa los modulos transversales.
        $director = Role::where('name', 'Director')->first();
        if ($director) {
            $director->givePermissionTo([
                'documento.ver', 'documento.gestionar', 'reunion.ver', 'reunion.gestionar',
                'academico.ver', 'academico.gestionar', 'patrimonio.ver',
                'academia.ver', 'admision.ver', 'admision.resultado',
            ]);
        }

        // Los docentes registran las lecturas de sus estudiantes (RF-45).
        $docente = Role::where('name', 'Docente')->first();
        if ($docente) {
            $docente->givePermissionTo([
                'biblioteca.ver', 'biblioteca.lectura_registrar',
                'academico.ver', 'academico.gestionar', 'reunion.ver',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
