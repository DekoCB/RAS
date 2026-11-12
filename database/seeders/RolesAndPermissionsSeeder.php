<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.ver',
            'usuarios.gestionar',
            'roles.gestionar',
            'permisos.gestionar',
            'estudiante.ver',
            'estudiante.crear',
            'estudiante.editar',
            'estudiante.eliminar',
            'apoderado.ver',
            'apoderado.crear',
            'apoderado.editar',
            'apoderado.eliminar',
            'matricula.crear',
            'matricula.editar',
            'matricula.confirmar',
            'matricula.retirar',
            'matricula.trasladar',
            'concepto_pago.gestionar',
            'deuda.ver',
            'pago.ver',
            'pago.registrar',
            'pago.editar',
            'pago.anular',
            'voucher.subir',
            'voucher.validar',
            'voucher.rechazar',
            'inventario.ver',
            'inventario.movimiento',
            'ingreso_egreso.ver',
            'ingreso_egreso.registrar',
            'asistencia.ver',
            'asistencia.registrar',
            'nota.ver',
            'nota.registrar',
            'nota.editar',
            'nota.editar_fuera_plazo',
            'nota.cerrar',
            'nota.reabrir',
            'incidencia.ver',
            'incidencia.crear',
            'incidencia.actualizar',
            'horario.ver',
            'reporte.pagos_dia',
            'reporte.morosos',
            'reporte.mensual',
            'reporte.academico',
            'reporte.financiero',
            'notificacion.enviar',
            'notificacion.ver',
            'caja.cerrar',
            'caja.anular',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $roles = [
            'SuperAdmin' => $permissions,
            'Director' => [
                'dashboard.ver',
                'reporte.pagos_dia',
                'reporte.morosos',
                'reporte.mensual',
                'reporte.academico',
                'reporte.financiero',
                'nota.cerrar',
                'nota.reabrir',
                'nota.editar_fuera_plazo',
                'pago.editar',
                'pago.anular',
                'caja.cerrar',
                'caja.anular',
                'incidencia.ver',
                'incidencia.actualizar',
                'usuarios.gestionar',
                'roles.gestionar',
            ],
            'Secretaria' => [
                'dashboard.ver',
                'usuarios.gestionar',
                'estudiante.ver',
                'estudiante.crear',
                'estudiante.editar',
                'apoderado.ver',
                'apoderado.crear',
                'apoderado.editar',
                'matricula.crear',
                'matricula.editar',
                'matricula.confirmar',
                'matricula.retirar',
                'matricula.trasladar',
                'concepto_pago.gestionar',
                'deuda.ver',
                'pago.ver',
                'pago.registrar',
                'pago.editar',
                'voucher.validar',
                'voucher.rechazar',
                'inventario.ver',
                'inventario.movimiento',
                'ingreso_egreso.ver',
                'ingreso_egreso.registrar',
                'asistencia.ver',
                'reporte.pagos_dia',
                'reporte.morosos',
                'reporte.mensual',
                'notificacion.enviar',
                'incidencia.crear',
                'incidencia.ver',
                'incidencia.actualizar',
            ],
            'Auxiliar' => [
                'dashboard.ver',
                'asistencia.ver',
                'asistencia.registrar',
            ],
            'Docente' => [
                'dashboard.ver',
                'asistencia.registrar',
                'asistencia.ver',
                'nota.registrar',
                'nota.editar',
                'nota.ver',
                'incidencia.crear',
                'incidencia.ver',
                'horario.ver',
            ],
            'Estudiante' => [
                'asistencia.ver',
                'nota.ver',
                'deuda.ver',
                'pago.ver',
                'notificacion.ver',
            ],
        ];

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolPortal = Role::where('name', 'Portal')->first();
        if ($rolPortal) {
            $rolPortal->syncPermissions([]);
            $rolPortal->delete();
        }

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName);
            $permissionsToSync = Permission::whereIn('name', $rolePermissions)->get();
            $role->syncPermissions($permissionsToSync);
        }
    }
}
