<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\Incidencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $anioActivo = AnioEscolar::where('activo', true)->first();
        $anioId = $anioActivo?->id;
        $periodoActivo = PeriodoAcademico::actualParaAnio($anioId);

        $seccionesAsignadas = Seccion::query()
            ->whereHas('cursos', function ($q) use ($user, $anioId, $periodoActivo) {
                $q->where('seccion_curso.docente_id', $user->id);
                if ($anioId) {
                    $q->where('seccion_curso.anio_escolar_id', $anioId);
                }
                if ($periodoActivo) {
                    $q->where('seccion_curso.periodo_academico_id', $periodoActivo->id);
                }
            })
            ->get();

        $seccionesIds = $seccionesAsignadas->pluck('id');

        $estudiantesAsignados = Matricula::query()
            ->when($anioId, fn($q) => $q->where('anio_escolar_id', $anioId))
            ->when($periodoActivo, fn($q) => $q->where('periodo_academico_id', $periodoActivo->id))
            ->when($seccionesIds->isNotEmpty(), fn($q) => $q->whereIn('seccion_id', $seccionesIds))
            ->count();

        $cursosAsignados = Curso::query()
            ->whereHas('secciones', function ($q) use ($user, $anioId, $periodoActivo) {
                $q->where('seccion_curso.docente_id', $user->id);
                if ($anioId) {
                    $q->where('seccion_curso.anio_escolar_id', $anioId);
                }
                if ($periodoActivo) {
                    $q->where('seccion_curso.periodo_academico_id', $periodoActivo->id);
                }
            })
            ->count();

        $asistenciasRegistradas = Asistencia::where('registrado_por', $user->id)
            ->with('estudiante')
            ->latest('fecha')
            ->take(10)
            ->get();

        $notasRegistradas = Nota::where('docente_id', $user->id)
            ->when($periodoActivo, fn($q) => $q->where('periodo_academico_id', $periodoActivo->id))
            ->with(['estudiante', 'curso'])
            ->latest()
            ->take(10)
            ->get();

        $incidenciasReportadas = Incidencia::where('reportado_por', $user->id)
            ->with('estudiante')
            ->latest('fecha_reporte')
            ->take(10)
            ->get();

        $resumen = [
            'asistencias' => Asistencia::where('registrado_por', $user->id)->count(),
            'asistencias_hoy' => Asistencia::where('registrado_por', $user->id)
                ->whereDate('fecha', Carbon::today())
                ->count(),
            'notas' => Nota::where('docente_id', $user->id)->count(),
            'incidencias' => Incidencia::where('reportado_por', $user->id)->count(),
            'secciones' => $seccionesIds->count(),
            'estudiantes' => $estudiantesAsignados,
            'cursos' => $cursosAsignados,
        ];

        return view('dashboards.docente', compact(
            'asistenciasRegistradas',
            'notasRegistradas',
            'incidenciasReportadas',
            'resumen',
            'anioActivo'
        ));
    }
}
