<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear el permiso si no existe
        $permiso = Permission::findOrCreate('pago.editar', 'web');

        // Asignar a Secretaria
        $secretaria = Role::where('name', 'Secretaria')->where('guard_name', 'web')->first();
        if ($secretaria && !$secretaria->hasPermissionTo('pago.editar')) {
            $secretaria->givePermissionTo($permiso);
        }

        // Asignar a Director
        $director = Role::where('name', 'Director')->where('guard_name', 'web')->first();
        if ($director && !$director->hasPermissionTo('pago.editar')) {
            $director->givePermissionTo($permiso);
        }

        // Asignar a SuperAdmin (por si acaso)
        $superAdmin = Role::where('name', 'SuperAdmin')->where('guard_name', 'web')->first();
        if ($superAdmin && !$superAdmin->hasPermissionTo('pago.editar')) {
            $superAdmin->givePermissionTo($permiso);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permiso = Permission::where('name', 'pago.editar')->where('guard_name', 'web')->first();
        if ($permiso) {
            $permiso->delete();
        }
    }
};
