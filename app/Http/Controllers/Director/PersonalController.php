<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\User;
use App\Support\CarnetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:usuarios.gestionar');
    }

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $tab      = $request->input('tab', 'docentes');

        $applySearch = function ($query) use ($busqueda) {
            return $query->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where(function ($sub) use ($busqueda) {
                    $sub->where('name', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('dni', 'like', "%{$busqueda}%");
                });
            });
        };

        $docentes = $applySearch(
            User::with(['roles', 'docente'])
                ->whereHas('roles', fn($q) => $q->where('name', 'Docente'))
                ->orderByDesc('created_at')
        )->paginate(10, ['*'], 'pag_doc')->withQueryString();

        $secretarias = $applySearch(
            User::with('roles')
                ->whereHas('roles', fn($q) => $q->where('name', 'Secretaria'))
                ->orderByDesc('created_at')
        )->paginate(10, ['*'], 'pag_sec')->withQueryString();

        $totalDocentes   = User::role('Docente')->count();
        $totalSecretarias = User::role('Secretaria')->count();
        $nextCodigo = 'DOC-' . str_pad((string) ((Docente::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);

        return view('director.personal.index', compact(
            'docentes',
            'secretarias',
            'busqueda',
            'tab',
            'totalDocentes',
            'totalSecretarias',
            'nextCodigo'
        ));
    }

    public function storeDocente(Request $request)
    {
        $data = $request->validateWithBag('docente', [
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20', 'unique:docentes,dni', 'unique:users,dni'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'especialidad' => ['nullable', 'string', 'max:120'],
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

            $docente = Docente::create([
                'user_id' => $usuario->id,
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'dni' => $dni,
                'telefono' => $data['telefono'] ?? null,
                'especialidad' => $data['especialidad'] ?? null,
                'fecha_ingreso' => $data['fecha_ingreso'] ?? null,
                'estado' => $data['estado'] ?? 'activo',
            ]);

            if (empty($docente->codigo)) {
                $docente->codigo = 'DOC-' . str_pad((string) $docente->id, 5, '0', STR_PAD_LEFT);
            }

            if (empty($docente->carnet_codigo)) {
                $docente->carnet_codigo = $this->generarCarnetCodigoDocente($docente);
            }

            $docente->save();
        });

        return redirect()
            ->route('director.personal.index')
            ->with('status', 'Docente creado correctamente.');
    }

    public function storeSecretaria(Request $request)
    {
        $data = $request->validateWithBag('secretaria', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'dni' => ['required', 'string', 'max:20', 'unique:users,dni'],
        ]);

        $dni = $this->normalizarDni((string) $data['dni']);

        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'dni' => $dni,
            'password' => $dni,
        ]);

        $usuario->assignRole('Secretaria');

        return redirect()
            ->route('director.personal.index')
            ->with('status', 'Secretaria creada correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $esDocente = $user->hasRole('Docente');
        $esSecretaria = $user->hasRole('Secretaria');

        if (!$esDocente && !$esSecretaria) {
            return redirect()
                ->route('director.personal.index')
                ->with('status', 'Solo se pueden editar docentes y secretarias.');
        }

        $data = $request->validate([
            'tab' => ['nullable', 'string', 'in:docentes,secretarias'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'dni' => ['nullable', 'string', 'max:20', 'unique:users,dni,' . $user->id],
        ]);

        $payloadUser = [
            'email' => $data['email'],
        ];

        if ($esDocente) {
            $doc = $user->docente;

            $docData = $request->validate([
                'nombres' => ['required', 'string', 'max:255'],
                'apellidos' => ['required', 'string', 'max:255'],
                'dni' => ['required', 'string', 'max:20', 'unique:docentes,dni,' . ($doc?->id ?? 'NULL')],
                'telefono' => ['nullable', 'string', 'max:30'],
                'especialidad' => ['nullable', 'string', 'max:120'],
                'fecha_ingreso' => ['nullable', 'date'],
                'estado' => ['nullable', 'in:activo,inactivo'],
                'sexo' => ['nullable', 'in:M,F'],
                'fecha_nacimiento' => ['nullable', 'date'],
                'direccion' => ['nullable', 'string', 'max:255'],
                'ciudad' => ['nullable', 'string', 'max:150'],
                'grado_academico' => ['nullable', 'string', 'max:150'],
                'tipo_contrato' => ['nullable', 'in:Nombrado,Contratado,Suplente'],
            ]);

            $payloadUser['name'] = trim($docData['nombres'] . ' ' . $docData['apellidos']);
            $payloadUser['dni'] = $this->normalizarDni((string) $docData['dni']);
            $payloadUser['password'] = $payloadUser['dni'];

            Docente::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres' => $docData['nombres'],
                    'apellidos' => $docData['apellidos'],
                    'dni' => $payloadUser['dni'],
                    'telefono' => $docData['telefono'] ?? null,
                    'especialidad' => $docData['especialidad'] ?? null,
                    'fecha_ingreso' => $docData['fecha_ingreso'] ?? null,
                    'estado' => $docData['estado'] ?? 'activo',
                    'sexo' => $docData['sexo'] ?? null,
                    'fecha_nacimiento' => $docData['fecha_nacimiento'] ?? null,
                    'direccion' => $docData['direccion'] ?? null,
                    'ciudad' => $docData['ciudad'] ?? null,
                    'grado_academico' => $docData['grado_academico'] ?? null,
                    'tipo_contrato' => $docData['tipo_contrato'] ?? null,
                ]
            );
        } else {
            $secData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
            ]);

            $payloadUser['name'] = $secData['name'];
            $payloadUser['dni'] = $this->normalizarDni((string) ($data['dni'] ?? ''));
            if ($payloadUser['dni'] === '') {
                return redirect()
                    ->route('director.personal.index')
                    ->withErrors(['dni' => 'El DNI es obligatorio.']);
            }
            $payloadUser['password'] = $payloadUser['dni'];
        }

        if (($payloadUser['dni'] ?? '') === '') {
            return redirect()
                ->route('director.personal.index', ['tab' => $data['tab'] ?? ($esDocente ? 'docentes' : 'secretarias')])
                ->withErrors(['dni' => 'El DNI es obligatorio.']);
        }

        $user->update($payloadUser);

        $tab = $data['tab'] ?? ($esDocente ? 'docentes' : 'secretarias');
        $rol = $esDocente ? 'docente' : 'secretaria';

        return redirect()
            ->route('director.personal.index', ['tab' => $tab])
            ->with('status', "Datos de {$rol} actualizados correctamente.");
    }

    private function generarCarnetCodigoDocente(Docente $docente): string
    {
        return CarnetCode::generateDocenteCode($docente);
    }

    private function normalizarDni(string $dni): string
    {
        return preg_replace('/\D+/', '', trim($dni)) ?? '';
    }
}
