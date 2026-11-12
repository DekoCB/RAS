<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\NotaCierre;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Models\NotaConcepto;
use App\Models\NotaConceptoDetalle;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:nota.ver')->only(['index', 'show']);
        $this->middleware('permission:nota.registrar')->only(['create', 'store']);
        $this->middleware('permission:nota.editar')->only(['edit', 'update']);
    }

    public function index(Request $request)
    {
        $anioActivo = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
        $anioId = $request->input('anio_escolar_id', $anioActivo?->id);
        $seccionId = $request->input('seccion_id');
        $cursoId = $request->input('curso_id');
        $periodoAcademicoId = $request->input('periodo_academico_id');
        $periodo = $request->input('periodo');
        $buscar = trim((string) $request->input('buscar', ''));

        $periodos = [
            'Mensual 1',
            'Bimestre 1',
            'Mensual 2',
            'Bimestre 2',
        ];

        $periodosAcademicos = $anioId
            ? PeriodoAcademico::where('anio_escolar_id', $anioId)->orderBy('orden')->get()
            : collect();

        $periodoAcademicoSeleccionado = $periodosAcademicos->firstWhere('id', (int) $periodoAcademicoId)
            ?? PeriodoAcademico::actualParaAnio((int) $anioId);

        $periodoAcademicoId = $periodoAcademicoSeleccionado?->id;

        // ✅ SECCIONES asignadas según seccion_curso.docente_id = Auth::id()
        $secciones = Seccion::with(['grado', 'aula'])
            ->whereHas('cursos', function ($query) use ($anioId, $periodoAcademicoId) {
                $query->where('seccion_curso.docente_id', Auth::id());
                if ($anioId) {
                    $query->where('seccion_curso.anio_escolar_id', $anioId);
                }
                if ($periodoAcademicoId) {
                    $query->where('seccion_curso.periodo_academico_id', $periodoAcademicoId);
                }
            })
            ->distinct()
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        // ✅ CURSOS asignados según seccion_curso.docente_id = Auth::id()
        $cursos = Curso::whereHas('secciones', function ($query) use ($anioId, $seccionId, $periodoAcademicoId) {
            if ($seccionId) {
                $query->where('secciones.id', $seccionId);
            }
            $query->where('seccion_curso.docente_id', Auth::id());
            if ($anioId) {
                $query->where('seccion_curso.anio_escolar_id', $anioId);
            }
            if ($periodoAcademicoId) {
                $query->where('seccion_curso.periodo_academico_id', $periodoAcademicoId);
            }
        })->orderBy('nombre')->get();

        // ✅ NOTAS del docente según notas.docente_id = Auth::id()
        $notasQuery = Nota::with(['estudiante', 'curso', 'seccion'])
            ->where('docente_id', Auth::id());

        if ($anioId) {
            $notasQuery->where('anio_escolar_id', $anioId);
        }
        if ($periodoAcademicoId) {
            $notasQuery->where('periodo_academico_id', $periodoAcademicoId);
        }
        if ($seccionId) {
            $notasQuery->where('seccion_id', $seccionId);
        }
        if ($cursoId) {
            $notasQuery->where('curso_id', $cursoId);
        }
        if ($periodo) {
            $notasQuery->where('periodo', $periodo);
        }
        if ($buscar !== '') {
            $notasQuery->whereHas('estudiante', function ($query) use ($buscar) {
                $query->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('dni', 'like', "%{$buscar}%");
            });
        }

        $notas = $notasQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('docente.notas.index', compact(
            'notas',
            'anios',
            'anioId',
            'secciones',
            'seccionId',
            'cursos',
            'cursoId',
            'periodosAcademicos',
            'periodoAcademicoId',
            'periodos',
            'periodo',
            'buscar'
        ));
    }

    public function create(Request $request)
    {
        $anioActivo = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
        $anioId = $request->input('anio_escolar_id', $anioActivo?->id);
        $seccionId = $request->input('seccion_id');
        $cursoId = $request->input('curso_id');
        $periodoAcademicoId = $request->input('periodo_academico_id');
        $periodo = $request->input('periodo');

        $periodos = [
            'Mensual 1',
            'Bimestre 1',
            'Mensual 2',
            'Bimestre 2',
        ];

        $periodosAcademicos = $anioId
            ? PeriodoAcademico::where('anio_escolar_id', $anioId)->orderBy('orden')->get()
            : collect();

        $periodoAcademicoSeleccionado = $periodosAcademicos->firstWhere('id', (int) $periodoAcademicoId)
            ?? PeriodoAcademico::actualParaAnio((int) $anioId);

        $periodoAcademicoId = $periodoAcademicoSeleccionado?->id;

        $secciones = Seccion::with(['grado', 'aula'])
            ->whereHas('cursos', function ($query) use ($anioId, $periodoAcademicoId) {
                $query->where('seccion_curso.docente_id', Auth::id());
                if ($anioId) {
                    $query->where('seccion_curso.anio_escolar_id', $anioId);
                }
                if ($periodoAcademicoId) {
                    $query->where('seccion_curso.periodo_academico_id', $periodoAcademicoId);
                }
            })
            ->distinct()
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        $cursos = Curso::whereHas('secciones', function ($query) use ($anioId, $seccionId, $periodoAcademicoId) {
            if ($seccionId) {
                $query->where('secciones.id', $seccionId);
            }
            $query->where('seccion_curso.docente_id', Auth::id());
            if ($anioId) {
                $query->where('seccion_curso.anio_escolar_id', $anioId);
            }
            if ($periodoAcademicoId) {
                $query->where('seccion_curso.periodo_academico_id', $periodoAcademicoId);
            }
        })->orderBy('nombre')->get();

        $estudiantes = collect();
        $notasExistentes = collect();
        $promedio = null;
        $aprobados = 0;
        $bloqueado = false;

        if ($anioId && $periodoAcademicoId && $seccionId && $cursoId && $periodo) {
            $estudiantes = Estudiante::whereHas('matriculas', function ($query) use ($anioId, $periodoAcademicoId, $seccionId) {
                $query->where('anio_escolar_id', $anioId)
                    ->where('periodo_academico_id', $periodoAcademicoId)
                    ->where('seccion_id', $seccionId);
            })->orderBy('apellidos')->orderBy('nombres')->get();

            $notasExistentes = Nota::where('anio_escolar_id', $anioId)
                ->where('periodo_academico_id', $periodoAcademicoId)
                ->where('seccion_id', $seccionId)
                ->where('curso_id', $cursoId)
                ->where('periodo', $periodo)
                ->where('docente_id', Auth::id())
                ->get()
                ->keyBy('estudiante_id');

            $valores = $notasExistentes->pluck('valor')->filter(fn($valor) => $valor !== null);
            $promedio = $valores->count() ? round($valores->avg(), 2) : null;
            $aprobados = $valores->filter(fn($valor) => $valor >= 11)->count();

            $bloqueado = NotaCierre::where('anio_escolar_id', $anioId)
                ->where('periodo_academico_id', $periodoAcademicoId)
                ->where('periodo', $periodo)
                ->where(function ($query) use ($cursoId) {
                    $query->whereNull('curso_id')
                        ->orWhere('curso_id', $cursoId);
                })->exists();
        }

        return view('docente.notas.create', compact(
            'anios',
            'anioId',
            'secciones',
            'seccionId',
            'cursos',
            'cursoId',
            'periodosAcademicos',
            'periodoAcademicoId',
            'periodos',
            'periodo',
            'estudiantes',
            'notasExistentes',
            'promedio',
            'aprobados',
            'bloqueado'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id' => ['required', 'exists:periodos_academicos,id'],
            'seccion_id' => ['required', 'exists:secciones,id'],
            'curso_id' => ['required', 'exists:cursos,id'],
            'periodo' => ['required', 'string', 'max:50'],
            'estudiantes' => ['required', 'array'],
            'estudiantes.*' => ['required', 'exists:estudiantes,id'],
            'notas' => ['nullable', 'array'],
            'notas.*' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ]);

        $tieneAcceso = Seccion::where('id', $data['seccion_id'])
            ->whereHas('cursos', function ($query) use ($data) {
                $query->where('cursos.id', $data['curso_id'])
                    ->where('seccion_curso.docente_id', Auth::id());
                $query->where('seccion_curso.anio_escolar_id', $data['anio_escolar_id']);
                $query->where('seccion_curso.periodo_academico_id', $data['periodo_academico_id']);
            })->exists();

        if (!$tieneAcceso) {
            return back()
                ->withInput()
                ->withErrors(['seccion_id' => 'No tienes permiso para registrar notas en esta seccion o curso.']);
        }

        $bloqueado = NotaCierre::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('periodo', $data['periodo'])
            ->where(function ($query) use ($data) {
                $query->whereNull('curso_id')
                    ->orWhere('curso_id', $data['curso_id']);
            })->exists();

        if ($bloqueado) {
            return back()
                ->withInput()
                ->withErrors(['periodo' => 'La evaluacion seleccionada esta cerrada.']);
        }

        $estudiantesIds = Matricula::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('seccion_id', $data['seccion_id'])
            ->whereIn('estudiante_id', $data['estudiantes'])
            ->pluck('estudiante_id')
            ->all();

        foreach ($estudiantesIds as $estudianteId) {
            $valor = $request->input("notas.$estudianteId");
            if ($valor === null || $valor === '') {
                continue;
            }

            // ✅ FIX: docente_id también forma parte de la "clave" para evitar inconsistencias
            Nota::updateOrCreate(
                [
                    'anio_escolar_id' => $data['anio_escolar_id'],
                    'periodo_academico_id' => $data['periodo_academico_id'],
                    'seccion_id' => $data['seccion_id'],
                    'curso_id' => $data['curso_id'],
                    'estudiante_id' => $estudianteId,
                    'periodo' => $data['periodo'],
                    'docente_id' => Auth::id(),
                ],
                [
                    'valor' => $valor,
                    'estado' => 'abierta',
                    'fecha_cierre' => null,
                ]
            );
        }

        AuditoriaService::log($request->user()?->id, 'registrar', 'notas', 0, $data, $request);

        return redirect()->route('docente.notas.create', [
            'anio_escolar_id' => $data['anio_escolar_id'],
            'periodo_academico_id' => $data['periodo_academico_id'],
            'seccion_id' => $data['seccion_id'],
            'curso_id' => $data['curso_id'],
            'periodo' => $data['periodo'],
        ])->with('status', 'Notas guardadas.');
    }

    public function show(Nota $nota)
    {
        return view('docente.notas.show', compact('nota'));
    }

    public function edit(Nota $nota)
    {
        $this->authorize('update', $nota);

        $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
        $cursos = Curso::orderBy('nombre')->get();
        $estudiantes = Estudiante::orderBy('apellidos')->orderBy('nombres')->get();

        return view('docente.notas.edit', compact('nota', 'anios', 'cursos', 'estudiantes'));
    }

    public function update(Request $request, Nota $nota)
    {
        $this->authorize('update', $nota);

        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'curso_id' => ['required', 'exists:cursos,id'],
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'periodo' => ['required', 'string', 'max:50'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'string', 'max:30'],
        ]);

        $nota->update($data);

        AuditoriaService::log($request->user()?->id, 'editar', 'notas', $nota->id, $data, $request);

        return redirect()->route('docente.notas.index')->with('status', 'Nota actualizada.');
    }

    public function destroy(Request $request, Nota $nota)
    {
        $nota->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar', 'notas', $nota->id, null, $request);

        return redirect()->route('docente.notas.index')->with('status', 'Nota eliminada.');
    }

    // =========================
    //  CONCEPTOS (FastTest, etc)
    // =========================

    public function getConceptos(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => 'required|integer',
            'periodo_academico_id' => 'required|integer',
            'curso_id' => 'required|integer',
            'seccion_id' => 'required|integer',
            'periodo' => 'required|string|max:50',
        ]);

        $conceptos = NotaConcepto::query()
            ->where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('curso_id', $data['curso_id'])
            ->where('seccion_id', $data['seccion_id'])
            ->where('docente_id', Auth::id())
            ->where('periodo', $data['periodo'])
            ->orderBy('id')
            ->get();

        return response()->json($conceptos);
    }

    public function saveConceptos(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => 'required|integer',
            'periodo_academico_id' => 'required|integer',
            'curso_id' => 'required|integer',
            'seccion_id' => 'required|integer',
            'periodo' => 'required|string|max:50',
            'conceptos' => 'required|array', // puede ser vacío si quieres
            'conceptos.*.nombre' => 'required|string|max:80',
            'conceptos.*.porcentaje' => 'required|numeric|min:0|max:100',
        ]);

        // Bloqueo por cierre
        $bloqueado = NotaCierre::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('periodo', $data['periodo'])
            ->where(function ($query) use ($data) {
                $query->whereNull('curso_id')->orWhere('curso_id', $data['curso_id']);
            })->exists();

        if ($bloqueado) {
            return response()->json(['message' => 'La evaluación seleccionada está cerrada.'], 422);
        }

        // Filtrar por si mandan "Examen" desde el frontend (no lo permitimos duplicado)
        $conceptosUsuario = collect($data['conceptos'])
            ->reject(fn($c) => mb_strtolower(trim($c['nombre'])) === 'examen')
            ->values();

        $totalConceptos = round($conceptosUsuario->sum('porcentaje'), 2);

        if ($totalConceptos > 100.00) {
            return response()->json([
                'message' => "La suma de conceptos no puede superar 100. Actualmente suman: {$totalConceptos}"
            ], 422);
        }

        $porcentajeExamen = round(100.00 - $totalConceptos, 2);

        // Borramos configuración anterior (para este contexto)
        NotaConcepto::query()
            ->where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('curso_id', $data['curso_id'])
            ->where('seccion_id', $data['seccion_id'])
            ->where('docente_id', Auth::id())
            ->where('periodo', $data['periodo'])
            ->delete();

        // 1) Guardar EXAMEN (siempre)
        NotaConcepto::create([
            'anio_escolar_id' => $data['anio_escolar_id'],
            'periodo_academico_id' => $data['periodo_academico_id'],
            'curso_id' => $data['curso_id'],
            'seccion_id' => $data['seccion_id'],
            'docente_id' => Auth::id(),
            'periodo' => $data['periodo'],
            'nombre' => 'Examen',
            'porcentaje' => $porcentajeExamen,
        ]);

        // 2) Guardar conceptos del usuario
        foreach ($conceptosUsuario as $c) {
            NotaConcepto::create([
                'anio_escolar_id' => $data['anio_escolar_id'],
                'periodo_academico_id' => $data['periodo_academico_id'],
                'curso_id' => $data['curso_id'],
                'seccion_id' => $data['seccion_id'],
                'docente_id' => Auth::id(),
                'periodo' => $data['periodo'],
                'nombre' => $c['nombre'],
                'porcentaje' => $c['porcentaje'],
            ]);
        }

        return response()->json([
            'message' => 'Conceptos guardados',
            'examen' => $porcentajeExamen
        ]);
    }

    public function saveDetalles(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => 'required|integer',
            'periodo_academico_id' => 'required|integer',
            'curso_id' => 'required|integer',
            'seccion_id' => 'required|integer',
            'periodo' => 'required|string|max:50',
            'notas' => 'required|array',
        ]);

        $bloqueado = NotaCierre::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('periodo', $data['periodo'])
            ->where(function ($query) use ($data) {
                $query->whereNull('curso_id')->orWhere('curso_id', $data['curso_id']);
            })->exists();

        if ($bloqueado) {
            return response()->json(['message' => 'La evaluación seleccionada está cerrada.'], 422);
        }

        $conceptos = NotaConcepto::query()
            ->where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('curso_id', $data['curso_id'])
            ->where('seccion_id', $data['seccion_id'])
            ->where('docente_id', Auth::id())
            ->where('periodo', $data['periodo'])
            ->get();

        if ($conceptos->isEmpty()) {
            return response()->json(['message' => 'Primero configura conceptos para esta evaluación.'], 422);
        }

        foreach ($data['notas'] as $estudianteId => $porConcepto) {
            foreach ($porConcepto as $conceptoId => $notaValor) {
                $n = ($notaValor === '' || $notaValor === null) ? null : (float) $notaValor;
                if ($n !== null && ($n < 0 || $n > 20)) {
                    return response()->json(['message' => "Nota inválida (0-20) para estudiante {$estudianteId}"], 422);
                }

                NotaConceptoDetalle::updateOrCreate(
                    [
                        'nota_concepto_id' => $conceptoId,
                        'estudiante_id' => $estudianteId,
                    ],
                    [
                        'nota' => $n,
                        'docente_id' => Auth::id(),
                    ]
                );
            }

            $final = 0.0;
            foreach ($conceptos as $c) {
                $detalle = NotaConceptoDetalle::query()
                    ->where('nota_concepto_id', $c->id)
                    ->where('estudiante_id', $estudianteId)
                    ->first();

                $notaC = $detalle?->nota ?? 0;
                $final += ($notaC * (float) $c->porcentaje / 100.0);
            }

            Nota::updateOrCreate(
                [
                    'anio_escolar_id' => $data['anio_escolar_id'],
                    'periodo_academico_id' => $data['periodo_academico_id'],
                    'seccion_id' => $data['seccion_id'],
                    'curso_id' => $data['curso_id'],
                    'estudiante_id' => $estudianteId,
                    'periodo' => $data['periodo'],
                    // ❌ NO pongas docente_id aquí si NO está en el UNIQUE
                ],
                [
                    'docente_id' => Auth::id(),        // ✅ lo pones en update
                    'valor' => round($final, 2),
                    'estado' => 'abierta',
                    'fecha_cierre' => null,
                ]
            );
        }

        return response()->json(['message' => 'Notas guardadas correctamente']);
    }
}
