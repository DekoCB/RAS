<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Curso;
use App\Models\HistorialNotaCierre;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\NotaCierre;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotasController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:nota.cerrar')->only(['index', 'cerrar', 'estadisticas', 'faltantes', 'exportar']);
        $this->middleware('permission:nota.reabrir')->only(['reabrir']);
    }

    // ─── Página principal ────────────────────────────────────────────────────

    public function index()
    {
        $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
        $cursos = Curso::orderBy('nombre')->get();
        $secciones = Seccion::with(['grado', 'anioEscolar'])->orderBy('nombre')->get();
        $periodosAcademicos = PeriodoAcademico::with('anioEscolar')
            ->orderBy('anio_escolar_id')
            ->orderBy('orden')
            ->get();
        $periodos = ['Mensual 1', 'Bimestre 1', 'Mensual 2', 'Bimestre 2'];

        return view('director.notas.cierre', compact(
            'anios', 'cursos', 'secciones', 'periodos', 'periodosAcademicos'
        ));
    }

    // ─── Estadísticas (AJAX) ─────────────────────────────────────────────────

    public function estadisticas(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id'=> ['required', 'exists:periodos_academicos,id'],
            'periodo'             => ['required', 'string', 'max:50'],
            'alcance'             => ['required', 'in:todos,seccion,curso'],
            'seccion_id'          => ['nullable', 'exists:secciones,id'],
            'curso_id'            => ['nullable', 'exists:cursos,id'],
        ]);

        $stats = $this->calcularEstadisticas($data);

        return response()->json($stats);
    }

    // ─── Detalle de faltantes (AJAX) ─────────────────────────────────────────

    public function faltantes(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id'=> ['required', 'exists:periodos_academicos,id'],
            'periodo'             => ['required', 'string', 'max:50'],
            'alcance'             => ['required', 'in:todos,seccion,curso'],
            'seccion_id'          => ['nullable', 'exists:secciones,id'],
            'curso_id'            => ['nullable', 'exists:cursos,id'],
        ]);

        $faltantes = $this->obtenerFaltantes($data);

        return response()->json(['faltantes' => $faltantes]);
    }

    // ─── Cerrar notas ─────────────────────────────────────────────────────────

    public function cerrar(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id'=> ['required', 'exists:periodos_academicos,id'],
            'periodo'             => ['required', 'string', 'max:50'],
            'alcance'             => ['required', 'in:todos,seccion,curso'],
            'seccion_id'          => ['nullable', 'exists:secciones,id'],
            'curso_id'            => ['nullable', 'exists:cursos,id'],
            'es_forzado'          => ['boolean'],
            'motivo'              => ['nullable', 'string', 'max:500'],
        ]);

        $esForzado = (bool) ($data['es_forzado'] ?? false);

        if ($esForzado && empty($data['motivo'])) {
            return response()->json([
                'success' => false,
                'message' => 'El motivo es obligatorio para un cierre forzado.',
            ], 422);
        }

        $periodoAcademico = PeriodoAcademico::where('id', $data['periodo_academico_id'])
            ->where('anio_escolar_id', $data['anio_escolar_id'])
            ->first();

        if (!$periodoAcademico) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo seleccionado no pertenece al año escolar.',
            ], 422);
        }

        // Verificar completitud si no es forzado
        if (!$esForzado) {
            $stats = $this->calcularEstadisticas($data);
            if ($stats['total_faltantes'] > 0) {
                return response()->json([
                    'success'    => false,
                    'message'    => "Hay {$stats['total_faltantes']} notas faltantes. Use el cierre forzado si desea proceder de todas formas.",
                    'faltantes'  => $stats['total_faltantes'],
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Crear o actualizar el registro de cierre
            $cierre = NotaCierre::updateOrCreate(
                [
                    'anio_escolar_id'      => $data['anio_escolar_id'],
                    'periodo_academico_id' => $data['periodo_academico_id'],
                    'periodo'              => $data['periodo'],
                    'curso_id'             => $data['curso_id'] ?? null,
                ],
                [
                    'cerrado_por'  => $request->user()?->id,
                    'fecha_cierre' => now(),
                    'motivo'       => $data['motivo'] ?? null,
                    'es_forzado'   => $esForzado,
                ]
            );

            // Actualizar notas
            $query = Nota::where('anio_escolar_id', $data['anio_escolar_id'])
                ->where('periodo_academico_id', $data['periodo_academico_id'])
                ->where('periodo', $data['periodo']);

            if (!empty($data['seccion_id'])) {
                $query->where('seccion_id', $data['seccion_id']);
            }
            if (!empty($data['curso_id'])) {
                $query->where('curso_id', $data['curso_id']);
            }

            $affected = $query->update([
                'estado'       => 'cerrada',
                'fecha_cierre' => now(),
            ]);

            // Guardar en historial
            $stats = $this->calcularEstadisticas($data);
            HistorialNotaCierre::create([
                'anio_escolar_id'      => $data['anio_escolar_id'],
                'periodo_academico_id' => $data['periodo_academico_id'],
                'periodo'              => $data['periodo'],
                'seccion_id'           => $data['seccion_id'] ?? null,
                'curso_id'             => $data['curso_id'] ?? null,
                'accion'               => $esForzado ? 'cierre_forzado' : 'cerrar',
                'realizado_por'        => $request->user()?->id,
                'motivo'               => $data['motivo'] ?? null,
                'datos_resumen'        => [
                    'notas_afectadas'       => $affected,
                    'total_esperadas'       => $stats['total_esperadas'],
                    'total_registradas'     => $stats['total_registradas'],
                    'porcentaje_completitud'=> $stats['porcentaje_completitud'],
                ],
            ]);

            AuditoriaService::log($request->user()?->id, 'cerrar', 'notas', $cierre->id, $data, $request);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Cierre exitoso. {$affected} notas cerradas.",
                'affected' => $affected,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar notas: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Reabrir notas ────────────────────────────────────────────────────────

    public function reabrir(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id'=> ['required', 'exists:periodos_academicos,id'],
            'periodo'             => ['required', 'string', 'max:50'],
            'alcance'             => ['required', 'in:todos,seccion,curso'],
            'seccion_id'          => ['nullable', 'exists:secciones,id'],
            'curso_id'            => ['nullable', 'exists:cursos,id'],
            'motivo'              => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $periodoAcademico = PeriodoAcademico::where('id', $data['periodo_academico_id'])
            ->where('anio_escolar_id', $data['anio_escolar_id'])
            ->first();

        if (!$periodoAcademico) {
            return response()->json([
                'success' => false,
                'message' => 'El periodo seleccionado no pertenece al año escolar.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            NotaCierre::where('anio_escolar_id', $data['anio_escolar_id'])
                ->where('periodo_academico_id', $data['periodo_academico_id'])
                ->where('periodo', $data['periodo'])
                ->where('curso_id', $data['curso_id'] ?? null)
                ->delete();

            $query = Nota::where('anio_escolar_id', $data['anio_escolar_id'])
                ->where('periodo_academico_id', $data['periodo_academico_id'])
                ->where('periodo', $data['periodo']);

            if (!empty($data['seccion_id'])) {
                $query->where('seccion_id', $data['seccion_id']);
            }
            if (!empty($data['curso_id'])) {
                $query->where('curso_id', $data['curso_id']);
            }

            $affected = $query->update([
                'estado'       => 'abierta',
                'fecha_cierre' => null,
            ]);

            HistorialNotaCierre::create([
                'anio_escolar_id'      => $data['anio_escolar_id'],
                'periodo_academico_id' => $data['periodo_academico_id'],
                'periodo'              => $data['periodo'],
                'seccion_id'           => $data['seccion_id'] ?? null,
                'curso_id'             => $data['curso_id'] ?? null,
                'accion'               => 'reabrir',
                'realizado_por'        => $request->user()?->id,
                'motivo'               => $data['motivo'],
                'datos_resumen'        => ['notas_afectadas' => $affected],
            ]);

            AuditoriaService::log($request->user()?->id, 'reabrir', 'notas', 0, $data, $request);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Periodo reabierto. {$affected} notas habilitadas para edición.",
                'affected' => $affected,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al reabrir notas: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Historial (AJAX) ─────────────────────────────────────────────────────

    public function historial(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['nullable', 'exists:anios_escolares,id'],
            'periodo_academico_id'=> ['nullable', 'exists:periodos_academicos,id'],
            'periodo'             => ['nullable', 'string', 'max:50'],
        ]);

        $query = HistorialNotaCierre::with([
                'anioEscolar',
                'periodoAcademico',
                'seccion.grado',
                'curso',
                'realizadoPor',
            ])
            ->orderByDesc('created_at');

        if (!empty($data['anio_escolar_id'])) {
            $query->where('anio_escolar_id', $data['anio_escolar_id']);
        }
        if (!empty($data['periodo_academico_id'])) {
            $query->where('periodo_academico_id', $data['periodo_academico_id']);
        }
        if (!empty($data['periodo'])) {
            $query->where('periodo', $data['periodo']);
        }

        $historial = $query->limit(50)->get()->map(function ($h) {
            return [
                'id'                 => $h->id,
                'accion'             => $h->accion,
                'accion_label'       => $this->accionLabel($h->accion),
                'anio'               => $h->anioEscolar?->nombre,
                'periodo_academico'  => $h->periodoAcademico?->nombre,
                'periodo'            => $h->periodo,
                'seccion'            => $h->seccion ? ($h->seccion->grado?->nombre . ' ' . $h->seccion->nombre) : 'Todas',
                'curso'              => $h->curso?->nombre ?? 'Todos',
                'realizado_por'      => $h->realizadoPor?->name ?? 'Sistema',
                'motivo'             => $h->motivo,
                'datos_resumen'      => $h->datos_resumen,
                'fecha'              => $h->created_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json(['historial' => $historial]);
    }

    // ─── Exportar reporte ─────────────────────────────────────────────────────

    public function exportar(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id'=> ['required', 'exists:periodos_academicos,id'],
            'periodo'             => ['required', 'string', 'max:50'],
            'alcance'             => ['required', 'in:todos,seccion,curso'],
            'seccion_id'          => ['nullable', 'exists:secciones,id'],
            'curso_id'            => ['nullable', 'exists:cursos,id'],
            'formato'             => ['required', 'in:pdf,excel'],
        ]);

        $stats    = $this->calcularEstadisticas($data);
        $faltantes = $this->obtenerFaltantes($data);
        $anio     = AnioEscolar::find($data['anio_escolar_id']);
        $periodo  = PeriodoAcademico::find($data['periodo_academico_id']);
        $cierre   = NotaCierre::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('periodo', $data['periodo'])
            ->where('curso_id', $data['curso_id'] ?? null)
            ->with('cerradoPor')
            ->first();

        if ($data['formato'] === 'excel') {
            return $this->exportarExcel($data, $stats, $faltantes, $anio, $periodo, $cierre);
        }

        return $this->exportarPdf($data, $stats, $faltantes, $anio, $periodo, $cierre);
    }

    // ─── Estado de cierre (AJAX) ──────────────────────────────────────────────

    public function estadoCierre(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['required', 'exists:anios_escolares,id'],
            'periodo_academico_id'=> ['required', 'exists:periodos_academicos,id'],
            'periodo'             => ['required', 'string', 'max:50'],
            'curso_id'            => ['nullable', 'exists:cursos,id'],
        ]);

        $cierre = NotaCierre::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('periodo', $data['periodo'])
            ->where('curso_id', $data['curso_id'] ?? null)
            ->with('cerradoPor')
            ->first();

        return response()->json([
            'cerrado'      => (bool) $cierre,
            'fecha_cierre' => $cierre?->fecha_cierre ? \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') : null,
            'cerrado_por'  => $cierre?->cerradoPor?->name,
            'es_forzado'   => (bool) $cierre?->es_forzado,
            'motivo'       => $cierre?->motivo,
        ]);
    }

    // ─── Métodos privados ─────────────────────────────────────────────────────

    private function calcularEstadisticas(array $data): array
    {
        // Obtener combinaciones seccion-curso esperadas
        $scQuery = DB::table('seccion_curso as sc')
            ->join('secciones as s', 's.id', '=', 'sc.seccion_id')
            ->join('cursos as c', 'c.id', '=', 'sc.curso_id')
            ->leftJoin('users as u', 'u.id', '=', 'sc.docente_id')
            ->leftJoin('grados as g', 'g.id', '=', 's.grado_id')
            ->where('sc.anio_escolar_id', $data['anio_escolar_id'])
            ->where('sc.periodo_academico_id', $data['periodo_academico_id'])
            ->select(
                'sc.seccion_id',
                'sc.curso_id',
                'sc.docente_id',
                's.nombre as seccion_nombre',
                'g.nombre as grado_nombre',
                'c.nombre as curso_nombre',
                'u.name as docente_nombre'
            );

        if (!empty($data['seccion_id'])) {
            $scQuery->where('sc.seccion_id', $data['seccion_id']);
        }
        if (!empty($data['curso_id'])) {
            $scQuery->where('sc.curso_id', $data['curso_id']);
        }

        $seccionCursos = $scQuery->get();

        // Para cada seccion-curso, contar alumnos matriculados y notas registradas
        $resumen = [];
        $totalEsperadas = 0;
        $totalRegistradas = 0;

        foreach ($seccionCursos as $sc) {
            $esperadas = DB::table('matriculas')
                ->where('anio_escolar_id', $data['anio_escolar_id'])
                ->where('periodo_academico_id', $data['periodo_academico_id'])
                ->where('seccion_id', $sc->seccion_id)
                ->whereIn('estado', ['activa', 'registrada'])
                ->count();

            $registradas = DB::table('notas')
                ->where('anio_escolar_id', $data['anio_escolar_id'])
                ->where('periodo_academico_id', $data['periodo_academico_id'])
                ->where('seccion_id', $sc->seccion_id)
                ->where('curso_id', $sc->curso_id)
                ->where('periodo', $data['periodo'])
                ->count();

            $porcentaje = $esperadas > 0 ? round(($registradas / $esperadas) * 100, 1) : 100;
            $estado = $this->semaforo($porcentaje);

            $totalEsperadas  += $esperadas;
            $totalRegistradas += $registradas;

            $resumen[] = [
                'seccion_id'     => $sc->seccion_id,
                'curso_id'       => $sc->curso_id,
                'docente_id'     => $sc->docente_id,
                'seccion'        => trim($sc->grado_nombre . ' ' . $sc->seccion_nombre),
                'curso'          => $sc->curso_nombre,
                'docente'        => $sc->docente_nombre ?? 'Sin docente',
                'esperadas'      => $esperadas,
                'registradas'    => $registradas,
                'faltantes'      => max(0, $esperadas - $registradas),
                'porcentaje'     => $porcentaje,
                'estado'         => $estado,
            ];
        }

        $totalPorcentaje = $totalEsperadas > 0
            ? round(($totalRegistradas / $totalEsperadas) * 100, 1)
            : 100;

        // Verificar si está cerrado
        $cierre = NotaCierre::where('anio_escolar_id', $data['anio_escolar_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('periodo', $data['periodo'])
            ->where('curso_id', $data['curso_id'] ?? null)
            ->first();

        return [
            'total_esperadas'        => $totalEsperadas,
            'total_registradas'      => $totalRegistradas,
            'total_faltantes'        => max(0, $totalEsperadas - $totalRegistradas),
            'porcentaje_completitud' => $totalPorcentaje,
            'estado_global'          => $this->semaforo($totalPorcentaje),
            'detalle'                => $resumen,
            'cerrado'                => (bool) $cierre,
            'fecha_cierre'           => $cierre?->fecha_cierre ? \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y H:i') : null,
            'cerrado_por'            => $cierre?->cerradoPor?->name ?? null,
            'es_forzado'             => (bool) $cierre?->es_forzado,
            'motivo_cierre'          => $cierre?->motivo,
        ];
    }

    private function obtenerFaltantes(array $data): array
    {
        $scQuery = DB::table('seccion_curso as sc')
            ->join('secciones as s', 's.id', '=', 'sc.seccion_id')
            ->join('cursos as c', 'c.id', '=', 'sc.curso_id')
            ->leftJoin('users as u', 'u.id', '=', 'sc.docente_id')
            ->leftJoin('grados as g', 'g.id', '=', 's.grado_id')
            ->where('sc.anio_escolar_id', $data['anio_escolar_id'])
            ->where('sc.periodo_academico_id', $data['periodo_academico_id'])
            ->select(
                'sc.seccion_id',
                'sc.curso_id',
                'sc.docente_id',
                's.nombre as seccion_nombre',
                'g.nombre as grado_nombre',
                'c.nombre as curso_nombre',
                'u.name as docente_nombre'
            );

        if (!empty($data['seccion_id'])) {
            $scQuery->where('sc.seccion_id', $data['seccion_id']);
        }
        if (!empty($data['curso_id'])) {
            $scQuery->where('sc.curso_id', $data['curso_id']);
        }

        $seccionCursos = $scQuery->get();

        $faltantes = [];

        foreach ($seccionCursos as $sc) {
            // IDs de estudiantes con nota registrada
            $conNota = DB::table('notas')
                ->where('anio_escolar_id', $data['anio_escolar_id'])
                ->where('periodo_academico_id', $data['periodo_academico_id'])
                ->where('seccion_id', $sc->seccion_id)
                ->where('curso_id', $sc->curso_id)
                ->where('periodo', $data['periodo'])
                ->pluck('estudiante_id')
                ->toArray();

            // Estudiantes matriculados sin nota
            $sinNota = DB::table('matriculas as m')
                ->join('estudiantes as e', 'e.id', '=', 'm.estudiante_id')
                ->join('users as u', 'u.id', '=', 'e.user_id')
                ->where('m.anio_escolar_id', $data['anio_escolar_id'])
                ->where('m.periodo_academico_id', $data['periodo_academico_id'])
                ->where('m.seccion_id', $sc->seccion_id)
                ->whereIn('m.estado', ['activa', 'registrada'])
                ->whereNotIn('m.estudiante_id', $conNota)
                ->select('e.id as estudiante_id', 'u.name as nombre')
                ->get();

            foreach ($sinNota as $estudiante) {
                $faltantes[] = [
                    'seccion'         => trim($sc->grado_nombre . ' ' . $sc->seccion_nombre),
                    'seccion_id'      => $sc->seccion_id,
                    'curso'           => $sc->curso_nombre,
                    'curso_id'        => $sc->curso_id,
                    'estudiante'      => $estudiante->nombre,
                    'estudiante_id'   => $estudiante->estudiante_id,
                    'docente'         => $sc->docente_nombre ?? 'Sin docente',
                    'docente_id'      => $sc->docente_id,
                ];
            }
        }

        return $faltantes;
    }

    private function semaforo(float $porcentaje): string
    {
        if ($porcentaje >= 100) return 'verde';
        if ($porcentaje >= 80)  return 'amarillo';
        return 'rojo';
    }

    private function accionLabel(string $accion): string
    {
        return match ($accion) {
            'cerrar'         => 'Cierre Normal',
            'cierre_forzado' => 'Cierre Forzado',
            'reabrir'        => 'Reapertura',
            default          => $accion,
        };
    }

    private function exportarExcel(array $data, array $stats, array $faltantes, $anio, $periodo, $cierre)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cierre de Notas');

        // Estilos de encabezado
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2A5885']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $subHeaderStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F0FE']],
        ];

        // Título
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'ACTA DE CIERRE DE NOTAS - REYSCHOOL');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A3558']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Información general
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', "Año Escolar: {$anio->nombre}  |  Periodo: {$periodo->nombre}  |  Evaluación: {$data['periodo']}");
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A3:G3');
        $estadoCierre = $stats['cerrado']
            ? "CERRADO el {$stats['fecha_cierre']} por {$stats['cerrado_por']}"
            : "ABIERTO (sin cierre registrado)";
        $sheet->setCellValue('A3', "Estado: {$estadoCierre}");
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

        // Resumen de completitud
        $sheet->setCellValue('A5', 'RESUMEN DE COMPLETITUD');
        $sheet->getStyle('A5')->applyFromArray($subHeaderStyle);

        $sheet->setCellValue('A6', 'Total Esperadas');
        $sheet->setCellValue('B6', $stats['total_esperadas']);
        $sheet->setCellValue('A7', 'Total Registradas');
        $sheet->setCellValue('B7', $stats['total_registradas']);
        $sheet->setCellValue('A8', 'Total Faltantes');
        $sheet->setCellValue('B8', $stats['total_faltantes']);
        $sheet->setCellValue('A9', '% Completitud');
        $sheet->setCellValue('B9', $stats['porcentaje_completitud'] . '%');

        // Detalle por sección/curso
        $row = 12;
        $sheet->setCellValue('A' . $row, 'DETALLE POR SECCIÓN / CURSO');
        $sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
        $sheet->mergeCells('A' . $row . ':G' . $row);

        $row++;
        $headers = ['Sección', 'Curso', 'Docente', 'Esperadas', 'Registradas', 'Faltantes', '% Completitud'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(18);

        $row++;
        foreach ($stats['detalle'] as $det) {
            $sheet->setCellValue('A' . $row, $det['seccion']);
            $sheet->setCellValue('B' . $row, $det['curso']);
            $sheet->setCellValue('C' . $row, $det['docente']);
            $sheet->setCellValue('D' . $row, $det['esperadas']);
            $sheet->setCellValue('E' . $row, $det['registradas']);
            $sheet->setCellValue('F' . $row, $det['faltantes']);
            $sheet->setCellValue('G' . $row, $det['porcentaje'] . '%');

            // Color según estado semáforo
            $colorMap = ['verde' => 'C8E6C9', 'amarillo' => 'FFF9C4', 'rojo' => 'FFCDD2'];
            $color = $colorMap[$det['estado']] ?? 'FFFFFF';
            $sheet->getStyle("A{$row}:G{$row}")->getFill()
                ->setFillType('solid')
                ->getStartColor()->setRGB($color);

            $row++;
        }

        // Faltantes detallados
        if (!empty($faltantes)) {
            $row += 2;
            $sheet->setCellValue('A' . $row, 'NOTAS FALTANTES');
            $sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
            $sheet->mergeCells('A' . $row . ':D' . $row);

            $row++;
            $fHeaders = ['Sección', 'Curso', 'Alumno', 'Docente'];
            foreach ($fHeaders as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
            }
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($headerStyle);

            $row++;
            foreach ($faltantes as $f) {
                $sheet->setCellValue('A' . $row, $f['seccion']);
                $sheet->setCellValue('B' . $row, $f['curso']);
                $sheet->setCellValue('C' . $row, $f['estudiante']);
                $sheet->setCellValue('D' . $row, $f['docente']);
                $sheet->getStyle("A{$row}:D{$row}")->getFill()
                    ->setFillType('solid')
                    ->getStartColor()->setRGB('FFEBEE');
                $row++;
            }
        }

        // Auto-width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'CIERRE_NOTAS_' . strtoupper(str_replace(' ', '_', $data['periodo'])) . '_' . now()->format('d-m-Y') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function exportarPdf(array $data, array $stats, array $faltantes, $anio, $periodo, $cierre)
    {
        $html = view('director.notas.acta_pdf', compact('data', 'stats', 'faltantes', 'anio', 'periodo', 'cierre'))->render();

        // Usar dompdf si está disponible, de lo contrario salida HTML
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf(['defaultPaperSize' => 'A4']);
            $dompdf->loadHtml($html);
            $dompdf->render();
            $filename = 'CIERRE_NOTAS_' . strtoupper(str_replace(' ', '_', $data['periodo'])) . '_' . now()->format('d-m-Y') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
            exit;
        }

        // Fallback: HTML directo para imprimir
        return response($html)->header('Content-Type', 'text/html');
    }
}
