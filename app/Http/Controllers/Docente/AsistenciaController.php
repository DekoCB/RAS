<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Models\Matricula;
use App\Support\CarnetCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:asistencia.ver')->only(['index']);
        $this->middleware('permission:asistencia.registrar')->only(['create', 'store']);
        $this->middleware('permission:asistencia.registrar')->only(['qr', 'scan']);
    }

    public function index()
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();
        $periodoActual = $anio ? PeriodoAcademico::actualParaAnio($anio->id) : null;

        // Obtener las secciones donde el docente dicta cursos (evitando duplicados)
        $seccionesAsignadas = Seccion::with(['grado', 'aula'])
            ->whereHas('cursos', function ($query) {
                $query->where('docente_id', Auth::id());
            })
            ->distinct()
            ->get();

        return view('docente.asistencias.index', compact('seccionesAsignadas', 'anio', 'periodoActual'));
    }

    public function create(Request $request)
    {
        $anio = AnioEscolar::where('activo', true)->first();
        if (!$anio) {
            $anio = AnioEscolar::orderByDesc('fecha_inicio')->first();
        }

        $secciones = Seccion::with(['grado', 'aula'])
            ->whereHas('cursos', function ($query) {
                $query->where('docente_id', Auth::id());
            })
            ->distinct()
            ->orderBy('nombre')
            ->orderBy('grado_id')
            ->get();

        $seccionId = $request->input('seccion_id');
        $estudiantes = collect();
        $seccion = null;
        $mensajeFecha = null;
        $fechaSeleccionada = $request->input('fecha', date('Y-m-d'));
        $periodoAcademico = null;

        if ($seccionId) {
            $seccion = Seccion::with('grado')->find($seccionId);

            $tieneAcceso = Seccion::where('id', $seccionId)
                ->whereHas('cursos', function ($query) {
                    $query->where('docente_id', Auth::id());
                })->exists();

            if ($tieneAcceso) {
                if (!$request->has('fecha')) {
                    $fechaCursor = $fechaSeleccionada;

                    while (Asistencia::where('seccion_id', $seccionId)
                        ->whereDate('fecha', $fechaCursor)
                        ->exists()
                    ) {
                        $fechaCursor = date('Y-m-d', strtotime($fechaCursor . ' +1 day'));
                    }

                    if ($fechaCursor !== $fechaSeleccionada) {
                        $fechaSeleccionada = $fechaCursor;
                        $mensajeFecha = 'La asistencia de hoy ya fue registrada. Se cargo la siguiente fecha disponible.';
                    }
                }

                $periodoAcademico = $anio
                    ? PeriodoAcademico::actualParaAnio($anio->id, $fechaSeleccionada)
                    : null;

                $estudiantes = Estudiante::whereHas('matriculas', function ($query) use ($seccionId, $anio, $periodoAcademico) {
                    $query->where('seccion_id', $seccionId);
                    if ($anio) {
                        $query->where('anio_escolar_id', $anio->id);
                    }
                    if ($periodoAcademico) {
                        $query->where('periodo_academico_id', $periodoAcademico->id);
                    }
                })->orderBy('apellidos')->get();
            } else {
                return redirect()->route('docente.asistencias.index')
                    ->with('error', 'No tienes permiso para registrar asistencia en esta seccion.');
            }
        }

        return view('docente.asistencias.create', compact(
            'seccion',
            'secciones',
            'estudiantes',
            'anio',
            'fechaSeleccionada',
            'mensajeFecha',
            'periodoAcademico'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'seccion_id' => ['required', 'exists:secciones,id'],
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array'],
            'asistencias.*.estudiante_id' => ['required', 'exists:estudiantes,id'],
            'asistencias.*.estado' => ['required', 'string', 'max:20'],
        ]);

        $yaRegistrado = Asistencia::where('seccion_id', $data['seccion_id'])
            ->whereDate('fecha', $data['fecha'])
            ->exists();

        if ($yaRegistrado) {
            return back()
                ->withInput()
                ->withErrors(['fecha' => 'La asistencia de esta seccion ya fue registrada para esta fecha.']);
        }

        DB::transaction(function () use ($data, $request) {
            foreach ($data['asistencias'] as $item) {
                Asistencia::create([
                    'anio_escolar_id' => $data['anio_escolar_id'],
                    'seccion_id' => $data['seccion_id'],
                    'fecha' => $data['fecha'],
                    'estudiante_id' => $item['estudiante_id'],
                    'estado' => $item['estado'],
                    'registrado_por' => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('docente.asistencias.index')->with('status', 'Asistencia registrada.');
    }

    public function historial(Request $request)
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        $seccionesAsignadas = Seccion::with(['grado', 'aula'])
            ->whereHas('cursos', function ($query) {
                $query->where('docente_id', Auth::id());
            })
            ->distinct()
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        $seccionId = $request->input('seccion_id');
        $fecha = $request->input('fecha');

        $seccionIds = $seccionesAsignadas->pluck('id');

        $asistenciasQuery = Asistencia::with(['estudiante', 'seccion.grado'])
            ->when($anio, function ($query) use ($anio) {
                $query->where('anio_escolar_id', $anio->id);
            });

        if ($seccionIds->isEmpty()) {
            $asistenciasQuery->whereRaw('1 = 0');
        } else {
            $asistenciasQuery->whereIn('seccion_id', $seccionIds);
        }

        $fechasDisponiblesQuery = Asistencia::query()
            ->when($anio, function ($query) use ($anio) {
                $query->where('anio_escolar_id', $anio->id);
            });

        if ($seccionIds->isEmpty()) {
            $fechasDisponiblesQuery->whereRaw('1 = 0');
        } else {
            $fechasDisponiblesQuery->whereIn('seccion_id', $seccionIds);
        }

        if ($seccionId) {
            $asistenciasQuery->where('seccion_id', $seccionId);
            $fechasDisponiblesQuery->where('seccion_id', $seccionId);
        }

        $fechasDisponibles = $fechasDisponiblesQuery
            ->select('fecha')
            ->distinct()
            ->orderByDesc('fecha')
            ->pluck('fecha');

        if ($fecha) {
            $asistenciasQuery->whereDate('fecha', $fecha);
        } else {
            $asistenciasQuery->whereRaw('1 = 0');
        }

        $asistencias = $asistenciasQuery
            ->orderByDesc('fecha')
            ->orderBy('estudiante_id')
            ->paginate(15)
            ->withQueryString();

        return view('docente.asistencias.historial', compact(
            'asistencias',
            'seccionesAsignadas',
            'seccionId',
            'fecha',
            'fechasDisponibles',
            'anio'
        ));
    }

    public function show(Asistencia $asistencia)
    {
        return view('docente.asistencias.show', compact('asistencia'));
    }

    public function edit(Asistencia $asistencia)
    {
        return view('docente.asistencias.edit', compact('asistencia'));
    }

    public function update(Request $request, Asistencia $asistencia)
    {
        $data = $request->validate([
            'estado' => ['required', 'string', 'max:20'],
        ]);

        $asistencia->update($data);

        return redirect()->route('docente.asistencias.index')->with('status', 'Asistencia actualizada.');
    }

    public function destroy(Request $request, Asistencia $asistencia)
    {
        $asistencia->delete();

        return redirect()->route('docente.asistencias.index')->with('status', 'Asistencia eliminada.');
    }

    // ✅ Vista del lector QR
    public function qr()
    {
        return view('docente.asistencias.qr');
    }

    // ✅ Endpoint para registrar asistencia por QR
    public function scan(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'string', 'max:255'],
        ]);

        $codigo = CarnetCode::normalize($request->codigo);

        // 1) Año escolar activo
        $anio = AnioEscolar::where('activo', true)->first();
        if (!$anio) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay año escolar activo.',
            ], 422);
        }

        // 2) Buscar estudiante por carnet_codigo
        $estudiante = CarnetCode::findEstudiante($codigo);
        if (!$estudiante) {
            return response()->json([
                'ok' => false,
                'message' => 'Carnet no encontrado: ' . $codigo,
            ], 404);
        }

        // 3) Obtener matrícula del año activo y su sección
        $matricula = Matricula::where('anio_escolar_id', $anio->id)
            ->where('estudiante_id', $estudiante->id)
            ->orderByDesc('id')
            ->first();

        $seccionId = $matricula?->seccion_id;

        if (!$seccionId) {
            return response()->json([
                'ok' => false,
                'message' => 'El estudiante no tiene matrícula activa en el año escolar.',
            ], 422);
        }

        // ✅ EXTRA IMPORTANTE: validar que el DOCENTE tenga acceso a esa sección
        $tieneAcceso = Seccion::where('id', $seccionId)
            ->whereHas('cursos', function ($query) {
                $query->where('docente_id', Auth::id());
            })->exists();

        if (!$tieneAcceso) {
            return response()->json([
                'ok' => false,
                'message' => 'No tienes permiso para registrar asistencia en esta sección.',
            ], 403);
        }

        // 4) Fecha de hoy
        $hoy = Carbon::today()->toDateString();

        // 5) Evitar duplicados (mismo estudiante + fecha + año + sección)
        $yaExiste = Asistencia::where('anio_escolar_id', $anio->id)
            ->where('seccion_id', $seccionId)
            ->where('estudiante_id', $estudiante->id)
            ->whereDate('fecha', $hoy)
            ->exists();

        if ($yaExiste) {
            return response()->json([
                'ok' => false,
                'message' => 'Ya registrado hoy.',
                'alumno' => $estudiante->apellidos . ' ' . $estudiante->nombres,
            ], 409);
        }

        // 6) Registrar asistencia (PRESENTE)
        Asistencia::create([
            'anio_escolar_id' => $anio->id,
            'seccion_id'      => $seccionId,
            'estudiante_id'   => $estudiante->id,
            'fecha'           => $hoy,
            'estado'          => 'PRESENTE',
            'registrado_por'  => Auth::id(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Asistencia registrada',
            'alumno' => $estudiante->apellidos . ' ' . $estudiante->nombres,
            'codigo' => $codigo,
        ]);
    }
}
