<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\Deuda;
use App\Models\Matricula;
use App\Models\Tarifa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetiroController extends Controller
{
    private function routePrefix(): string
    {
        return auth()->user()?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
    }

    private const TIPO_CONCEPTO_RETIRO_TRASLADO = 'retiro_traslado';
    private const TIPO_CONCEPTO_RETIRO_FUT = 'retiro_fut';
    private const MONTO_TRASLADO_FALLBACK = 120.00;
    private const MONTO_FUT_FALLBACK = 1.00;
    private const PERIODO_TRASLADO = 'Retiro - Traslado';
    private const PERIODO_FUT = 'Retiro - F.U.T';

    public function index(Request $request)
    {
        $anioActivo = AnioEscolar::where('activo', true)->first();

        $query = Matricula::with(['estudiante', 'grado', 'seccion', 'anioEscolar', 'periodoAcademico', 'deudas'])
            ->where('estado', Matricula::ESTADO_RETIRADA);

        if ($anioActivo) {
            $query->where('anio_escolar_id', $anioActivo->id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('estudiante', function ($sub) use ($q) {
                $sub->where('dni', 'like', "%{$q}%")
                    ->orWhere('nombres', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%");
            });
        }

        // Stats para el año activo (sin paginar)
        $statsQuery = Matricula::with('deudas')
            ->where('estado', Matricula::ESTADO_RETIRADA);
        if ($anioActivo) {
            $statsQuery->where('anio_escolar_id', $anioActivo->id);
        }
        $todosRetirados = $statsQuery->get();

        $totalRetirados     = $todosRetirados->count();
        $totalDeudasAnuladas = $todosRetirados->sum(fn($m) => $m->deudas->where('estado', 'anulado')->count());
        $montoAnulado       = $todosRetirados->sum(fn($m) => $m->deudas->where('estado', 'anulado')->sum('saldo'));

        $retirados = $query->orderBy('fecha_retiro', 'desc')->paginate(15)->withQueryString();

        return view('secretaria.retiros.index', compact(
            'retirados',
            'anioActivo',
            'totalRetirados',
            'totalDeudasAnuladas',
            'montoAnulado'
        ));
    }

    public function create(Request $request)
    {
        // Sin matricula_id → flujo de búsqueda
        if (! $request->filled('matricula_id')) {
            $resultados = collect();
            $q = $request->input('q', '');

            if ($request->filled('q')) {
                $resultados = Matricula::with(['estudiante', 'grado', 'seccion', 'periodoAcademico'])
                    ->whereIn('estado', [Matricula::ESTADO_ACTIVA, Matricula::ESTADO_REGISTRADA])
                    ->whereHas('estudiante', function ($sub) use ($q) {
                        $sub->where('dni', 'like', "%{$q}%")
                            ->orWhere('nombres', 'like', "%{$q}%")
                            ->orWhere('apellidos', 'like', "%{$q}%");
                    })
                    ->orderByDesc('id')
                    ->limit(20)
                    ->get();
            }

            return view('secretaria.retiros.create', compact('resultados', 'q'));
        }

        // Con matricula_id → flujo de confirmación
        $request->validate(['matricula_id' => 'required|exists:matriculas,id']);

        $matricula = Matricula::with([
            'estudiante',
            'grado',
            'seccion',
            'anioEscolar',
            'periodoAcademico',
            'deudas.concepto',
        ])->findOrFail($request->matricula_id);

        if ($matricula->estado === Matricula::ESTADO_RETIRADA) {
            return redirect()->route($this->routePrefix() . 'retiros.index')
                ->with('info', 'El alumno ya fue retirado el ' . $matricula->fecha_retiro?->format('d/m/Y') . '.');
        }

        $conceptosRetiroIds = $this->obtenerIdsConceptosRetiro();
        $pensionesAnular = $matricula->deudas
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->filter(function ($d) use ($conceptosRetiroIds) {
                return !in_array((int) $d->concepto_pago_id, $conceptosRetiroIds, true);
            })
            ->sortBy(function ($d) {
                return [
                    $d->fecha_vencimiento ? 0 : 1,
                    $d->fecha_vencimiento ? (is_string($d->fecha_vencimiento) ? $d->fecha_vencimiento : $d->fecha_vencimiento->toDateString()) : '9999-12-31',
                    (string) $d->periodo,
                ];
            })
            ->values();

        $cobrosRetiro = $this->obtenerCobrosRetiro($matricula);

        return view('secretaria.retiros.create', compact('matricula', 'pensionesAnular', 'cobrosRetiro'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula_id'  => 'required|exists:matriculas,id',
            'fecha_retiro'  => 'required|date',
            'motivo_retiro' => 'required|string|max:500',
        ]);

        $matricula = Matricula::with(['deudas', 'estudiante', 'anioEscolar', 'grado'])->findOrFail($request->matricula_id);

        if ($matricula->estado === Matricula::ESTADO_RETIRADA) {
            return redirect()->route($this->routePrefix() . 'retiros.index')
                ->with('info', 'El alumno ya fue retirado anteriormente.');
        }

        DB::transaction(function () use ($request, $matricula) {
            $fechaRetiro = Carbon::parse($request->fecha_retiro);

            // 1. Marcar matrícula como RETIRADA
            $matricula->update([
                'estado'        => Matricula::ESTADO_RETIRADA,
                'fecha_retiro'  => $fechaRetiro->toDateString(),
                'motivo_retiro' => $request->motivo_retiro,
            ]);

            // 2. Anular deudas pendientes/parciales (se conservan deudas pagadas)
            $this->anularDeudasNoRetiro($matricula);

            // 3. Generar cobros del retiro (F.U.T y Traslado)
            $this->registrarCobrosRetiro($matricula, $fechaRetiro);
        });

        $nombre = trim($matricula->estudiante?->nombres . ' ' . $matricula->estudiante?->apellidos);

        return redirect()->route($this->routePrefix() . 'retiros.index')
            ->with('success', "Retiro registrado correctamente para {$nombre}. Los pagos realizados permanecen intactos y se generaron los cobros de retiro.");
    }

    private function obtenerIdsConceptosRetiro(): array
    {
        return ConceptoPago::whereIn('tipo', [
            self::TIPO_CONCEPTO_RETIRO_TRASLADO,
            self::TIPO_CONCEPTO_RETIRO_FUT,
        ])->pluck('id')->map(fn($id) => (int) $id)->all();
    }

    private function anularDeudasNoRetiro(Matricula $matricula): void
    {
        $conceptosRetiroIds = $this->obtenerIdsConceptosRetiro();

        $query = $matricula->deudas()
            ->whereIn('estado', ['pendiente', 'parcial']);

        if (!empty($conceptosRetiroIds)) {
            $query->whereNotIn('concepto_pago_id', $conceptosRetiroIds);
        }

        $query->update(['estado' => 'anulado']);
    }

    private function obtenerCobrosRetiro(Matricula $matricula): array
    {
        $matricula->loadMissing(['anioEscolar', 'grado']);

        $conceptoFut = $this->obtenerConceptoRetiro(
            self::TIPO_CONCEPTO_RETIRO_FUT,
            'F.U.T',
            self::MONTO_FUT_FALLBACK
        );

        $cobros = [
            [
                'tipo' => self::TIPO_CONCEPTO_RETIRO_FUT,
                'nombre' => $conceptoFut->nombre ?? 'F.U.T',
                'concepto_pago_id' => $conceptoFut->id,
                'periodo' => self::PERIODO_FUT,
                'monto' => $this->resolverMontoDesdeTarifa(
                    $matricula,
                    $conceptoFut,
                    self::MONTO_FUT_FALLBACK
                ),
            ],
        ];

        if ($this->aplicaTraslado($matricula)) {
            $conceptoTraslado = $this->obtenerConceptoRetiro(
                self::TIPO_CONCEPTO_RETIRO_TRASLADO,
                'Traslado',
                self::MONTO_TRASLADO_FALLBACK
            );

            array_unshift($cobros, [
                'tipo' => self::TIPO_CONCEPTO_RETIRO_TRASLADO,
                'nombre' => $conceptoTraslado->nombre ?? 'Traslado',
                'concepto_pago_id' => $conceptoTraslado->id,
                'periodo' => self::PERIODO_TRASLADO,
                'monto' => $this->resolverMontoDesdeTarifa(
                    $matricula,
                    $conceptoTraslado,
                    self::MONTO_TRASLADO_FALLBACK
                ),
            ]);
        }

        return $cobros;
    }

    private function registrarCobrosRetiro(Matricula $matricula, Carbon $fechaRetiro): void
    {
        foreach ($this->obtenerCobrosRetiro($matricula) as $cobro) {
            $monto = round((float) ($cobro['monto'] ?? 0), 2);
            if ($monto <= 0) {
                continue;
            }

            $conceptoId = (int) ($cobro['concepto_pago_id'] ?? 0);
            if ($conceptoId <= 0) {
                continue;
            }

            Deuda::firstOrCreate(
                [
                    'matricula_id' => $matricula->id,
                    'concepto_pago_id' => $conceptoId,
                ],
                [
                    'anio_escolar_id' => $matricula->anio_escolar_id,
                    'periodo_academico_id' => $matricula->periodo_academico_id,
                    'periodo' => (string) $cobro['periodo'],
                    'monto' => $monto,
                    'saldo' => $monto,
                    'fecha_vencimiento' => $fechaRetiro->toDateString(),
                    'estado' => 'pendiente',
                ]
            );
        }
    }

    private function obtenerConceptoRetiro(string $tipo, string $nombre, float $montoBase): ConceptoPago
    {
        return ConceptoPago::updateOrCreate(
            ['tipo' => $tipo],
            [
                'nombre' => $nombre,
                'monto_base' => round($montoBase, 2),
                'activo' => true,
            ]
        );
    }

    private function resolverMontoDesdeTarifa(Matricula $matricula, ConceptoPago $concepto, float $fallback): float
    {
        $anioId = (int) $matricula->anio_escolar_id;
        $nivel = trim((string) ($matricula->grado?->nivel ?? ''));
        $nivelGeneral = 'General';

        if ($anioId > 0) {
            $baseQuery = Tarifa::where('anio_escolar_id', $anioId)
                ->where('concepto_pago_id', $concepto->id)
                ->where('activo', 1);

            $tarifa = null;

            if ($nivel !== '') {
                $tarifa = (clone $baseQuery)->where('nivel', $nivel)->where('mes', 0)->first()
                    ?? (clone $baseQuery)->where('nivel', $nivel)->first();
            }

            if (! $tarifa) {
                $tarifa = (clone $baseQuery)->where('nivel', $nivelGeneral)->where('mes', 0)->first()
                    ?? (clone $baseQuery)->where('nivel', $nivelGeneral)->first();
            }

            if (! $tarifa) {
                $tarifa = (clone $baseQuery)->where('mes', 0)->first()
                    ?? (clone $baseQuery)->first();
            }

            if ($tarifa) {
                return round((float) $tarifa->monto, 2);
            }
        }

        $montoBase = round((float) ($concepto->monto_base ?? 0), 2);
        if ($montoBase > 0) {
            return $montoBase;
        }

        return round($fallback, 2);
    }

    private function aplicaTraslado(Matricula $matricula): bool
    {
        $nombreAnio = (string) ($matricula->anioEscolar?->nombre ?? '');

        if (preg_match('/(20\d{2})/', $nombreAnio, $match)) {
            return ((int) $match[1]) >= 2025;
        }

        // Fallback: si no se puede leer el anio, aplicar traslado por defecto.
        return true;
    }

    public function show(Matricula $matricula)
    {
        if ($matricula->estado !== Matricula::ESTADO_RETIRADA) {
            return redirect()->route('secretaria.retiros.index')
                ->with('info', 'Esta matrícula no corresponde a un retiro registrado.');
        }

        $matricula->load([
            'estudiante',
            'grado',
            'seccion',
            'anioEscolar',
            'periodoAcademico',
            'deudas.concepto',
        ]);

        $deudasAnuladas = $matricula->deudas->where('estado', 'anulado')->sortBy('fecha_vencimiento')->values();
        $deudasPagadas  = $matricula->deudas->where('estado', 'pagado')->values();
        $deudasPendientes = $matricula->deudas->where('estado', 'pendiente')->values();

        return view('secretaria.retiros.show', compact(
            'matricula',
            'deudasAnuladas',
            'deudasPagadas',
            'deudasPendientes'
        ));
    }
}
