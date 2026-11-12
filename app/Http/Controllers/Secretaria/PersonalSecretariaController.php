<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\User;
use App\Support\CarnetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PersonalSecretariaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $dni = trim((string) $request->input('dni', ''));
        $estado = $request->input('estado');
        $estadosDisponibles = ['activo', 'inactivo'];

        if ($estado && !in_array($estado, $estadosDisponibles, true)) {
            $estado = null;
        }

        $docentes = Docente::query()
            ->with('user')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('nombres', 'like', "%{$busqueda}%")
                        ->orWhere('apellidos', 'like', "%{$busqueda}%")
                        ->orWhere('dni', 'like', "%{$busqueda}%")
                        ->orWhere('codigo', 'like', "%{$busqueda}%")
                        ->orWhereHas('user', function ($userQuery) use ($busqueda) {
                            $userQuery->where('email', 'like', "%{$busqueda}%")
                                ->orWhere('dni', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when($dni !== '', function ($query) use ($dni) {
                $query->where('dni', 'like', "%{$dni}%");
            })
            ->when($estado, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(4)
            ->withQueryString();

        $nextCodigo = 'DOC-' . str_pad((string) ((Docente::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);

        return view('secretaria.index', compact(
            'docentes',
            'busqueda',
            'dni',
            'estado',
            'estadosDisponibles',
            'nextCodigo'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20', 'unique:docentes,dni', 'unique:users,dni'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:docentes,codigo'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'string', 'max:10'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'grado_academico' => ['nullable', 'string', 'max:120'],
            'especialidad' => ['nullable', 'string', 'max:120'],
            'tipo_contrato' => ['nullable', 'string', 'max:120'],
            'fecha_ingreso' => ['nullable', 'date'],
            'estado' => ['nullable', 'in:activo,inactivo'],
        ]);

        DB::transaction(function () use ($data) {
            $dni = $this->normalizarDni((string) $data['dni']);

            $usuario = User::create([
                'name' => trim($data['nombres'] . ' ' . $data['apellidos']),
                'email' => $data['email'],
                'dni' => $dni,
                'password' => $dni,
            ]);

            $usuario->assignRole('Docente');

            $docenteData = Arr::only($data, [
                'codigo',
                'nombres',
                'apellidos',
                'fecha_nacimiento',
                'sexo',
                'telefono',
                'direccion',
                'ciudad',
                'grado_academico',
                'especialidad',
                'tipo_contrato',
                'fecha_ingreso',
                'estado',
            ]);

            $docenteData['user_id'] = $usuario->id;
            $docenteData['dni'] = $dni;
            $docenteData['estado'] = $docenteData['estado'] ?? 'activo';

            $docente = Docente::create($docenteData);

            if (empty($docente->codigo)) {
                $docente->codigo = 'DOC-' . str_pad((string) $docente->id, 5, '0', STR_PAD_LEFT);
            }

            if (empty($docente->carnet_codigo)) {
                $docente->carnet_codigo = $this->generarCarnetCodigoDocente($docente);
            }

            $docente->save();
        });

        return redirect()
            ->route('secretaria.usuarios.index')
            ->with('status', 'Docente registrado correctamente.');
    }

    public function updateCredenciales(Request $request, Docente $docente)
    {
        $user = $docente->user;

        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
        ]);

        $dni = $this->normalizarDni((string) $docente->dni);
        if ($dni === '') {
            return redirect()
                ->route('secretaria.usuarios.index')
                ->withErrors(['dni' => 'El docente no tiene DNI valido para generar credenciales.']);
        }

        if (!$user) {
            $user = User::create([
                'name' => trim($docente->nombres . ' ' . $docente->apellidos),
                'email' => $data['email'],
                'dni' => $dni,
                'password' => $dni,
            ]);

            $user->assignRole('Docente');

            $docente->user_id = $user->id;
            $docente->save();
        } else {
            $user->update([
                'email' => $data['email'],
                'dni' => $dni,
                'password' => $dni,
            ]);
        }

        return redirect()
            ->route('secretaria.usuarios.index')
            ->with('status', 'Credenciales actualizadas correctamente.');
    }

    private function generarCarnetCodigoDocente(Docente $docente): string
    {
        return CarnetCode::generateDocenteCode($docente);
    }

    private function normalizarDni(string $dni): string
    {
        return preg_replace('/\D+/', '', trim($dni)) ?? '';
    }

    public function carnet(Docente $docente)
    {
        if (empty($docente->carnet_codigo)) {
            $docente->carnet_codigo = $this->generarCarnetCodigoDocente($docente);
            $docente->save();
        }

        return view('secretaria.docentes.carnet', compact('docente'));
    }

    public function carnetModal(Docente $docente)
    {
        if (empty($docente->carnet_codigo)) {
            $docente->carnet_codigo = $this->generarCarnetCodigoDocente($docente);
            $docente->save();
        }

        return response()->json([
            'ok' => true,
            'html' => view('secretaria.docentes.partials.carnet_modal', compact('docente'))->render()
        ]);
    }
}
