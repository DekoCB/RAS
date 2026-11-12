<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Estudiante;
use App\Models\Incidencia;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidenciaController extends Controller
{
    private function routePrefix(): string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
    }
    public function __construct()
    {
        $this->middleware('permission:incidencia.ver')->only(['index', 'show']);
        $this->middleware('permission:incidencia.crear')->only(['create', 'store']);
        $this->middleware('permission:incidencia.actualizar')->only(['edit', 'update']);
    }

    public function index(Request $request)
    {
        $q = $request->input('q');
        $tipo = $request->input('tipo');
        $gravedad = $request->input('gravedad');
        $estado = $request->input('estado');

        $query = Incidencia::with(['estudiante', 'reportadoPor'])
            ->orderByDesc('fecha_reporte');

        if ($q) {
            $query->whereHas('estudiante', function ($sub) use ($q) {
                $sub->where('dni', 'like', "%{$q}%")
                    ->orWhere('nombres', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%");
            });
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        if ($gravedad) {
            $query->where('gravedad', $gravedad);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $incidencias = $query->paginate(15)->withQueryString();

        $tipos = ['Disciplinaria', 'Conducta', 'Academica', 'Convivencia', 'Otro'];
        $gravedades = ['Leve', 'Moderada', 'Grave', 'Critica'];
        $estados = ['abierta', 'cerrada'];

        $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
        $estudiantes = Estudiante::orderBy('apellidos')->orderBy('nombres')->get();
        $anioActivoId = $anios->first()?->id;

        return view('secretaria.incidencias.index', compact(
            'incidencias',
            'anios',
            'estudiantes',
            'anioActivoId',
            'tipos',
            'gravedades',
            'estados'
        ));
    }

    public function create()
    {
        $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
        $estudiantes = Estudiante::orderBy('apellidos')->orderBy('nombres')->get();

        return view('secretaria.incidencias.create', compact('anios', 'estudiantes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'fecha_reporte' => ['required', 'date'],
            'tipo' => ['required', 'string', 'max:50'],
            'gravedad' => ['required', 'string', 'max:30'],
            'descripcion' => ['required', 'string'],
        ]);

        $data['reportado_por'] = $request->user()->id;
        $data['estado'] = 'abierta';

        $incidencia = Incidencia::create($data);

        AuditoriaService::log($request->user()?->id, 'crear', 'incidencias', $incidencia->id, $data, $request);

        return redirect()->route($this->routePrefix() . 'incidencias.index')->with('status', 'Incidencia registrada.');
    }

    public function show(Incidencia $incidencia)
    {
        return view('secretaria.incidencias.show', compact('incidencia'));
    }

    public function edit(Incidencia $incidencia)
    {
        return view('secretaria.incidencias.edit', compact('incidencia'));
    }

    public function update(Request $request, Incidencia $incidencia)
    {
        $data = $request->validate([
            'estado' => ['required', 'string', 'max:30'],
            'seguimiento' => ['nullable', 'string'],
        ]);

        $incidencia->update($data);

        AuditoriaService::log($request->user()?->id, 'editar', 'incidencias', $incidencia->id, $data, $request);

        return redirect()->route($this->routePrefix() . 'incidencias.index')->with('status', 'Incidencia actualizada.');
    }

    public function destroy(Request $request, Incidencia $incidencia)
    {
        $incidencia->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar', 'incidencias', $incidencia->id, null, $request);

        return redirect()->route($this->routePrefix() . 'incidencias.index')->with('status', 'Incidencia eliminada.');
    }
}
