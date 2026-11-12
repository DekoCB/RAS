<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\ConceptoPago;
use App\Models\Deuda;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Pago;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reporte.pagos_dia')->only(['pagosDelDia', 'pagosDelDiaExcel']);
        $this->middleware('permission:reporte.morosos')->only(['morosos', 'morososExcel']);
    }

    public function pagosDelDia(Request $request)
    {
        $filters = $this->normalizePagoFilters($request, true);

        $pagosBase = Pago::query();
        $this->applyPagoFilters($pagosBase, $filters);

        $kpiOperaciones = (clone $pagosBase)->count();
        $kpiMontoTotal = (float) (clone $pagosBase)->sum('monto');
        $kpiConfirmados = (clone $pagosBase)->where('estado', 'confirmado')->count();
        $kpiPendientes = (clone $pagosBase)->where('estado', 'pendiente')->count();
        $kpiTicketPromedio = $kpiOperaciones > 0 ? ($kpiMontoTotal / $kpiOperaciones) : 0.0;

        $metodoResumen = (clone $pagosBase)
            ->pluck('metodo')
            ->flatMap(function ($metodo) {
                if (!$metodo) {
                    return ['Sin metodo'];
                }
                $decoded = json_decode($metodo, true);
                if (is_array($decoded)) {
                    $nombres = array_filter(array_column($decoded, 'metodo'));
                    return $nombres ?: ['Sin metodo'];
                }
                $valor = trim((string) $metodo);
                return [$valor !== '' ? $valor : 'Sin metodo'];
            })
            ->countBy()
            ->sortDesc()
            ->take(4);

        $pagosQuery = (clone $pagosBase)
            ->with(['deudas.matricula.estudiante', 'deudas.concepto', 'registradoPor']);
        $this->applyPagoSorting($pagosQuery, $filters['orden']);

        $pagos = $pagosQuery
            ->paginate(15)
            ->withQueryString();

        $totalesPagina = [
            'registros' => $pagos->count(),
            'monto' => (float) $pagos->getCollection()->sum(function (Pago $pago) {
                return (float) $pago->monto;
            }),
        ];

        $metodosDisponibles = Pago::query()
            ->whereNotNull('metodo')
            ->where('metodo', '<>', '')
            ->pluck('metodo')
            ->flatMap(function ($metodo) {
                $decoded = json_decode($metodo, true);
                if (is_array($decoded)) {
                    return array_filter(array_column($decoded, 'metodo'));
                }
                $valor = trim((string) $metodo);
                return $valor !== '' ? [$valor] : [];
            })
            ->unique()
            ->sort()
            ->values();

        $kpis = [
            'operaciones' => $kpiOperaciones,
            'monto_total' => $kpiMontoTotal,
            'confirmados' => $kpiConfirmados,
            'pendientes' => $kpiPendientes,
            'ticket_promedio' => $kpiTicketPromedio,
        ];

        return view('secretaria.reportes.pagos-dia', [
            'pagos' => $pagos,
            'filters' => $filters,
            'kpis' => $kpis,
            'metodoResumen' => $metodoResumen,
            'metodosDisponibles' => $metodosDisponibles,
            'totalesPagina' => $totalesPagina,
            'fechaCorte' => now(),
        ]);
    }

    public function pagosDelDiaExcel(Request $request)
    {
        $filters = $this->normalizePagoFilters($request, true);

        $query = Pago::query()
            ->with(['deudas.matricula.estudiante', 'deudas.concepto', 'registradoPor']);
        $this->applyPagoFilters($query, $filters);
        $this->applyPagoSorting($query, $filters['orden']);

        $pagos = $query->get();

        $titulo = 'Reporte de pagos';
        $headers = ['Fecha', 'DNI', 'Alumno', 'Concepto', 'Monto', 'Metodo', 'Estado', 'Registrado por'];
        $rows = [];

        foreach ($pagos as $pago) {
            $deuda = $pago->deudas->first();
            $estudiante = $deuda?->matricula?->estudiante;
            $concepto = $deuda?->concepto?->nombre;
            $conceptoLabel = $pago->deudas->count() > 1 ? 'Varios' : ($concepto ?? '-');
            $alumnoLabel = $pago->deudas->count() > 1
                ? 'Varios'
                : ($estudiante ? trim(($estudiante->apellidos ?? '') . ', ' . ($estudiante->nombres ?? ''), ' ,') : '-');

            $rows[] = [
                $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : '-',
                $estudiante?->dni ?? '-',
                $alumnoLabel,
                $conceptoLabel,
                (float) $pago->monto,
                $pago->metodo ?? '-',
                $pago->estado ?? '-',
                $pago->registradoPor->name ?? '-',
            ];
        }

        return $this->exportXlsx(
            $titulo,
            $headers,
            $rows,
            'REPORTE_PAGOS_' . now()->format('d-m-Y') . '.xlsx',
            [5]
        );
    }

    public function morosos(Request $request)
    {
        $filters = $this->normalizeMorosoFilters($request);

        $deudasBase = Deuda::query()
            ->with(['matricula.estudiante', 'matricula.grado', 'matricula.seccion', 'concepto'])
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->where('saldo', '>', 0);

        $this->applyMorosoFilters($deudasBase, $filters, true);

        $kpiBase = Deuda::query()
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->where('saldo', '>', 0);
        $this->applyMorosoFilters($kpiBase, $filters, false);

        $hoy = Carbon::today();

        $kpis = [
            'registros' => (clone $kpiBase)->count(),
            'saldo_total' => (float) (clone $kpiBase)->sum('saldo'),
            'monto_total' => (float) (clone $kpiBase)->sum('monto'),
            'vencidos' => (clone $kpiBase)
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', $hoy->toDateString())
                ->count(),
            'por_vencer_7' => (clone $kpiBase)
                ->whereNotNull('fecha_vencimiento')
                ->whereBetween('fecha_vencimiento', [
                    $hoy->copy()->addDay()->toDateString(),
                    $hoy->copy()->addDays(7)->toDateString(),
                ])->count(),
        ];
        $kpis['saldo_promedio'] = $kpis['registros'] > 0 ? ($kpis['saldo_total'] / $kpis['registros']) : 0.0;

        $riesgoResumen = [
            '1_7' => (clone $kpiBase)
                ->whereNotNull('fecha_vencimiento')
                ->whereBetween('fecha_vencimiento', [
                    $hoy->copy()->subDays(7)->toDateString(),
                    $hoy->copy()->subDay()->toDateString(),
                ])->count(),
            '8_30' => (clone $kpiBase)
                ->whereNotNull('fecha_vencimiento')
                ->whereBetween('fecha_vencimiento', [
                    $hoy->copy()->subDays(30)->toDateString(),
                    $hoy->copy()->subDays(8)->toDateString(),
                ])->count(),
            '31_60' => (clone $kpiBase)
                ->whereNotNull('fecha_vencimiento')
                ->whereBetween('fecha_vencimiento', [
                    $hoy->copy()->subDays(60)->toDateString(),
                    $hoy->copy()->subDays(31)->toDateString(),
                ])->count(),
            '60_plus' => (clone $kpiBase)
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->subDays(61)->toDateString())
                ->count(),
            'sin_fecha' => (clone $kpiBase)->whereNull('fecha_vencimiento')->count(),
        ];

        $deudasQuery = (clone $deudasBase);
        $this->applyMorosoSorting($deudasQuery, $filters['orden']);

        $deudas = $deudasQuery
            ->paginate(15)
            ->withQueryString();

        $deudas->getCollection()->transform(function (Deuda $deuda) {
            $diasAtraso = $this->diasAtraso($deuda->fecha_vencimiento);
            $riesgoKey = $this->riesgoPorDias($diasAtraso);
            $deuda->dias_atraso = $diasAtraso;
            $deuda->riesgo_key = $riesgoKey;
            $deuda->riesgo_label = $this->riesgoLabel($riesgoKey);

            return $deuda;
        });

        $totalesPagina = [
            'monto' => (float) $deudas->getCollection()->sum('monto'),
            'saldo' => (float) $deudas->getCollection()->sum('saldo'),
            'registros' => $deudas->count(),
        ];

        return view('secretaria.reportes.morosos', [
            'deudas' => $deudas,
            'filters' => $filters,
            'kpis' => $kpis,
            'riesgoResumen' => $riesgoResumen,
            'totalesPagina' => $totalesPagina,
            'grados' => Grado::query()->orderBy('nivel')->orderBy('nombre')->get(['id', 'nivel', 'nombre']),
            'secciones' => Seccion::query()->orderBy('nombre')->get(['id', 'nombre']),
            'conceptos' => ConceptoPago::query()->orderBy('nombre')->get(['id', 'nombre']),
            'fechaCorte' => now(),
        ]);
    }

    public function morososExcel(Request $request)
    {
        $filters = $this->normalizeMorosoFilters($request);

        $query = Deuda::query()
            ->with(['matricula.estudiante', 'matricula.grado', 'matricula.seccion', 'concepto'])
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->where('saldo', '>', 0);

        $this->applyMorosoFilters($query, $filters, true);
        $this->applyMorosoSorting($query, $filters['orden']);

        $deudas = $query->get();

        $titulo = 'Reporte de morosos';
        $headers = ['Alumno', 'DNI', 'Grado/Seccion', 'Concepto', 'Periodo', 'Monto', 'Saldo', 'Vencimiento', 'Dias atraso', 'Riesgo', 'Estado'];
        $rows = [];

        foreach ($deudas as $deuda) {
            $estudiante = $deuda->matricula?->estudiante;
            $diasAtraso = $this->diasAtraso($deuda->fecha_vencimiento);
            $riesgoKey = $this->riesgoPorDias($diasAtraso);

            $rows[] = [
                $estudiante ? trim(($estudiante->apellidos ?? '') . ', ' . ($estudiante->nombres ?? ''), ' ,') : '-',
                $estudiante?->dni ?? '-',
                trim(($deuda->matricula?->grado?->nombre ?? '-') . ' ' . ($deuda->matricula?->seccion?->nombre ?? '')),
                $deuda->concepto?->nombre ?? '-',
                $deuda->periodo ?? '-',
                (float) $deuda->monto,
                (float) $deuda->saldo,
                $deuda->fecha_vencimiento ? Carbon::parse($deuda->fecha_vencimiento)->format('d/m/Y') : '-',
                $diasAtraso ?? 0,
                $this->riesgoLabel($riesgoKey),
                $deuda->estado ?? '-',
            ];
        }

        return $this->exportXlsx(
            $titulo,
            $headers,
            $rows,
            'REPORTE_MOROSOS_' . now()->format('d-m-Y') . '.xlsx',
            [6, 7]
        );
    }

    public function alumno(Request $request)
    {
        $filters = $this->normalizeAlumnoFilters($request);
        $dni = $filters['dni'];

        $estudiante = null;
        $matricula = null;
        $deudas = collect();
        $pagos = collect();
        $resumen = null;

        if ($dni !== '') {
            $estudiante = Estudiante::where('dni', $dni)->first();

            if ($estudiante) {
                $matricula = $estudiante->matriculas()
                    ->with(['grado', 'seccion', 'anioEscolar'])
                    ->latest()
                    ->first();

                $deudasQuery = Deuda::query()
                    ->with(['concepto', 'anioEscolar', 'matricula'])
                    ->whereHas('matricula', function ($query) use ($estudiante) {
                        $query->where('estudiante_id', $estudiante->id);
                    });

                if ($filters['estado_deuda'] !== 'todos') {
                    $deudasQuery->where('estado', $filters['estado_deuda']);
                }
                if ($filters['fecha_desde']) {
                    $deudasQuery->whereDate('fecha_vencimiento', '>=', $filters['fecha_desde']);
                }
                if ($filters['fecha_hasta']) {
                    $deudasQuery->whereDate('fecha_vencimiento', '<=', $filters['fecha_hasta']);
                }

                $deudas = $deudasQuery
                    ->orderByRaw('fecha_vencimiento is null')
                    ->orderBy('fecha_vencimiento')
                    ->orderByDesc('id')
                    ->get()
                    ->map(function (Deuda $deuda) {
                        $diasAtraso = $this->diasAtraso($deuda->fecha_vencimiento);
                        $riesgoKey = $this->riesgoPorDias($diasAtraso);
                        $deuda->dias_atraso = $diasAtraso;
                        $deuda->riesgo_key = $riesgoKey;
                        $deuda->riesgo_label = $this->riesgoLabel($riesgoKey);
                        return $deuda;
                    });

                $pagosQuery = Pago::query()
                    ->with(['deudas.concepto', 'deudas.matricula', 'registradoPor'])
                    ->whereHas('deudas.matricula', function ($query) use ($estudiante) {
                        $query->where('estudiante_id', $estudiante->id);
                    });

                if ($filters['estado_pago'] !== 'todos') {
                    $pagosQuery->where('estado', $filters['estado_pago']);
                }
                if ($filters['fecha_desde']) {
                    $pagosQuery->whereDate('fecha_pago', '>=', $filters['fecha_desde']);
                }
                if ($filters['fecha_hasta']) {
                    $pagosQuery->whereDate('fecha_pago', '<=', $filters['fecha_hasta']);
                }

                $pagos = $pagosQuery
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->get();

                $totalMonto = (float) $deudas->sum('monto');
                $totalSaldo = (float) $deudas->sum('saldo');
                $totalPagado = max($totalMonto - $totalSaldo, 0);
                $deudasPendientes = $deudas->whereIn('estado', ['pendiente', 'parcial'])->count();
                $vencidas = $deudas->filter(function ($deuda) {
                    return $deuda->fecha_vencimiento
                        && Carbon::parse($deuda->fecha_vencimiento)->lt(Carbon::today())
                        && (float) $deuda->saldo > 0;
                })->count();

                $ultimoPago = $pagos->first();
                $proximaDeuda = $deudas
                    ->filter(function ($deuda) {
                        return in_array($deuda->estado, ['pendiente', 'parcial'], true) && $deuda->fecha_vencimiento;
                    })
                    ->sortBy('fecha_vencimiento')
                    ->first();

                $maxDias = $deudas
                    ->map(function ($deuda) {
                        return $this->diasAtraso($deuda->fecha_vencimiento);
                    })
                    ->filter(function ($dias) {
                        return $dias !== null;
                    })
                    ->max();

                $resumen = [
                    'total_monto' => $totalMonto,
                    'total_saldo' => $totalSaldo,
                    'total_pagado' => $totalPagado,
                    'deudas_pendientes' => $deudasPendientes,
                    'deudas_vencidas' => $vencidas,
                    'pagos_registrados' => $pagos->count(),
                    'ultima_gestion' => $ultimoPago?->created_at?->format('d/m/Y H:i') ?? '-',
                    'responsable' => $ultimoPago?->registradoPor?->name ?? '-',
                    'proxima_accion' => $deudasPendientes > 0
                        ? 'Contactar al responsable y coordinar pago.'
                        : 'Cuenta sin deudas pendientes.',
                    'proxima_fecha' => $proximaDeuda?->fecha_vencimiento
                        ? Carbon::parse($proximaDeuda->fecha_vencimiento)->format('d/m/Y')
                        : '-',
                    'riesgo_key' => $this->riesgoPorDias($maxDias),
                    'riesgo_label' => $this->riesgoLabel($this->riesgoPorDias($maxDias)),
                ];
            }
        }

        return view('secretaria.reportes.alumno', compact(
            'filters',
            'dni',
            'estudiante',
            'matricula',
            'deudas',
            'pagos',
            'resumen'
        ));
    }

    public function alumnoPagosExcel(Request $request)
    {
        $filters = $this->normalizeAlumnoFilters($request);
        $dni = $filters['dni'];

        $estudiante = $dni !== '' ? Estudiante::where('dni', $dni)->first() : null;
        if (!$estudiante) {
            return redirect()->route('secretaria.reportes.alumno', ['dni' => $dni])
                ->with('status', 'Alumno no encontrado.');
        }

        $query = Pago::query()
            ->with(['deudas.concepto', 'deudas.matricula', 'registradoPor'])
            ->whereHas('deudas.matricula', function ($builder) use ($estudiante) {
                $builder->where('estudiante_id', $estudiante->id);
            });

        if ($filters['estado_pago'] !== 'todos') {
            $query->where('estado', $filters['estado_pago']);
        }
        if ($filters['fecha_desde']) {
            $query->whereDate('fecha_pago', '>=', $filters['fecha_desde']);
        }
        if ($filters['fecha_hasta']) {
            $query->whereDate('fecha_pago', '<=', $filters['fecha_hasta']);
        }

        $pagos = $query->orderByDesc('created_at')->orderByDesc('id')->get();

        $titulo = 'Pagos alumno ' . $estudiante->apellidos . ', ' . $estudiante->nombres . ' (' . $estudiante->dni . ')';
        $headers = ['Fecha', 'Alumno', 'Concepto', 'Monto', 'Metodo', 'Estado', 'Registrado por'];
        $rows = [];

        foreach ($pagos as $pago) {
            $deudasAlumno = $pago->deudas->filter(function ($deuda) use ($estudiante) {
                return $deuda->matricula && $deuda->matricula->estudiante_id === $estudiante->id;
            });
            $deuda = $deudasAlumno->first();
            $concepto = $deuda?->concepto?->nombre;
            $conceptoLabel = $deudasAlumno->count() > 1 ? 'Varios' : ($concepto ?? '-');

            $rows[] = [
                $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : '-',
                $estudiante->apellidos . ', ' . $estudiante->nombres,
                $conceptoLabel,
                (float) $pago->monto,
                $pago->metodo ?? '-',
                $pago->estado ?? '-',
                $pago->registradoPor->name ?? '-',
            ];
        }

        return $this->exportXlsx(
            $titulo,
            $headers,
            $rows,
            'REPORTE_PAGOS_ALUMNO_' . $estudiante->dni . '_' . now()->format('d-m-Y') . '.xlsx',
            [4]
        );
    }

    public function alumnoDeudasExcel(Request $request)
    {
        $filters = $this->normalizeAlumnoFilters($request);
        $dni = $filters['dni'];

        $estudiante = $dni !== '' ? Estudiante::where('dni', $dni)->first() : null;
        if (!$estudiante) {
            return redirect()->route('secretaria.reportes.alumno', ['dni' => $dni])
                ->with('status', 'Alumno no encontrado.');
        }

        $query = Deuda::query()
            ->with(['concepto', 'anioEscolar', 'matricula'])
            ->whereHas('matricula', function ($builder) use ($estudiante) {
                $builder->where('estudiante_id', $estudiante->id);
            });

        if ($filters['estado_deuda'] !== 'todos') {
            $query->where('estado', $filters['estado_deuda']);
        }
        if ($filters['fecha_desde']) {
            $query->whereDate('fecha_vencimiento', '>=', $filters['fecha_desde']);
        }
        if ($filters['fecha_hasta']) {
            $query->whereDate('fecha_vencimiento', '<=', $filters['fecha_hasta']);
        }

        $deudas = $query
            ->orderByRaw('fecha_vencimiento is null')
            ->orderBy('fecha_vencimiento')
            ->orderByDesc('id')
            ->get();

        $titulo = 'Deudas alumno ' . $estudiante->apellidos . ', ' . $estudiante->nombres . ' (' . $estudiante->dni . ')';
        $headers = ['Alumno', 'Concepto', 'Periodo', 'Monto', 'Saldo', 'Vencimiento', 'Dias atraso', 'Riesgo', 'Estado'];
        $rows = [];

        foreach ($deudas as $deuda) {
            $diasAtraso = $this->diasAtraso($deuda->fecha_vencimiento);
            $riesgoKey = $this->riesgoPorDias($diasAtraso);

            $rows[] = [
                $estudiante->apellidos . ', ' . $estudiante->nombres,
                $deuda->concepto?->nombre ?? '-',
                $deuda->periodo ?? '-',
                (float) $deuda->monto,
                (float) $deuda->saldo,
                $deuda->fecha_vencimiento ? Carbon::parse($deuda->fecha_vencimiento)->format('d/m/Y') : '-',
                $diasAtraso ?? 0,
                $this->riesgoLabel($riesgoKey),
                $deuda->estado ?? '-',
            ];
        }

        return $this->exportXlsx(
            $titulo,
            $headers,
            $rows,
            'REPORTE_DEUDAS_ALUMNO_' . $estudiante->dni . '_' . now()->format('d-m-Y') . '.xlsx',
            [4, 5]
        );
    }

    public function pensionesMensuales(Request $request)
    {
        $filters = $this->normalizePensionesFilters($request);

        $deudasBase = Deuda::query()
            ->with(['matricula.estudiante', 'concepto'])
            ->whereHas('concepto', function ($query) {
                $query->whereIn('nombre', ['Pension', 'Pension ', 'Pension Escolar', 'Pensión']);
            });

        $this->applyPensionesFilters($deudasBase, $filters);

        $deudas = $deudasBase->get();

        $resumen = $deudas
            ->groupBy(function (Deuda $deuda) {
                return $deuda->periodo ?: 'Sin periodo';
            })
            ->map(function ($items) {
                $estudiantes = $items->pluck('matricula.estudiante_id')->filter()->unique()->count();
                $monto = (float) $items->sum('monto');
                $saldo = (float) $items->sum('saldo');
                $cobrado = max($monto - $saldo, 0);
                $cobertura = $monto > 0 ? round(($cobrado / $monto) * 100, 2) : 0.0;

                return [
                    'estudiantes' => $estudiantes,
                    'monto' => $monto,
                    'saldo' => $saldo,
                    'cobrado' => $cobrado,
                    'cobertura' => $cobertura,
                ];
            });

        $resumen = $this->sortResumenPensiones($resumen, $filters['orden']);

        $kpis = [
            'periodos' => $resumen->count(),
            'estudiantes' => $deudas->pluck('matricula.estudiante_id')->filter()->unique()->count(),
            'monto_total' => (float) $deudas->sum('monto'),
            'saldo_total' => (float) $deudas->sum('saldo'),
        ];
        $kpis['cobrado_total'] = max($kpis['monto_total'] - $kpis['saldo_total'], 0);
        $kpis['cobertura_total'] = $kpis['monto_total'] > 0
            ? round(($kpis['cobrado_total'] / $kpis['monto_total']) * 100, 2)
            : 0.0;

        $perPage = 12;
        $page = (int) $request->input('page', 1);
        $resumenPaginado = new LengthAwarePaginator(
            $resumen->forPage($page, $perPage),
            $resumen->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('secretaria.reportes.pensiones', [
            'resumen' => $resumenPaginado,
            'filters' => $filters,
            'kpis' => $kpis,
            'fechaCorte' => now(),
        ]);
    }

    public function pensionesExcel(Request $request)
    {
        $filters = $this->normalizePensionesFilters($request);

        $deudasBase = Deuda::query()
            ->with(['matricula.estudiante', 'concepto'])
            ->whereHas('concepto', function ($query) {
                $query->whereIn('nombre', ['Pension', 'Pension ', 'Pension Escolar', 'Pensión']);
            });

        $this->applyPensionesFilters($deudasBase, $filters);

        $resumen = $deudasBase->get()
            ->groupBy(function (Deuda $deuda) {
                return $deuda->periodo ?: 'Sin periodo';
            })
            ->map(function ($items) {
                $estudiantes = $items->pluck('matricula.estudiante_id')->filter()->unique()->count();
                $monto = (float) $items->sum('monto');
                $saldo = (float) $items->sum('saldo');
                $cobrado = max($monto - $saldo, 0);
                $cobertura = $monto > 0 ? round(($cobrado / $monto) * 100, 2) : 0.0;

                return [
                    'estudiantes' => $estudiantes,
                    'monto' => $monto,
                    'saldo' => $saldo,
                    'cobrado' => $cobrado,
                    'cobertura' => $cobertura,
                ];
            });

        $resumen = $this->sortResumenPensiones($resumen, $filters['orden']);

        $titulo = 'Reporte de pensiones';
        $headers = ['Periodo', 'Estudiantes', 'Monto', 'Saldo', 'Cobrado', 'Cobertura %'];
        $rows = [];

        foreach ($resumen as $periodo => $data) {
            $rows[] = [
                $this->formatPeriodoLabel((string) $periodo),
                (int) $data['estudiantes'],
                (float) $data['monto'],
                (float) $data['saldo'],
                (float) $data['cobrado'],
                (float) $data['cobertura'],
            ];
        }

        return $this->exportXlsx(
            $titulo,
            $headers,
            $rows,
            'REPORTE_PENSIONES_' . now()->format('d-m-Y') . '.xlsx',
            [3, 4, 5, 6]
        );
    }

    private function normalizePagoFilters(Request $request, bool $defaultToday = false): array
    {
        $fechaDesdeInput = trim((string) $request->input('fecha_desde', ''));
        $fechaHastaInput = trim((string) $request->input('fecha_hasta', ''));

        if ($defaultToday && $fechaDesdeInput === '' && $fechaHastaInput === '') {
            $today = now()->toDateString();
            $fechaDesdeInput = $today;
            $fechaHastaInput = $today;
        }

        $fechaDesde = $this->parseDateInput($fechaDesdeInput);
        $fechaHasta = $this->parseDateInput($fechaHastaInput);

        if ($fechaDesde && $fechaHasta && $fechaDesde->gt($fechaHasta)) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $orden = trim((string) $request->input('orden', 'reciente'));
        $ordenPermitido = ['reciente', 'antiguo', 'monto_desc', 'monto_asc'];
        if (!in_array($orden, $ordenPermitido, true)) {
            $orden = 'reciente';
        }

        return [
            'dni' => trim((string) $request->input('dni', '')),
            'fecha_desde' => $fechaDesde?->toDateString(),
            'fecha_hasta' => $fechaHasta?->toDateString(),
            'metodo' => trim((string) $request->input('metodo', 'todos')),
            'estado' => trim((string) $request->input('estado', 'todos')),
            'monto_min' => $this->parseDecimal($request->input('monto_min')),
            'monto_max' => $this->parseDecimal($request->input('monto_max')),
            'orden' => $orden,
        ];
    }

    private function applyPagoFilters(Builder $query, array $filters): void
    {
        if ($filters['dni'] !== '') {
            $dni = $filters['dni'];
            $query->whereHas('deudas.matricula.estudiante', function ($builder) use ($dni) {
                $builder->where('dni', 'like', "%{$dni}%");
            });
        }

        if ($filters['fecha_desde']) {
            $query->whereDate('fecha_pago', '>=', $filters['fecha_desde']);
        }
        if ($filters['fecha_hasta']) {
            $query->whereDate('fecha_pago', '<=', $filters['fecha_hasta']);
        }

        if ($filters['metodo'] !== '' && $filters['metodo'] !== 'todos') {
            $metodo = $filters['metodo'];
            $query->where(function ($q) use ($metodo) {
                $q->where('metodo', $metodo)
                    ->orWhere('metodo', 'like', '%"metodo":"' . addcslashes($metodo, '%_\\') . '"%');
            });
        }
        if ($filters['estado'] !== '' && $filters['estado'] !== 'todos') {
            $query->where('estado', $filters['estado']);
        }
        if ($filters['monto_min'] !== null) {
            $query->where('monto', '>=', $filters['monto_min']);
        }
        if ($filters['monto_max'] !== null) {
            $query->where('monto', '<=', $filters['monto_max']);
        }
    }

    private function applyPagoSorting(Builder $query, string $orden): void
    {
        switch ($orden) {
            case 'antiguo':
                $query->orderBy('created_at')->orderBy('id');
                break;
            case 'monto_desc':
                $query->orderByDesc('monto')->orderByDesc('id');
                break;
            case 'monto_asc':
                $query->orderBy('monto')->orderByDesc('id');
                break;
            case 'reciente':
            default:
                $query->orderByDesc('created_at')->orderByDesc('id');
                break;
        }
    }

    private function normalizeMorosoFilters(Request $request): array
    {
        $fechaDesde = $this->parseDateInput($request->input('fecha_desde'));
        $fechaHasta = $this->parseDateInput($request->input('fecha_hasta'));

        if ($fechaDesde && $fechaHasta && $fechaDesde->gt($fechaHasta)) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $riesgo = trim((string) $request->input('riesgo', 'todos'));
        $riesgosPermitidos = ['todos', '1_7', '8_30', '31_60', '60_plus', 'sin_fecha'];
        if (!in_array($riesgo, $riesgosPermitidos, true)) {
            $riesgo = 'todos';
        }

        $orden = trim((string) $request->input('orden', 'vencimiento_asc'));
        $ordenPermitido = ['vencimiento_asc', 'vencimiento_desc', 'saldo_desc', 'saldo_asc', 'reciente'];
        if (!in_array($orden, $ordenPermitido, true)) {
            $orden = 'vencimiento_asc';
        }

        return [
            'dni' => trim((string) $request->input('dni', '')),
            'grado_id' => (int) $request->input('grado_id', 0) ?: null,
            'seccion_id' => (int) $request->input('seccion_id', 0) ?: null,
            'concepto_id' => (int) $request->input('concepto_id', 0) ?: null,
            'estado' => trim((string) $request->input('estado', 'todos')),
            'saldo_min' => $this->parseDecimal($request->input('saldo_min')),
            'saldo_max' => $this->parseDecimal($request->input('saldo_max')),
            'fecha_desde' => $fechaDesde?->toDateString(),
            'fecha_hasta' => $fechaHasta?->toDateString(),
            'solo_vencidos' => $request->boolean('solo_vencidos', false),
            'riesgo' => $riesgo,
            'orden' => $orden,
        ];
    }

    private function applyMorosoFilters(Builder $query, array $filters, bool $includeRiesgo): void
    {
        if ($filters['dni'] !== '') {
            $dni = $filters['dni'];
            $query->whereHas('matricula.estudiante', function ($builder) use ($dni) {
                $builder->where('dni', 'like', "%{$dni}%");
            });
        }

        if ($filters['grado_id']) {
            $gradoId = $filters['grado_id'];
            $query->whereHas('matricula', function ($builder) use ($gradoId) {
                $builder->where('grado_id', $gradoId);
            });
        }
        if ($filters['seccion_id']) {
            $seccionId = $filters['seccion_id'];
            $query->whereHas('matricula', function ($builder) use ($seccionId) {
                $builder->where('seccion_id', $seccionId);
            });
        }
        if ($filters['concepto_id']) {
            $query->where('concepto_pago_id', $filters['concepto_id']);
        }
        if ($filters['estado'] !== '' && $filters['estado'] !== 'todos') {
            $query->where('estado', $filters['estado']);
        }
        if ($filters['saldo_min'] !== null) {
            $query->where('saldo', '>=', $filters['saldo_min']);
        }
        if ($filters['saldo_max'] !== null) {
            $query->where('saldo', '<=', $filters['saldo_max']);
        }
        if ($filters['fecha_desde']) {
            $query->whereDate('fecha_vencimiento', '>=', $filters['fecha_desde']);
        }
        if ($filters['fecha_hasta']) {
            $query->whereDate('fecha_vencimiento', '<=', $filters['fecha_hasta']);
        }
        if ($filters['solo_vencidos']) {
            $query->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', Carbon::today()->toDateString());
        }

        if (!$includeRiesgo || $filters['riesgo'] === 'todos') {
            return;
        }

        $hoy = Carbon::today();
        switch ($filters['riesgo']) {
            case '1_7':
                $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [
                        $hoy->copy()->subDays(7)->toDateString(),
                        $hoy->copy()->subDay()->toDateString(),
                    ]);
                break;
            case '8_30':
                $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [
                        $hoy->copy()->subDays(30)->toDateString(),
                        $hoy->copy()->subDays(8)->toDateString(),
                    ]);
                break;
            case '31_60':
                $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [
                        $hoy->copy()->subDays(60)->toDateString(),
                        $hoy->copy()->subDays(31)->toDateString(),
                    ]);
                break;
            case '60_plus':
                $query->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->subDays(61)->toDateString());
                break;
            case 'sin_fecha':
                $query->whereNull('fecha_vencimiento');
                break;
        }
    }

    private function applyMorosoSorting(Builder $query, string $orden): void
    {
        switch ($orden) {
            case 'vencimiento_desc':
                $query->orderByDesc('fecha_vencimiento')->orderByDesc('id');
                break;
            case 'saldo_desc':
                $query->orderByDesc('saldo')->orderBy('fecha_vencimiento')->orderByDesc('id');
                break;
            case 'saldo_asc':
                $query->orderBy('saldo')->orderBy('fecha_vencimiento')->orderByDesc('id');
                break;
            case 'reciente':
                $query->orderByDesc('created_at')->orderByDesc('id');
                break;
            case 'vencimiento_asc':
            default:
                $query->orderByRaw('fecha_vencimiento is null')
                    ->orderBy('fecha_vencimiento')
                    ->orderByDesc('id');
                break;
        }
    }

    private function normalizePensionesFilters(Request $request): array
    {
        $fechaDesde = $this->parseDateInput($request->input('fecha_desde'));
        $fechaHasta = $this->parseDateInput($request->input('fecha_hasta'));
        if ($fechaDesde && $fechaHasta && $fechaDesde->gt($fechaHasta)) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $orden = trim((string) $request->input('orden', 'periodo_desc'));
        $ordenPermitido = ['periodo_desc', 'periodo_asc', 'saldo_desc', 'saldo_asc', 'cobertura_desc', 'cobertura_asc'];
        if (!in_array($orden, $ordenPermitido, true)) {
            $orden = 'periodo_desc';
        }

        return [
            'dni' => trim((string) $request->input('dni', '')),
            'periodo' => trim((string) $request->input('periodo', '')),
            'estado' => trim((string) $request->input('estado', 'todos')),
            'saldo_min' => $this->parseDecimal($request->input('saldo_min')),
            'saldo_max' => $this->parseDecimal($request->input('saldo_max')),
            'fecha_desde' => $fechaDesde?->toDateString(),
            'fecha_hasta' => $fechaHasta?->toDateString(),
            'orden' => $orden,
        ];
    }

    private function applyPensionesFilters(Builder $query, array $filters): void
    {
        if ($filters['dni'] !== '') {
            $dni = $filters['dni'];
            $query->whereHas('matricula.estudiante', function ($builder) use ($dni) {
                $builder->where('dni', 'like', "%{$dni}%");
            });
        }
        if ($filters['periodo'] !== '') {
            $periodo = $filters['periodo'];
            $query->where('periodo', 'like', "%{$periodo}%");
        }
        if ($filters['estado'] !== '' && $filters['estado'] !== 'todos') {
            $query->where('estado', $filters['estado']);
        }
        if ($filters['saldo_min'] !== null) {
            $query->where('saldo', '>=', $filters['saldo_min']);
        }
        if ($filters['saldo_max'] !== null) {
            $query->where('saldo', '<=', $filters['saldo_max']);
        }
        if ($filters['fecha_desde']) {
            $query->whereDate('fecha_vencimiento', '>=', $filters['fecha_desde']);
        }
        if ($filters['fecha_hasta']) {
            $query->whereDate('fecha_vencimiento', '<=', $filters['fecha_hasta']);
        }
    }

    private function sortResumenPensiones($resumen, string $orden)
    {
        switch ($orden) {
            case 'periodo_asc':
                return $resumen->sortKeys();
            case 'saldo_desc':
                return $resumen->sortByDesc('saldo');
            case 'saldo_asc':
                return $resumen->sortBy('saldo');
            case 'cobertura_desc':
                return $resumen->sortByDesc('cobertura');
            case 'cobertura_asc':
                return $resumen->sortBy('cobertura');
            case 'periodo_desc':
            default:
                return $resumen->sortKeysDesc();
        }
    }

    private function normalizeAlumnoFilters(Request $request): array
    {
        $fechaDesde = $this->parseDateInput($request->input('fecha_desde'));
        $fechaHasta = $this->parseDateInput($request->input('fecha_hasta'));
        if ($fechaDesde && $fechaHasta && $fechaDesde->gt($fechaHasta)) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        return [
            'dni' => trim((string) $request->input('dni', '')),
            'fecha_desde' => $fechaDesde?->toDateString(),
            'fecha_hasta' => $fechaHasta?->toDateString(),
            'estado_pago' => trim((string) $request->input('estado_pago', 'todos')),
            'estado_deuda' => trim((string) $request->input('estado_deuda', 'todos')),
        ];
    }

    private function parseDateInput(?string $value): ?Carbon
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], $raw);
        if (!is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function diasAtraso($fechaVencimiento): ?int
    {
        if (!$fechaVencimiento) {
            return null;
        }

        $vencimiento = Carbon::parse($fechaVencimiento)->startOfDay();
        $hoy = Carbon::today();

        if ($vencimiento->gt($hoy)) {
            return 0;
        }

        return $vencimiento->diffInDays($hoy);
    }

    private function riesgoPorDias(?int $diasAtraso): string
    {
        if ($diasAtraso === null) {
            return 'sin_fecha';
        }
        if ($diasAtraso <= 0) {
            return 'al_dia';
        }
        if ($diasAtraso <= 7) {
            return '1_7';
        }
        if ($diasAtraso <= 30) {
            return '8_30';
        }
        if ($diasAtraso <= 60) {
            return '31_60';
        }

        return '60_plus';
    }

    private function riesgoLabel(string $riesgo): string
    {
        return match ($riesgo) {
            'al_dia' => 'Al dia',
            '1_7' => 'Leve (1-7 dias)',
            '8_30' => 'Medio (8-30 dias)',
            '31_60' => 'Alto (31-60 dias)',
            '60_plus' => 'Critico (+60 dias)',
            default => 'Sin fecha',
        };
    }

    private function exportXlsx(string $titulo, array $headers, array $rows, string $filename, array $numberColumns = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnCount = count($headers);
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);

        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '8B0F14'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->fromArray($headers, null, 'A3');
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A4');
        }

        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F2F2F2'],
            ],
        ]);

        $sheet->setAutoFilter("A3:{$lastColumn}3");
        $sheet->freezePane('A4');

        $dataStartRow = 4;
        $dataEndRow = 3 + count($rows);
        if ($dataEndRow >= $dataStartRow && !empty($numberColumns)) {
            foreach ($numberColumns as $colIndex) {
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$dataEndRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }
        }

        for ($i = 1; $i <= $columnCount; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function formatPeriodoLabel(string $periodo): string
    {
        if ($periodo === 'Sin periodo') {
            return $periodo;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            return Carbon::createFromFormat('Y-m', $periodo)
                ->startOfMonth()
                ->format('d/m/Y');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodo)) {
            return Carbon::parse($periodo)->format('d/m/Y');
        }

        return $periodo;
    }
}
