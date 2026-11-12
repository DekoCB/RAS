<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::orderBy('name')->get();

        $qDirector = trim((string) $request->input('q_director', ''));
        $qSecretaria = trim((string) $request->input('q_secretaria', ''));
        $qDocente = trim((string) $request->input('q_docente', ''));
        $qEstudiante = trim((string) $request->input('q_estudiante', ''));
        $qAuxiliar = trim((string) $request->input('q_auxiliar', ''));

        $directores = User::with('roles')
            ->role('Director')
            ->when($qDirector !== '', function ($query) use ($qDirector) {
                $query->where(function ($sub) use ($qDirector) {
                    $sub->where('name', 'like', "%{$qDirector}%")
                        ->orWhere('email', 'like', "%{$qDirector}%")
                        ->orWhere('dni', 'like', "%{$qDirector}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'directores_page')
            ->appends($request->query());

        $secretarias = User::with('roles')
            ->role('Secretaria')
            ->when($qSecretaria !== '', function ($query) use ($qSecretaria) {
                $query->where(function ($sub) use ($qSecretaria) {
                    $sub->where('name', 'like', "%{$qSecretaria}%")
                        ->orWhere('email', 'like', "%{$qSecretaria}%")
                        ->orWhere('dni', 'like', "%{$qSecretaria}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'secretarias_page')
            ->appends($request->query());

        $docentes = User::with(['roles', 'docente'])
            ->role('Docente')
            ->when($qDocente !== '', function ($query) use ($qDocente) {
                $query->where(function ($sub) use ($qDocente) {
                    $sub->where('name', 'like', "%{$qDocente}%")
                        ->orWhere('email', 'like', "%{$qDocente}%")
                        ->orWhere('dni', 'like', "%{$qDocente}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'docentes_page')
            ->appends($request->query());

        $estudiantes = User::with('roles')
            ->role('Estudiante')
            ->when($qEstudiante !== '', function ($query) use ($qEstudiante) {
                $query->where(function ($sub) use ($qEstudiante) {
                    $sub->where('name', 'like', "%{$qEstudiante}%")
                        ->orWhere('email', 'like', "%{$qEstudiante}%")
                        ->orWhere('dni', 'like', "%{$qEstudiante}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'estudiantes_page')
            ->appends($request->query());

        $auxiliares = User::with('roles')
            ->role('Auxiliar')
            ->when($qAuxiliar !== '', function ($query) use ($qAuxiliar) {
                $query->where(function ($sub) use ($qAuxiliar) {
                    $sub->where('name', 'like', "%{$qAuxiliar}%")
                        ->orWhere('email', 'like', "%{$qAuxiliar}%")
                        ->orWhere('dni', 'like', "%{$qAuxiliar}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'auxiliares_page')
            ->appends($request->query());

        return view('admin.usuarios.index', compact(
            'roles',
            'directores',
            'secretarias',
            'docentes',
            'estudiantes',
            'auxiliares',
            'qDirector',
            'qSecretaria',
            'qDocente',
            'qEstudiante',
            'qAuxiliar'
        ));
    }

    public function store(Request $request)
    {
        $role = $request->input('role');

        if ($role === 'Docente') {
            $data = $request->validate([
                'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
                'nombres'       => ['required', 'string', 'max:150'],
                'apellidos'     => ['required', 'string', 'max:150'],
                'dni'           => ['required', 'string', 'max:20', 'unique:users,dni', 'unique:docentes,dni'],
                'telefono'      => ['nullable', 'string', 'max:15'],
                'sexo'          => ['nullable', 'in:M,F'],
                'especialidad'  => ['nullable', 'string', 'max:150'],
                'tipo_contrato' => ['nullable', 'in:Nombrado,Contratado,Suplente'],
            ]);

            $dni = $this->normalizarDni((string) $data['dni']);

            $usuario = User::create([
                'name'     => trim($data['nombres'] . ' ' . $data['apellidos']),
                'email'    => $data['email'],
                'dni'      => $dni,
                'password' => $dni,
            ]);

            $usuario->assignRole('Docente');

            Docente::create([
                'user_id'       => $usuario->id,
                'nombres'       => $data['nombres'],
                'apellidos'     => $data['apellidos'],
                'dni'           => $dni,
                'telefono'      => $data['telefono'] ?? null,
                'sexo'          => $data['sexo'] ?? null,
                'especialidad'  => $data['especialidad'] ?? null,
                'tipo_contrato' => $data['tipo_contrato'] ?? null,
                'estado'        => 'activo',
            ]);

            return redirect()
                ->route('admin-sistema.usuarios.index', ['tab' => 'docentes'])
                ->with('status', 'Docente registrado correctamente.');
        }

        // Roles permitidos (no Estudiante ni Docente ni SuperAdmin)
        $rolesPermitidos = Role::whereNotIn('name', ['Estudiante', 'Docente', 'SuperAdmin'])
            ->pluck('name')->toArray();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'dni'      => ['required', 'string', 'max:20', 'unique:users,dni'],
            'role'     => ['required', 'string', 'in:' . implode(',', $rolesPermitidos)],
        ]);

        $dni = $this->normalizarDni((string) $data['dni']);

        $usuario = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'dni'      => $dni,
            'password' => $dni,
        ]);

        $usuario->assignRole($data['role']);

        $tabRedir = match($data['role']) {
            'Auxiliar'   => 'auxiliares',
            'Director'   => 'directores',
            'Secretaria' => 'secretarias',
            default      => 'directores',
        };

        return redirect()
            ->route('admin-sistema.usuarios.index', ['tab' => $tabRedir])
            ->with('status', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $esSecretaria = $user->hasRole('Secretaria');
        $esDirector = $user->hasRole('Director');
        $esDocente = $user->hasRole('Docente');
        $esAuxiliar = $user->hasRole('Auxiliar');

        if (!$esSecretaria && !$esDirector && !$esDocente && !$esAuxiliar) {
            return redirect()
                ->route('admin-sistema.usuarios.index', ['tab' => 'secretarias'])
                ->with('status', 'Solo se permite editar Director, Secretaria, Docente y Auxiliar desde este panel.');
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'dni' => ['nullable', 'string', 'max:20', 'unique:users,dni,' . $user->id],
            'tab' => ['nullable', 'string', 'in:directores,secretarias,docentes,auxiliares'],
        ]);

        $docenteData = [];
        if ($esDocente) {
            $docenteData = $request->validate([
                'nombres' => ['required', 'string', 'max:150'],
                'apellidos' => ['required', 'string', 'max:150'],
                'dni' => ['required', 'string', 'max:20', 'unique:docentes,dni,' . ($user->docente?->id ?? 'NULL')],
                'fecha_nacimiento' => ['nullable', 'date'],
                'sexo' => ['nullable', 'in:M,F'],
                'telefono' => ['nullable', 'string', 'max:15'],
                'direccion' => ['nullable', 'string', 'max:255'],
                'ciudad' => ['nullable', 'string', 'max:150'],
                'grado_academico' => ['nullable', 'string', 'max:150'],
                'especialidad' => ['nullable', 'string', 'max:150'],
                'tipo_contrato' => ['nullable', 'in:Nombrado,Contratado,Suplente'],
                'fecha_ingreso' => ['nullable', 'date'],
                'estado_docente' => ['nullable', 'in:activo,inactivo'],
            ]);

            $data['name'] = trim($docenteData['nombres'] . ' ' . $docenteData['apellidos']);
            $data['dni'] = $docenteData['dni'];
        } elseif (empty($data['name'])) {
            return redirect()
                ->route('admin-sistema.usuarios.index', ['tab' => $data['tab'] ?? 'secretarias'])
                ->withErrors(['name' => 'El nombre es obligatorio.']);
        }

        $dni = $this->normalizarDni((string) ($data['dni'] ?? ''));
        if ($dni === '') {
            return redirect()
                ->route('admin-sistema.usuarios.index', ['tab' => $data['tab'] ?? 'secretarias'])
                ->withErrors(['dni' => 'El DNI es obligatorio.']);
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'dni' => $dni,
            'password' => $dni,
        ];

        $user->update($payload);

        if ($esDocente) {
            Docente::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres' => $docenteData['nombres'],
                    'apellidos' => $docenteData['apellidos'],
                    'dni' => $dni,
                    'fecha_nacimiento' => $docenteData['fecha_nacimiento'] ?? null,
                    'sexo' => $docenteData['sexo'] ?? null,
                    'telefono' => $docenteData['telefono'] ?? null,
                    'direccion' => $docenteData['direccion'] ?? null,
                    'ciudad' => $docenteData['ciudad'] ?? null,
                    'grado_academico' => $docenteData['grado_academico'] ?? null,
                    'especialidad' => $docenteData['especialidad'] ?? null,
                    'tipo_contrato' => $docenteData['tipo_contrato'] ?? null,
                    'fecha_ingreso' => $docenteData['fecha_ingreso'] ?? null,
                    'estado' => $docenteData['estado_docente'] ?? 'activo',
                ]
            );
        }

        $rolEtiqueta = $esDocente ? 'docente' : ($esDirector ? 'director' : 'secretaria');
        return redirect()
            ->route('admin-sistema.usuarios.index', ['tab' => $data['tab'] ?? 'secretarias'])
            ->with('status', "Datos de {$rolEtiqueta} actualizados correctamente.");
    }

    public function toggleEstado(Request $request, User $user)
    {
        if (!$user->hasRole('Docente')) {
            return response()->json(['error' => 'Solo se puede anular docentes.'], 403);
        }

        $docente = $user->docente;
        if (!$docente) {
            return response()->json(['error' => 'Docente no encontrado.'], 404);
        }

        $nuevoEstado = $docente->estado === 'activo' ? 'inactivo' : 'activo';
        $docente->update(['estado' => $nuevoEstado]);

        return response()->json([
            'ok'     => true,
            'estado' => $nuevoEstado,
            'msg'    => $nuevoEstado === 'inactivo'
                ? 'Docente anulado. Ya no figurará en carnets ni en asignaciones.'
                : 'Docente reactivado correctamente.',
        ]);
    }

    private function normalizarDni(string $dni): string
    {
        return preg_replace('/\D+/', '', trim($dni)) ?? '';
    }
}
