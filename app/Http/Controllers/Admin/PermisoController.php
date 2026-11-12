<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::with('roles')->orderBy('name')->get();
        $permisoActual = null;

        if ($request->filled('permission_id')) {
            $permisoActual = $permissions->firstWhere('id', (int) $request->input('permission_id'));
        }

        if (!$permisoActual) {
            $permisoActual = $permissions->first();
        }

        $roles = Role::orderBy('name')->get();

        return view('admin.permisos.index', compact('permissions', 'permisoActual', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $data['name']]);

        return redirect()
            ->route('admin-sistema.permisos.index')
            ->with('status', 'Permiso creado.');
    }

    public function roles(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $permission->syncRoles($data['roles'] ?? []);

        return redirect()
            ->route('admin-sistema.permisos.index', ['permission_id' => $permission->id])
            ->with('status', 'Roles actualizados.');
    }
}
