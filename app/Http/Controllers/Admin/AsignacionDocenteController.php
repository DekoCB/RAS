<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AsignacionDocenteController extends Controller
{
    public function index(Request $request)
    {
        $anios = AnioEscolar::orderBy('nombre', 'desc')->get();

        $anioSeleccionado = $request->input('anio_escolar_id');
        $anio = $anios->firstWhere('id', (int) $anioSeleccionado)
            ?? AnioEscolar::where('activo', true)->first()
            ?? $anios->first();

        $secciones = collect();
        $seccion = null;
        $cursos = collect();
        $periodos = collect();
        $periodo = null;
        $resumenAsignaciones = collect();
        $pendientes = 0;
        $totalAsignadas = 0;
        $docenteFiltro = trim((string) $request->input('docente', ''));

        if ($anio) {
            $periodos = $anio->periodosAcademicos()->orderBy('orden')->get();
            $periodoId = (int) $request->input('periodo_academico_id');
            $periodo = $periodos->firstWhere('id', $periodoId)
                ?? PeriodoAcademico::actualParaAnio($anio->id);

            $secciones = Seccion::with('grado')
                ->where('anio_escolar_id', $anio->id)
                ->orderBy('grado_id')
                ->orderBy('nombre')
                ->get();

            $seccionId = (int) $request->input('seccion_id');
            if ($seccionId) {
                $seccion = $secciones->firstWhere('id', $seccionId)
                    ?? Seccion::with('grado')
                        ->where('anio_escolar_id', $anio->id)
                        ->where('id', $seccionId)
                        ->first();

                if ($seccion) {
                    $cursos = $seccion->cursos()
                        ->wherePivot('anio_escolar_id', $anio->id)
                        ->when($periodo, function ($query) use ($periodo) {
                            $query->where('seccion_curso.periodo_academico_id', $periodo->id);
                        })
                        ->orderBy('cursos.nombre')
                        ->get();
                }
            }

            $asignaciones = DB::table('seccion_curso')
                ->join('secciones', 'seccion_curso.seccion_id', '=', 'secciones.id')
                ->join('grados', 'secciones.grado_id', '=', 'grados.id')
                ->join('cursos', 'seccion_curso.curso_id', '=', 'cursos.id')
                ->leftJoin('users as docentes', 'seccion_curso.docente_id', '=', 'docentes.id')
                ->where('seccion_curso.anio_escolar_id', $anio->id)
                ->when($periodo, function ($query) use ($periodo) {
                    $query->where('seccion_curso.periodo_academico_id', $periodo->id);
                })
                ->select([
                    'seccion_curso.docente_id',
                    'docentes.name as docente',
                    'grados.nivel as nivel',
                    'grados.nombre as grado',
                    'secciones.nombre as seccion',
                    'cursos.nombre as curso',
                ])
                ->get();

            $pendientes = $asignaciones->whereNull('docente_id')->count();
            $totalAsignadas = $asignaciones->whereNotNull('docente_id')->count();

            $resumenAsignaciones = $asignaciones
                ->whereNotNull('docente_id')
                ->groupBy('docente_id')
                ->map(function ($items) {
                    $docente = $items->first()->docente ?? 'Docente';
                    $cursos = $items->pluck('curso')->unique()->values();
                    $secciones = $items->map(function ($item) {
                        $nivel = $item->nivel ?? '-';
                        $grado = $item->grado ?? '-';
                        $seccion = $item->seccion ?? '-';
                        return "{$nivel} {$grado} - {$seccion}";
                    })->unique()->values();

                    return [
                        'docente' => $docente,
                        'total_cursos' => $cursos->count(),
                        'cursos' => $cursos,
                        'secciones' => $secciones,
                    ];
                })
                ->values();

            if ($docenteFiltro !== '') {
                $needle = Str::lower($docenteFiltro);
                $resumenAsignaciones = $resumenAsignaciones
                    ->filter(fn ($item) => Str::contains(Str::lower($item['docente']), $needle))
                    ->values();
            }
        }

        $docentes = User::role('Docente')
            ->whereHas('docente', fn($q) => $q->where('estado', 'activo'))
            ->orderBy('name')->get();

        return view('admin.docentes.asignaciones', compact(
            'anios',
            'anio',
            'periodos',
            'periodo',
            'secciones',
            'seccion',
            'cursos',
            'docentes',
            'resumenAsignaciones',
            'pendientes',
            'totalAsignadas',
            'docenteFiltro'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id' => ['required', 'exists:periodos_academicos,id'],
            'seccion_id' => ['required', 'exists:secciones,id'],
            'docentes' => ['array'],
            'docentes.*' => ['nullable', 'exists:users,id'],
        ]);

        $anio = AnioEscolar::findOrFail($data['anio_escolar_id']);
        $periodo = PeriodoAcademico::where('id', $data['periodo_academico_id'])
            ->where('anio_escolar_id', $anio->id)
            ->firstOrFail();
        $seccion = Seccion::where('anio_escolar_id', $anio->id)
            ->where('id', $data['seccion_id'])
            ->firstOrFail();

        $docentesPermitidos = User::role('Docente')
            ->whereHas('docente', fn($q) => $q->where('estado', 'activo'))
            ->pluck('id')->all();

        $cursos = $seccion->cursos()
            ->wherePivot('anio_escolar_id', $anio->id)
            ->wherePivot('periodo_academico_id', $periodo->id)
            ->pluck('cursos.id');

        foreach ($cursos as $cursoId) {
            $docenteId = Arr::get($data, "docentes.$cursoId");

            if ($docenteId && !in_array((int) $docenteId, $docentesPermitidos, true)) {
                $docenteId = null;
            }

            DB::table('seccion_curso')
                ->where('anio_escolar_id', $anio->id)
                ->where('periodo_academico_id', $periodo->id)
                ->where('seccion_id', $seccion->id)
                ->where('curso_id', $cursoId)
                ->update([
                    'docente_id' => $docenteId,
                    'updated_at' => now(),
                ]);
        }

        return redirect()
            ->route('admin-sistema.asignacion-docente.index', [
                'anio_escolar_id' => $anio->id,
                'periodo_academico_id' => $periodo->id,
                'seccion_id' => $seccion->id,
            ])
            ->with('status', 'Asignaciones actualizadas correctamente.');
    }
}
