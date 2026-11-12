<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Incidencia;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:incidencia.ver')->only(['index', 'show']);
        $this->middleware('permission:incidencia.crear')->only(['create', 'store']);
        $this->middleware('permission:incidencia.actualizar')->only(['edit', 'update']);
    }

    public function index()
    {
        $incidencias = Incidencia::with(['estudiante', 'seccion.grado', 'curso'])
            ->orderByDesc('fecha_reporte')
            ->paginate(15);

        return view('docente.incidencias.index', compact('incidencias'));
    }

    public function create(Request $request)
    {
        $anioActivo = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();
        $anioId = $anioActivo?->id;
        $periodoActivo = PeriodoAcademico::actualParaAnio($anioId);

        $seccionId = $request->input('seccion_id');
        $cursoId = $request->input('curso_id');

        $secciones = Seccion::with(['grado', 'aula'])
            ->whereHas('cursos', function ($query) use ($anioId, $periodoActivo) {
                $query->where('seccion_curso.docente_id', Auth::id());
                if ($anioId) {
                    $query->where('seccion_curso.anio_escolar_id', $anioId);
                }
                if ($periodoActivo) {
                    $query->where('seccion_curso.periodo_academico_id', $periodoActivo->id);
                }
            })
            ->distinct()
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        $cursos = collect();
        if ($seccionId) {
            $cursos = Curso::whereHas('secciones', function ($query) use ($anioId, $periodoActivo, $seccionId) {
                $query->where('secciones.id', $seccionId)
                    ->where('seccion_curso.docente_id', Auth::id());
                if ($anioId) {
                    $query->where('seccion_curso.anio_escolar_id', $anioId);
                }
                if ($periodoActivo) {
                    $query->where('seccion_curso.periodo_academico_id', $periodoActivo->id);
                }
            })->orderBy('nombre')->get();
        }

        $estudiantes = collect();
        if ($seccionId) {
            $estudiantes = Estudiante::whereHas('matriculas', function ($query) use ($anioId, $periodoActivo, $seccionId) {
                $query->where('seccion_id', $seccionId);
                if ($anioId) {
                    $query->where('anio_escolar_id', $anioId);
                }
                if ($periodoActivo) {
                    $query->where('periodo_academico_id', $periodoActivo->id);
                }
            })->orderBy('apellidos')->orderBy('nombres')->get();
        }

        return view('docente.incidencias.create', compact(
            'anioId',
            'periodoActivo',
            'secciones',
            'seccionId',
            'cursos',
            'cursoId',
            'estudiantes'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'seccion_id' => ['required', 'exists:secciones,id'],
            'curso_id' => ['required', 'exists:cursos,id'],
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'fecha_reporte' => ['required', 'date'],
            'tipo' => ['required', 'string', 'max:50'],
            'gravedad' => ['required', 'string', 'max:30'],
            'descripcion' => ['required', 'string'],
        ]);

        $periodoActivo = PeriodoAcademico::actualParaAnio($data['anio_escolar_id'], $data['fecha_reporte']);

        $tieneAcceso = Seccion::where('id', $data['seccion_id'])
            ->whereHas('cursos', function ($query) use ($data, $periodoActivo) {
                $query->where('cursos.id', $data['curso_id'])
                    ->where('seccion_curso.docente_id', Auth::id())
                    ->where('seccion_curso.anio_escolar_id', $data['anio_escolar_id']);
                if ($periodoActivo) {
                    $query->where('seccion_curso.periodo_academico_id', $periodoActivo->id);
                }
            })->exists();

        if (!$tieneAcceso) {
            return back()
                ->withInput()
                ->withErrors(['seccion_id' => 'No tienes permiso para registrar incidencias en esta seccion o curso.']);
        }

        $matriculado = Matricula::where('anio_escolar_id', $data['anio_escolar_id'])
            ->when($periodoActivo, function ($query) use ($periodoActivo) {
                $query->where('periodo_academico_id', $periodoActivo->id);
            })
            ->where('seccion_id', $data['seccion_id'])
            ->where('estudiante_id', $data['estudiante_id'])
            ->exists();

        if (!$matriculado) {
            return back()
                ->withInput()
                ->withErrors(['estudiante_id' => 'El estudiante no pertenece a la seccion seleccionada.']);
        }

        $data['reportado_por'] = $request->user()->id;
        $data['estado'] = 'abierta';

        $incidencia = Incidencia::create($data);

        AuditoriaService::log($request->user()?->id, 'crear', 'incidencias', $incidencia->id, $data, $request);

        return redirect()->route('docente.incidencias.index')->with('status', 'Incidencia registrada.');
    }

    public function show(Incidencia $incidencia)
    {
        return view('docente.incidencias.show', compact('incidencia'));
    }

    public function edit(Incidencia $incidencia)
    {
        return view('docente.incidencias.edit', compact('incidencia'));
    }

    public function update(Request $request, Incidencia $incidencia)
    {
        $data = $request->validate([
            'estado' => ['required', 'string', 'max:30'],
            'seguimiento' => ['nullable', 'string'],
        ]);

        $incidencia->update($data);

        AuditoriaService::log($request->user()?->id, 'editar', 'incidencias', $incidencia->id, $data, $request);

        return redirect()->route('docente.incidencias.index')->with('status', 'Incidencia actualizada.');
    }

    public function destroy(Request $request, Incidencia $incidencia)
    {
        $incidencia->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar', 'incidencias', $incidencia->id, null, $request);

        return redirect()->route('docente.incidencias.index')->with('status', 'Incidencia eliminada.');
    }
}
