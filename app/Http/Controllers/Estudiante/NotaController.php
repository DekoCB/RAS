<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Estudiante;
use App\Models\Nota;
use App\Models\PeriodoAcademico;
use App\Models\NotaConcepto;
use App\Models\NotaConceptoDetalle;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->first() : null;

        $notas = collect();
        $anios = collect();
        $anioId = null;
        $periodoAcademicoId = null;
        $periodosAcademicos = collect();

        $periodos = [
            'Mensual 1',
            'Bimestre 1',
            'Mensual 2',
            'Bimestre 2',
        ];

        $cursosNotas = collect();

        if ($estudiante) {
            $anioActivo = AnioEscolar::where('activo', true)->first()
                ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

            $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
            $anioId = $request->input('anio_escolar_id', $anioActivo?->id);

            $periodoAcademicoId = $request->input('periodo_academico_id');

            $periodosAcademicos = $anioId
                ? PeriodoAcademico::where('anio_escolar_id', $anioId)->orderBy('orden')->get()
                : collect();

            $periodoSeleccionado = $periodosAcademicos->firstWhere('id', (int) $periodoAcademicoId)
                ?? PeriodoAcademico::actualParaAnio((int) $anioId);

            $periodoAcademicoId = $periodoSeleccionado?->id;

            $notas = Nota::where('estudiante_id', $estudiante->id)
                ->with(['curso'])
                ->when($anioId, fn($q) => $q->where('anio_escolar_id', $anioId))
                ->when($periodoAcademicoId, fn($q) => $q->where('periodo_academico_id', $periodoAcademicoId))
                ->get();

            $cursosNotas = $notas
                ->groupBy('curso_id')
                ->map(function ($items) use ($periodos) {
                    $curso = $items->first()?->curso;

                    $valores = [];
                    foreach ($periodos as $periodo) {
                        $nota = $items->firstWhere('periodo', $periodo);
                        $valores[$periodo] = $nota?->valor;
                    }

                    $numeros = collect($valores)
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->map(fn($v) => (float)$v);

                    $promedio = $numeros->isNotEmpty()
                        ? round($numeros->sum() / $numeros->count(), 2)
                        : null;

                    return [
                        'curso_id' => $items->first()?->curso_id, // ✅ IMPORTANTE
                        'curso' => $curso?->nombre ?? 'Curso',
                        'valores' => $valores,
                        'promedio' => $promedio,
                    ];
                })
                ->sortBy('curso')
                ->values();
        }

        return view('estudiante.notas.index', compact(
            'estudiante',
            'notas',
            'anios',
            'anioId',
            'periodosAcademicos',
            'periodoAcademicoId',
            'periodos',
            'cursosNotas'
        ));
    }

    public function detalle(Request $request)
    {
        $request->validate([
            'anio_escolar_id' => 'required|integer',
            'periodo_academico_id' => 'required|integer',
            'curso_id' => 'required|integer',
            'periodo' => 'required|string',
        ]);

        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->firstOrFail() : abort(403);

        $notaFinal = Nota::where([
            'estudiante_id' => $estudiante->id,
            'anio_escolar_id' => $request->anio_escolar_id,
            'periodo_academico_id' => $request->periodo_academico_id,
            'curso_id' => $request->curso_id,
            'periodo' => $request->periodo,
        ])->first();

        $seccionId = $notaFinal?->seccion_id;

        // Si no hay nota todavía
        if (!$notaFinal) {
            return response()->json([
                'final' => null,
                'tiene_conceptos' => false,
                'items' => [],
            ]);
        }

        // Si no hay seccion, igual devolvemos nota directa
        if (!$seccionId) {
            return response()->json([
                'final' => $notaFinal?->valor,
                'tiene_conceptos' => false,
                'items' => [
                    [
                        'tipo' => 'Nota final',
                        'peso' => 100,
                        'nota' => $notaFinal?->valor,
                        'fecha' => optional($notaFinal->updated_at)->format('d/m/Y h:i a'),
                    ]
                ],
            ]);
        }

        $conceptos = NotaConcepto::where([
            'anio_escolar_id' => $request->anio_escolar_id,
            'periodo_academico_id' => $request->periodo_academico_id,
            'curso_id' => $request->curso_id,
            'seccion_id' => $seccionId,
            'periodo' => $request->periodo,
        ])->orderBy('id')->get();

        // Si no hay conceptos configurados, mostrar nota directa
        if ($conceptos->isEmpty()) {
            return response()->json([
                'final' => $notaFinal?->valor,
                'tiene_conceptos' => false,
                'items' => [
                    [
                        'tipo' => 'Nota final',
                        'peso' => 100,
                        'nota' => $notaFinal?->valor,
                        'fecha' => optional($notaFinal->updated_at)->format('d/m/Y h:i a'),
                    ]
                ],
            ]);
        }

        $detalles = NotaConceptoDetalle::where('estudiante_id', $estudiante->id)
            ->whereIn('nota_concepto_id', $conceptos->pluck('id'))
            ->get()
            ->keyBy('nota_concepto_id');

        // Items para tabla (Tipo/Peso/Nota/Fecha)
        $items = $conceptos->map(function ($c) use ($detalles) {
            $detalle = $detalles->get($c->id);

            return [
                'tipo' => $c->nombre,
                'peso' => (float)$c->porcentaje,
                'nota' => optional($detalle)->nota,
                'fecha' => $detalle?->updated_at?->format('d/m/Y h:i a'),
            ];
        })->values();

        return response()->json([
            'final' => $notaFinal?->valor,
            'tiene_conceptos' => true,
            'items' => $items,
        ]);
    }

    public function detalleTodo(Request $request)
    {
        $request->validate([
            'anio_escolar_id' => 'required|integer',
            'periodo_academico_id' => 'required|integer',
        ]);

        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->firstOrFail() : abort(403);

        $periodos = ['Mensual 1', 'Bimestre 1', 'Mensual 2', 'Bimestre 2'];

        // Todas las notas del alumno para ese año + periodo académico
        $notas = Nota::where('estudiante_id', $estudiante->id)
            ->with('curso')
            ->where('anio_escolar_id', $request->anio_escolar_id)
            ->where('periodo_academico_id', $request->periodo_academico_id)
            ->get();

        // Agrupar por curso
        $porCurso = $notas->groupBy('curso_id')->map(function ($itemsCurso) use ($periodos, $request, $estudiante) {
            $curso = $itemsCurso->first()?->curso;

            $evaluaciones = collect($periodos)->map(function ($periodo) use ($itemsCurso, $request, $estudiante) {
                $notaFinal = $itemsCurso->firstWhere('periodo', $periodo);

                // Si aún no hay nota en ese periodo
                if (!$notaFinal) {
                    return [
                        'periodo' => $periodo,
                        'final' => null,
                        'items' => [],
                    ];
                }

                $seccionId = $notaFinal->seccion_id;

                // Si no hay seccion o no hay conceptos → nota directa
                if (!$seccionId) {
                    return [
                        'periodo' => $periodo,
                        'final' => $notaFinal->valor,
                        'items' => [[
                            'tipo' => 'Nota final',
                            'peso' => 100,
                            'nota' => $notaFinal->valor,
                            'fecha' => optional($notaFinal->updated_at)->format('d/m/Y h:i a'),
                        ]],
                    ];
                }

                // Buscar conceptos configurados
                $conceptos = NotaConcepto::where([
                    'anio_escolar_id' => $request->anio_escolar_id,
                    'periodo_academico_id' => $request->periodo_academico_id,
                    'curso_id' => $notaFinal->curso_id,
                    'seccion_id' => $seccionId,
                    'periodo' => $periodo,
                ])->orderBy('id')->get();

                if ($conceptos->isEmpty()) {
                    return [
                        'periodo' => $periodo,
                        'final' => $notaFinal->valor,
                        'items' => [[
                            'tipo' => 'Nota final',
                            'peso' => 100,
                            'nota' => $notaFinal->valor,
                            'fecha' => optional($notaFinal->updated_at)->format('d/m/Y h:i a'),
                        ]],
                    ];
                }

                // Detalles por alumno
                $detalles = NotaConceptoDetalle::where('estudiante_id', $estudiante->id)
                    ->whereIn('nota_concepto_id', $conceptos->pluck('id'))
                    ->get()
                    ->keyBy('nota_concepto_id');

                $items = $conceptos->map(function ($c) use ($detalles) {
                    $d = $detalles->get($c->id);
                    return [
                        'tipo' => $c->nombre,
                        'peso' => (float)$c->porcentaje,
                        'nota' => $d?->nota,
                        'fecha' => $d?->updated_at?->format('d/m/Y h:i a'),
                    ];
                })->values();

                return [
                    'periodo' => $periodo,
                    'final' => $notaFinal->valor,
                    'items' => $items,
                ];
            })->values();

            return [
                'curso' => $curso?->nombre ?? 'Curso',
                'evaluaciones' => $evaluaciones,
            ];
        })->values();

        return response()->json([
            'periodos' => $periodos,
            'cursos' => $porCurso,
        ]);
    }
}
