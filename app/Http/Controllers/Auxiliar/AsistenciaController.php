<?php

namespace App\Http\Controllers\Auxiliar;

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
        $this->middleware('permission:asistencia.ver')->only(['index', 'historial']);
        $this->middleware('permission:asistencia.registrar')->only(['create', 'store', 'qr', 'scan']);
    }

    public function index()
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();
        $periodoActual = $anio ? PeriodoAcademico::actualParaAnio($anio->id) : null;

        // El auxiliar ve TODAS las secciones
        $seccionesAsignadas = Seccion::with(['grado', 'aula'])
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        return view('auxiliar.asistencias.index', compact('seccionesAsignadas', 'anio', 'periodoActual'));
    }

    public function create(Request $request)
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        // El auxiliar ve TODAS las secciones
        $secciones = Seccion::with(['grado', 'aula'])
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        $seccionId = $request->input('seccion_id');
        $estudiantes = collect();
        $seccion = null;
        $mensajeFecha = null;
        $fechaSeleccionada = $request->input('fecha', date('Y-m-d'));
        $periodoAcademico = null;

        if ($seccionId) {
            $seccion = Seccion::with('grado')->find($seccionId);

            if ($seccion) {
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
                        $mensajeFecha = 'La asistencia de hoy ya fue registrada. Se cargó la siguiente fecha disponible.';
                    }
                }

                $periodoAcademico = $anio
                    ? PeriodoAcademico::actualParaAnio($anio->id, $fechaSeleccionada)
                    : null;

                $estudiantes = Estudiante::whereHas('matriculas', function ($query) use ($seccionId, $anio, $periodoAcademico) {
                    $query->where('seccion_id', $seccionId);
                    if ($anio) $query->where('anio_escolar_id', $anio->id);
                    if ($periodoAcademico) $query->where('periodo_academico_id', $periodoAcademico->id);
                })->orderBy('apellidos')->get();
            }
        }

        return view('auxiliar.asistencias.create', compact(
            'seccion', 'secciones', 'estudiantes', 'anio',
            'fechaSeleccionada', 'mensajeFecha', 'periodoAcademico'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'                  => ['required', 'exists:anios_escolares,id'],
            'seccion_id'                        => ['required', 'exists:secciones,id'],
            'fecha'                             => ['required', 'date'],
            'asistencias'                       => ['required', 'array'],
            'asistencias.*.estudiante_id'       => ['required', 'exists:estudiantes,id'],
            'asistencias.*.estado'              => ['required', 'string', 'max:20'],
        ]);

        $yaRegistrado = Asistencia::where('seccion_id', $data['seccion_id'])
            ->whereDate('fecha', $data['fecha'])
            ->exists();

        if ($yaRegistrado) {
            return back()->withInput()
                ->withErrors(['fecha' => 'La asistencia de esta sección ya fue registrada para esta fecha.']);
        }

        DB::transaction(function () use ($data, $request) {
            foreach ($data['asistencias'] as $item) {
                Asistencia::create([
                    'anio_escolar_id' => $data['anio_escolar_id'],
                    'seccion_id'      => $data['seccion_id'],
                    'fecha'           => $data['fecha'],
                    'estudiante_id'   => $item['estudiante_id'],
                    'estado'          => $item['estado'],
                    'registrado_por'  => $request->user()->id,
                ]);
            }
        });

        return redirect()->route('auxiliar.asistencias.index')->with('status', 'Asistencia registrada correctamente.');
    }

    public function historial(Request $request)
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        // El auxiliar ve TODAS las secciones
        $seccionesAsignadas = Seccion::with(['grado', 'aula'])
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        $seccionId = $request->input('seccion_id');
        $fecha     = $request->input('fecha');
        $seccionIds = $seccionesAsignadas->pluck('id');

        $asistenciasQuery = Asistencia::with(['estudiante', 'seccion.grado'])
            ->when($anio, fn($q) => $q->where('anio_escolar_id', $anio->id))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId));

        $fechasDisponibles = Asistencia::query()
            ->when($anio, fn($q) => $q->where('anio_escolar_id', $anio->id))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId))
            ->select('fecha')->distinct()->orderByDesc('fecha')->pluck('fecha');

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

        return view('auxiliar.asistencias.historial', compact(
            'asistencias', 'seccionesAsignadas', 'seccionId', 'fecha', 'fechasDisponibles', 'anio'
        ));
    }

    public function qr()
    {
        return view('auxiliar.asistencias.qr');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'string', 'max:255'],
        ]);

        $codigo = CarnetCode::normalize($request->codigo);

        $anio = AnioEscolar::where('activo', true)->first();
        if (!$anio) {
            return response()->json(['ok' => false, 'message' => 'No hay año escolar activo.'], 422);
        }

        $estudiante = CarnetCode::findEstudiante($codigo);
        if (!$estudiante) {
            return response()->json(['ok' => false, 'message' => 'Carnet no encontrado: ' . $codigo], 404);
        }

        $matricula = Matricula::where('anio_escolar_id', $anio->id)
            ->where('estudiante_id', $estudiante->id)
            ->orderByDesc('id')->first();

        $seccionId = $matricula?->seccion_id;
        if (!$seccionId) {
            return response()->json(['ok' => false, 'message' => 'El estudiante no tiene matrícula activa.'], 422);
        }

        $hoy = Carbon::today()->toDateString();

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

        Asistencia::create([
            'anio_escolar_id' => $anio->id,
            'seccion_id'      => $seccionId,
            'estudiante_id'   => $estudiante->id,
            'fecha'           => $hoy,
            'estado'          => 'PRESENTE',
            'registrado_por'  => Auth::id(),
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Asistencia registrada',
            'alumno'  => $estudiante->apellidos . ' ' . $estudiante->nombres,
            'codigo'  => $codigo,
        ]);
    }
}
