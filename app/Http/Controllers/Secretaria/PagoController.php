<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\Deuda;
use App\Models\Matricula;
use App\Models\MatriculaDescuento;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use App\Models\Tarifa;
use App\Models\Voucher;
use App\Services\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pago.ver')->only(['index', 'show']);
        $this->middleware('permission:pago.registrar')->only([
            'create', 'store', 'buscarEstudiante', 'deudasEstudiante',
        ]);
        $this->middleware('permission:pago.editar')->only(['edit', 'update', 'destroyVoucher']);
        $this->middleware('permission:pago.anular')->only(['destroy', 'anular']);
    }

    private function routePrefix(): string
    {
        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();
        return $authUser?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
    }

    // ──────────────────────────────────────────────────────────────
    // Index
    // ──────────────────────────────────────────────────────────────

    public function index()
    {
        $dni = request()->input('dni');

        $pagos = Pago::with(['deudas.matricula.estudiante', 'registradoPor'])
            ->when($dni, function ($query) use ($dni) {
                $query->whereHas('deudas.matricula.estudiante', function ($q) use ($dni) {
                    $q->where('dni', 'like', "%{$dni}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'       => Pago::count(),
            'confirmados' => Pago::where('estado', 'confirmado')->count(),
            'pendientes'  => Pago::where('estado', 'pendiente')->count(),
            'ingresos'    => Pago::where('estado', 'confirmado')->sum('monto'),
            'hoy'         => Pago::whereDate('fecha_pago', Carbon::today())->count(),
        ];

        // Calcular tag de hermanos para todos los estudiantes de la página actual
        $pairs = $pagos->flatMap(function ($pago) {
            return $pago->deudas->map(function ($d) use ($pago) {
                $eid = $d->matricula?->estudiante_id;
                return $eid ? ['estudiante_id' => $eid, 'anio_id' => $pago->anio_escolar_id] : null;
            })->filter();
        })->unique(fn($p) => $p['estudiante_id'] . '-' . $p['anio_id'])->values()->toArray();

        $hermanoTags = $this->hermanoTagsBulk($pairs);

        return view('secretaria.pagos.index', compact('pagos', 'stats', 'hermanoTags'));
    }

    // ──────────────────────────────────────────────────────────────
    // Create / Store
    // ──────────────────────────────────────────────────────────────

    public function create()
    {
        $anios = AnioEscolar::orderByDesc('fecha_inicio')->get();
        return view('secretaria.pagos.create', compact('anios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id'          => ['required', 'exists:anios_escolares,id'],
            'deuda_ids'                => ['required', 'array', 'min:1'],
            'deuda_ids.*'              => ['required', 'integer', 'exists:deudas,id'],
            'deuda_amounts'            => ['nullable', 'array'],
            'deuda_amounts.*'          => ['nullable', 'numeric', 'min:0.01'],
            'descuento_hermano_monto'  => ['nullable', 'numeric', 'min:0'],
            'fecha_pago'               => ['required', 'date'],
            'metodos'                  => ['required', 'array', 'min:1'],
            'metodos.*.metodo'         => ['required', 'string', 'in:Efectivo,Yape,Plin,Transferencia,Tarjeta,BancoNacion,Otro'],
            'metodos.*.monto'          => ['required', 'numeric', 'min:0.01'],
            'metodos.*.op'             => ['nullable', 'string', 'max:60'],
            'comentario'               => ['nullable', 'string'],
            'voucher_archivo'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        // Verificar que todas las deudas estén pendientes/parciales
        $deudas = Deuda::whereIn('id', $data['deuda_ids'])
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->get();

        if ($deudas->count() !== count($data['deuda_ids'])) {
            return back()->withInput()
                ->withErrors(['deuda_ids' => 'Una o más deudas seleccionadas no son válidas o ya fueron pagadas.']);
        }

        // Descuento hermano menor: distribuir sobre deudas de pensión seleccionadas
        $descuentoTotal = (float) ($data['descuento_hermano_monto'] ?? 0);
        $amounts        = $data['deuda_amounts'] ?? [];

        if ($descuentoTotal > 0) {
            $deudas->load('concepto');
            $deudasPension = $deudas->filter(fn($d) =>
                in_array($d->concepto?->tipo ?? '', ['mensual', 'pension'])
            );

            if ($deudasPension->isNotEmpty()) {
                // Distribuir descuento proporcionalmente al saldo de cada pensión
                $totalPension   = $deudasPension->sum(fn($d) => (float) $d->saldo);
                $descuentoReal  = min($descuentoTotal, $totalPension);

                foreach ($deudasPension as $dp) {
                    $proporcion  = $totalPension > 0 ? ((float) $dp->saldo / $totalPension) : 0;
                    $descuentoDp = round($descuentoReal * $proporcion, 2);
                    if ($descuentoDp <= 0) continue;

                    $nuevoMonto = max(0, round((float) $dp->monto - $descuentoDp, 2));
                    $nuevoSaldo = max(0, round((float) $dp->saldo - $descuentoDp, 2));
                    $dp->update(['monto' => $nuevoMonto, 'saldo' => $nuevoSaldo]);

                    // Ajustar el amount enviado por el cliente al nuevo saldo
                    $amounts[$dp->id] = min(
                        isset($amounts[$dp->id]) ? (float) $amounts[$dp->id] : $nuevoSaldo,
                        $nuevoSaldo
                    );
                }
            }
        }

        // Calcular monto real según los montos parciales enviados (ya con descuento aplicado)
        $monto = $deudas->sum(function ($deuda) use ($amounts) {
            $solicitado = isset($amounts[$deuda->id]) ? (float) $amounts[$deuda->id] : (float) $deuda->saldo;
            return min($solicitado, (float) $deuda->saldo);
        });

        // Validar que la suma de los métodos de pago coincida con el monto real
        // (evita registrar, p. ej., "Efectivo: 0.01" cuando la deuda se saldó por S/150)
        $metodosData  = $data['metodos'];
        $sumaMetodos  = round(array_sum(array_column($metodosData, 'monto')), 2);
        if (abs($sumaMetodos - round($monto, 2)) > 0.01) {
            return back()->withInput()->withErrors([
                'metodos' => sprintf(
                    'La suma de los métodos de pago (S/ %s) no coincide con el monto a pagar (S/ %s).',
                    number_format($sumaMetodos, 2),
                    number_format($monto, 2)
                ),
            ]);
        }

        // Preparar JSON de métodos y primer número de operación
        $metodosJson = json_encode(array_values(array_map(fn($m) => array_filter([
            'metodo' => $m['metodo'],
            'monto'  => round((float) $m['monto'], 2),
            'op'     => isset($m['op']) && $m['op'] !== '' ? trim($m['op']) : null,
        ], fn($v) => $v !== null), $metodosData)));
        $primerOp = collect($metodosData)->first(fn($m) => !empty($m['op']))['op'] ?? null;

        $pago = DB::transaction(function () use ($data, $deudas, $amounts, $monto, $descuentoTotal, $request, $metodosJson, $primerOp) {
            $pago = Pago::create([
                'anio_escolar_id'     => $data['anio_escolar_id'],
                'monto'               => $monto,
                'fecha_pago'          => $data['fecha_pago'],
                'metodo'              => $metodosJson,
                'numero_operacion'    => $primerOp ? trim($primerOp) : null,
                'estado'              => 'confirmado',
                'usuario_registra_id' => $request->user()->id,
                'comentario'          => $data['comentario'] ?? null,
            ]);

            if ($request->hasFile('voucher_archivo')) {
                $path = $request->file('voucher_archivo')->store('vouchers', 'public');
                Voucher::create([
                    'pago_id'           => $pago->id,
                    'archivo_path'      => $path,
                    'estado'            => 'validado',
                    'usuario_valida_id' => $request->user()->id,
                ]);
            }

            // Registrar descuento en MatriculaDescuento para trazabilidad
            if ($descuentoTotal > 0) {
                $deudasPension = $deudas->filter(fn($d) =>
                    in_array($d->concepto?->tipo ?? '', ['mensual', 'pension'])
                );
                $matriculaId = $deudasPension->first()?->matricula_id;
                if ($matriculaId) {
                    MatriculaDescuento::create([
                        'matricula_id' => $matriculaId,
                        'tipo'         => 'hermano_menor',
                        'monto'        => $descuentoTotal,
                        'descripcion'  => 'Descuento hermano menor aplicado en pago #' . $pago->id,
                    ]);
                }
            }

            $this->aplicarPagoADeudas($pago, $deudas, $amounts);

            AuditoriaService::log(
                $request->user()?->id,
                'crear',
                'pagos',
                $pago->id,
                ['deuda_ids' => $deudas->pluck('id')->toArray(), 'monto' => $monto, 'descuento_hermano' => $descuentoTotal],
                $request
            );

            return $pago;
        });

        return redirect()->route($this->routePrefix() . 'pagos.voucher', $pago);
    }

    // ──────────────────────────────────────────────────────────────
    // Show
    // ──────────────────────────────────────────────────────────────

    public function show(Pago $pago)
    {
        $pago->load(['deudas.matricula.estudiante', 'deudas.concepto', 'registradoPor']);
        return view('secretaria.pagos.show', compact('pago'));
    }

    // ──────────────────────────────────────────────────────────────
    // Edit / Update
    // ──────────────────────────────────────────────────────────────

    public function edit(Pago $pago)
    {
        $pago->load(['deudas.concepto', 'deudas.matricula.estudiante', 'registradoPor', 'vouchers']);
        return view('secretaria.pagos.edit', compact('pago'));
    }

    public function update(Request $request, Pago $pago)
    {
        $request->validate([
            'metodo'             => ['nullable', 'string', 'max:50'],
            'numero_operacion'   => ['nullable', 'string', 'max:60'],
            'fecha_pago'         => ['required', 'date'],
            'estado'             => ['required', 'string', 'in:confirmado,pendiente,anulado'],
            'comentario'         => ['nullable', 'string'],
            'montos_deuda'       => ['nullable', 'array'],
            'montos_deuda.*'     => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'voucher_archivo'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $estadoAnterior = $pago->estado;
        $deudasPrevias  = $pago->deudas; // con pivot cargado
        $nuevoEstado    = $request->input('estado');
        $montosRaw      = $request->input('montos_deuda', []);
        $montosDeuda    = array_filter(
            array_map('floatval', $montosRaw),
            fn($m) => $m > 0
        );

        // 1. Revertir efectos anteriores del pago
        if ($estadoAnterior === 'confirmado') {
            // Restaura saldos de deudas y desvincula el pivot
            $this->revertirPago($pago, $deudasPrevias, $estadoAnterior);
        } else {
            // Solo desvincular pivot sin tocar saldos
            $pago->deudas()->detach();
        }

        // 2. Calcular nuevo monto total
        $nuevoMonto = !empty($montosDeuda)
            ? round(array_sum($montosDeuda), 2)
            : (float) $pago->monto;

        // Número de operación: requerido para Yape / Plin / Transferencia
        $metodoNuevo = $request->input('metodo');
        $metodosConOp = ['Yape', 'Plin', 'Transferencia', 'Tarjeta', 'BancoNacion'];
        $numOp = in_array($metodoNuevo, $metodosConOp)
            ? trim((string) $request->input('numero_operacion', ''))
            : null;

        // 3. Actualizar datos del pago
        $pago->update([
            'metodo'           => $metodoNuevo,
            'numero_operacion' => $numOp ?: null,
            'fecha_pago'       => $request->input('fecha_pago'),
            'estado'           => $nuevoEstado,
            'comentario'       => $request->input('comentario'),
            'monto'            => $nuevoMonto,
        ]);

        // 4. Re-vincular deudas con los nuevos montos
        if ($nuevoEstado === 'confirmado') {
            // Recargar deudas frescas (saldos ya restaurados tras revertirPago)
            $deudaIds = !empty($montosDeuda)
                ? array_keys($montosDeuda)
                : $deudasPrevias->pluck('id')->all();
            $deudas = Deuda::whereIn('id', $deudaIds)->get();
            $this->aplicarPagoADeudas($pago, $deudas, $montosDeuda);
        } else {
            // Estado no confirmado: vincular pivot sin afectar saldos
            if (!empty($montosDeuda)) {
                foreach ($montosDeuda as $deudaId => $monto) {
                    $pago->deudas()->attach((int) $deudaId, ['monto_aplicado' => $monto]);
                }
            } else {
                // Restaurar vínculos originales
                foreach ($deudasPrevias as $deuda) {
                    $pago->deudas()->attach($deuda->id, ['monto_aplicado' => $deuda->pivot->monto_aplicado]);
                }
            }
        }

        // 5. Subir nuevo voucher si se adjuntó
        if ($request->hasFile('voucher_archivo')) {
            $path = $request->file('voucher_archivo')->store('vouchers', 'public');
            Voucher::create([
                'pago_id'           => $pago->id,
                'archivo_path'      => $path,
                'estado'            => 'validado',
                'usuario_valida_id' => $request->user()->id,
            ]);
        }

        AuditoriaService::log($request->user()?->id, 'editar', 'pagos', $pago->id, [
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado,
            'monto_nuevo'     => $nuevoMonto,
        ], $request);

        return redirect()
            ->route($this->routePrefix() . 'pagos.edit', $pago)
            ->with('status', 'Pago actualizado correctamente.');
    }

    public function destroyVoucher(Request $request, Pago $pago, Voucher $voucher)
    {
        abort_if($voucher->pago_id !== $pago->id, 403);

        Storage::disk('public')->delete($voucher->archivo_path);
        $voucher->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar_voucher', 'vouchers', $voucher->id, null, $request);

        return back()->with('status', 'Comprobante eliminado correctamente.');
    }

    // ──────────────────────────────────────────────────────────────
    // Anular
    // ──────────────────────────────────────────────────────────────

    public function anular(Request $request, Pago $pago)
    {
        if ($pago->estado === 'anulado') {
            return back()->with('error', 'Este pago ya fue anulado.');
        }

        $estadoAnterior = $pago->estado;
        $deudasPrevias  = $pago->deudas;

        $pago->update([
            'estado'    => 'anulado',
            'comentario' => trim(($pago->comentario ? $pago->comentario . ' | ' : '') . 'ANULADO: ' . ($request->input('motivo') ?? 'Sin motivo')),
        ]);

        $this->revertirPago($pago, $deudasPrevias, $estadoAnterior);

        AuditoriaService::log(
            $request->user()?->id,
            'anular',
            'pagos',
            $pago->id,
            ['motivo' => $request->input('motivo'), 'estado_anterior' => $estadoAnterior],
            $request
        );

        return back()->with('status', 'Pago #' . $pago->id . ' anulado correctamente.');
    }

    // ──────────────────────────────────────────────────────────────
    // Destroy
    // ──────────────────────────────────────────────────────────────

    public function destroy(Request $request, Pago $pago)
    {
        $estadoAnterior = $pago->estado;
        $deudasPrevias  = $pago->deudas;
        $pago->delete();

        $this->revertirPago($pago, $deudasPrevias, $estadoAnterior);

        AuditoriaService::log($request->user()?->id, 'eliminar', 'pagos', $pago->id, null, $request);

        return redirect()->route($this->routePrefix() . 'pagos.index')
            ->with('status', 'Pago eliminado.');
    }

    // ──────────────────────────────────────────────────────────────
    // Voucher
    // ──────────────────────────────────────────────────────────────

    public function voucher(Pago $pago)
    {
        $pago->load([
            'registradoPor',
            'deudas.concepto',
            'deudas.matricula.estudiante',
            'deudas.matricula.grado',
            'deudas.matricula.seccion',
        ]);

        return view('secretaria.pagos.voucher', compact('pago'));
    }

    // ──────────────────────────────────────────────────────────────
    // AJAX: historial de pagos de un estudiante
    // ──────────────────────────────────────────────────────────────

    public function historialEstudiante(Request $request): JsonResponse
    {
        $estudianteId = (int) $request->input('estudiante_id');
        $anioId       = (int) $request->input('anio_escolar_id');

        if ($estudianteId <= 0) {
            return response()->json([]);
        }

        $pagos = Pago::with(['deudas.concepto', 'registradoPor'])
            ->whereHas('deudas.matricula', fn($q) => $q
                ->where('estudiante_id', $estudianteId)
                ->when($anioId > 0, fn($q) => $q->where('anio_escolar_id', $anioId)))
            ->where('estado', 'confirmado')
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $rp = $this->routePrefix();

        return response()->json($pagos->map(fn($p) => [
            'id'             => $p->id,
            'fecha_pago'     => $p->fecha_pago ? $p->fecha_pago->format('d/m/Y') : '-',
            'monto'          => (float) $p->monto,
            'metodo'         => $p->metodo_display,
            'metodos'        => $p->metodos_decoded,
            'conceptos'      => $p->deudas->map(fn($d) => $d->concepto?->nombre)->filter()->unique()->values(),
            'periodos'       => $p->deudas->pluck('periodo')->filter()->unique()->values(),
            'registrado_por' => $p->registradoPor?->name ?? '-',
            'show_url'       => route($rp . 'pagos.show', $p),
            'voucher_url'    => route($rp . 'pagos.voucher', $p),
        ]));
    }

    // ──────────────────────────────────────────────────────────────
    // AJAX: buscar estudiante
    // ──────────────────────────────────────────────────────────────

    public function buscarEstudiante(Request $request): JsonResponse
    {
        $q      = trim($request->input('q', ''));
        $anioId = $request->input('anio_escolar_id');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $matriculas = Matricula::with(['estudiante', 'grado', 'seccion'])
            ->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA])
            ->when($anioId, fn($query) => $query->where('anio_escolar_id', $anioId))
            ->whereHas('estudiante', function ($query) use ($q) {
                $query->where('dni', 'like', $q . '%')
                      ->orWhere('nombres', 'like', '%' . $q . '%')
                      ->orWhere('apellidos', 'like', '%' . $q . '%');
            })
            ->limit(10)
            ->get();

        $results = $matriculas->map(function (Matricula $m) use ($anioId) {
            $e = $m->estudiante;
            return [
                'estudiante_id'   => $e->id,
                'matricula_id'    => $m->id,
                'dni'             => $e->dni,
                'nombres'         => $e->nombres,
                'apellidos'       => $e->apellidos,
                'nombre_completo' => $e->apellidos . ', ' . $e->nombres,
                'grado'           => $m->grado?->nombre ?? '-',
                'seccion'         => $m->seccion?->nombre ?? '-',
                'nivel'           => $m->grado?->nivel ?? '-',
                'hermano_tag'     => $anioId ? $this->hermanoTag($e->id, (int) $anioId) : null,
            ];
        });

        return response()->json($results);
    }

    // ──────────────────────────────────────────────────────────────
    // AJAX: deudas de un estudiante
    // ──────────────────────────────────────────────────────────────

    public function deudasEstudiante(Request $request): JsonResponse
    {
        $estudianteId = (int) $request->input('estudiante_id');
        $anioId       = (int) $request->input('anio_escolar_id');

        if ($estudianteId <= 0 || $anioId <= 0) {
            return response()->json([]);
        }
        // Mapa tipo DB → tipo frontend (para los filtros JS)
        $mapaFrontend = [
            'matricula'        => 'matricula',
            'gastos_internos'  => 'matricula',
            'mensual'          => 'pension',
            'pension'          => 'pension',
        ];

        $hoy = now()->toDateString();

        $deudas = Deuda::with(['concepto', 'matricula'])
            ->whereHas('matricula', fn($q) => $q
                ->where('estudiante_id', $estudianteId)
                ->where('anio_escolar_id', $anioId))
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->orderBy('fecha_vencimiento')
            ->orderBy('id')
            ->get();

        $results = $deudas->map(function (Deuda $d) use ($mapaFrontend, $hoy) {
            $tipoDb = $d->concepto?->tipo ?? '';
            $conceptoNombre = (string) ($d->concepto?->nombre ?? 'Concepto');
            $tipoFrontend = $mapaFrontend[$tipoDb] ?? 'otro';

            return [
                'id'                => $d->id,
                'concepto_nombre'   => $conceptoNombre,
                'concepto_tipo'     => $tipoFrontend,
                'concepto_tipo_db'  => $tipoDb,
                'periodo'           => $d->periodo,
                'monto'             => (float) $d->monto,
                'saldo'             => (float) $d->saldo,
                'estado'            => $d->estado,
                'fecha_vencimiento' => $d->fecha_vencimiento,
                'vencida'           => $d->fecha_vencimiento
                    ? ($d->fecha_vencimiento <= $hoy)
                    : false,
            ];
        });

        return response()->json($results);
    }

    // ──────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Aplica el pago a una colección de deudas.
     * Soporta pagos parciales: si $amounts[deuda_id] < saldo, la deuda queda en 'parcial'.
     */
    private function aplicarPagoADeudas(Pago $pago, $deudas, array $amounts = []): void
    {
        $deudas->loadMissing('concepto');

        foreach ($deudas as $deuda) {
            $saldoActual   = (float) $deuda->saldo;
            $montoAplicado = isset($amounts[$deuda->id])
                ? min((float) $amounts[$deuda->id], $saldoActual)
                : $saldoActual;

            if ($montoAplicado <= 0) continue;

            $pago->deudas()->syncWithoutDetaching([
                $deuda->id => ['monto_aplicado' => $montoAplicado],
            ]);

            $nuevoSaldo  = round(max(0, $saldoActual - $montoAplicado), 2);
            $nuevoEstado = $nuevoSaldo <= 0 ? 'pagado' : 'parcial';

            $deuda->update([
                'saldo'  => $nuevoSaldo,
                'estado' => $nuevoEstado,
            ]);
        }
    }

    /**
     * Revierte la aplicación de un pago (restaura saldo y estado de cada deuda).
     */
    private function revertirPago(Pago $pago, $deudasPrevias, ?string $estadoAnterior): void
    {
        if (! $deudasPrevias || $deudasPrevias->isEmpty()) {
            return;
        }

        if ($estadoAnterior !== 'confirmado') {
            $pago->deudas()->detach();
            return;
        }

        foreach ($deudasPrevias as $deuda) {
            $aplicado = (float) ($deuda->pivot->monto_aplicado ?? 0);
            if ($aplicado <= 0) {
                continue;
            }

            $saldoNuevo    = round($deuda->saldo + $aplicado, 2);
            $montoOriginal = (float) $deuda->monto;

            $estado = match(true) {
                $saldoNuevo >= $montoOriginal => 'pendiente',
                $saldoNuevo > 0              => 'parcial',
                default                      => 'pagado',
            };

            $deuda->update([
                'saldo'  => $saldoNuevo,
                'estado' => $estado,
            ]);
        }

        $pago->deudas()->detach();
    }

    // ──────────────────────────────────────────────────────────────
    // Hermanos: detección de tag H- / H+ / H
    // Prioriza la observación guardada en matrícula; si no existe, infiere por orden de grado.
    // ──────────────────────────────────────────────────────────────

    private function hermanoTag(int $estudianteId, int $anioId): ?string
    {
        $tags = $this->hermanoTagsBulk([['estudiante_id' => $estudianteId, 'anio_id' => $anioId]]);
        return $tags[$estudianteId][$anioId] ?? null;
    }

    /**
     * Calcula tags en bulk para minimizar consultas DB.
     * @param  array $pairs  [ ['estudiante_id' => X, 'anio_id' => Y], ... ]
     * @return array         [ estudiante_id ][ anio_id ] => 'H-'|'H+'|'H'|null
     */
    private function hermanoTagsBulk(array $pairs): array
    {
        if (empty($pairs)) return [];

        $resultado = [];

        foreach (collect($pairs)->groupBy('anio_id') as $anioId => $grupo) {
            $estudianteIds = $grupo->pluck('estudiante_id')->unique()->values()->toArray();

            // 1. Apoderados de los estudiantes consultados
            $apoderadoRows = DB::table('apoderado_estudiante')
                ->whereIn('estudiante_id', $estudianteIds)
                ->get(['estudiante_id', 'apoderado_id']);

            $apodByEst  = $apoderadoRows->groupBy('estudiante_id');
            $allApodIds = $apoderadoRows->pluck('apoderado_id')->unique();

            // 2. Todos los estudiantes que comparten esos apoderados
            $hermanoRows = DB::table('apoderado_estudiante')
                ->whereIn('apoderado_id', $allApodIds)
                ->get(['estudiante_id', 'apoderado_id']);

            $apodToStudents = $hermanoRows->groupBy('apoderado_id');

            // 3. Matrículas activas del año para todos los involucrados
            $allIds = $hermanoRows->pluck('estudiante_id')->unique();
            $matriculaMap = Matricula::whereIn('estudiante_id', $allIds)
                ->where('anio_escolar_id', $anioId)
                ->where('estado', '!=', 'retirada')
                ->with('grado')
                ->get()
                ->keyBy('estudiante_id');

            // 4. Calcular tag por estudiante
            foreach ($estudianteIds as $eid) {
                $miMatricula = $matriculaMap->get($eid);
                $observacionExplicita = $this->normalizarObservacionHermano($miMatricula?->observacion);

                if ($observacionExplicita !== null) {
                    $resultado[$eid][$anioId] = $observacionExplicita;
                    continue;
                }

                $misApods = $apodByEst->get($eid, collect())->pluck('apoderado_id');

                $hermanoIds = collect();
                foreach ($misApods as $apodId) {
                    $hermanoIds = $hermanoIds->concat(
                        $apodToStudents->get($apodId, collect())->pluck('estudiante_id')
                    );
                }
                $hermanoIds = $hermanoIds->unique()->filter(fn($id) => $id != $eid);
                $hermanosActivos = $hermanoIds->filter(fn($id) => $matriculaMap->has($id));

                if ($hermanosActivos->isEmpty()) {
                    $resultado[$eid][$anioId] = null;
                    continue;
                }

                if (!$miMatricula?->grado) {
                    $resultado[$eid][$anioId] = 'H';
                    continue;
                }

                $miOrden = $miMatricula->grado->orden;
                $ordenes = $hermanosActivos
                    ->map(fn($id) => $matriculaMap->get($id)?->grado?->orden)
                    ->filter()
                    ->values();

                if ($ordenes->isEmpty()) {
                    $resultado[$eid][$anioId] = 'H';
                } elseif ($miOrden < $ordenes->min()) {
                    $resultado[$eid][$anioId] = 'H-';
                } elseif ($miOrden > $ordenes->max()) {
                    $resultado[$eid][$anioId] = 'H+';
                } else {
                    $resultado[$eid][$anioId] = 'H';
                }
            }
        }

        return $resultado;
    }

    private function normalizarObservacionHermano(?string $observacion): ?string
    {
        $observacion = Str::upper(trim((string) $observacion));

        if ($observacion === '') {
            return null;
        }

        if (str_contains($observacion, 'H-')) {
            return 'H-';
        }

        if (str_contains($observacion, 'H+')) {
            return 'H+';
        }

        return preg_match('/(^|[^A-Z0-9])H($|[^A-Z0-9])/', $observacion) ? 'H' : null;
    }
}
