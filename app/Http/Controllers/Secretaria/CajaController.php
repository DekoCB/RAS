<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Caja;
use App\Models\Pago;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    // Denominaciones peruanas disponibles
    private const DENOMINACIONES = [
        '0.10', '0.20', '0.50',
        '1.00', '2.00', '5.00',
        '10.00', '20.00', '50.00', '100.00', '200.00',
    ];

    // Imágenes asociadas a cada denominación
    private const IMAGENES_DENOM = [
        '0.10'   => 'imagenes_caja/moneda_010.jpg',
        '0.20'   => 'imagenes_caja/moneda_020.jpeg',
        '0.50'   => 'imagenes_caja/moneda_050.jpg',
        '1.00'   => 'imagenes_caja/moneda_1.jpg',
        '2.00'   => 'imagenes_caja/moneda_2.jpg',
        '5.00'   => 'imagenes_caja/moneda_5.jpg',
        '10.00'  => 'imagenes_caja/billete_10.jpg',
        '20.00'  => 'imagenes_caja/billete_20.jpg',
        '50.00'  => 'imagenes_caja/billete_50.jpg',
        '100.00' => 'imagenes_caja/billete_100.jpg',
        '200.00' => 'imagenes_caja/billete_200.png',
    ];

    // Imágenes asociadas a métodos de pago digitales
    private const IMAGENES_METODO = [
        'Yape'          => 'imagenes_caja/yape.jpg',
        'Plin'          => 'imagenes_caja/plin.png',
        'Transferencia' => 'imagenes_caja/bbva.jpg',
        'Tarjeta'       => 'imagenes_caja/tarjeta.jpg',
        'Tarjeta BCP'   => 'imagenes_caja/tarjeta.jpg',
        'Otros'         => 'imagenes_caja/otros.png',
        'BancoNacion'   => 'imagenes_caja/logo-banco-nacion-peru.webp',
    ];

    public function index()
    {
        $cajaAbierta = Caja::where('usuario_id', Auth::id())
            ->where('estado', 'abierta')
            ->with('denominaciones')
            ->first();

        $anioActivo = AnioEscolar::where('activo', true)->first();

        $pagosHoy = null;
        $totalesPorMetodo = collect();
        $totalEfectivo = 0;
        $totalDigital = 0;
        $totalGeneral = 0;
        $cantidadPagos = 0;
        $pagosRecientes = collect();

        if ($cajaAbierta) {
            $pagosHoy = $cajaAbierta->getPagos()->load([
                'deudas.concepto',
                'deudas.matricula.estudiante',
            ]);
            $totalesPorMetodo = collect();
            foreach ($pagosHoy as $pago) {
                foreach ($pago->metodos_decoded as $m) {
                    $nombre = $m['metodo'] ?? 'Otro';
                    $totalesPorMetodo[$nombre] = ($totalesPorMetodo[$nombre] ?? 0) + (float) ($m['monto'] ?? 0);
                }
            }
            $totalEfectivo = (float) ($totalesPorMetodo->get('Efectivo', 0));
            $totalDigital  = (float) ($totalesPorMetodo->except('Efectivo')->sum());
            $totalGeneral  = $totalEfectivo + $totalDigital;
            $cantidadPagos = $pagosHoy->count();
            $pagosRecientes = $pagosHoy->take(8);
        }

        return view('secretaria.caja.index', compact(
            'cajaAbierta',
            'anioActivo',
            'totalesPorMetodo',
            'totalEfectivo',
            'totalDigital',
            'totalGeneral',
            'cantidadPagos',
            'pagosRecientes',
        ));
    }

    public function abrir(Request $request)
    {
        $existente = Caja::where('usuario_id', Auth::id())->where('estado', 'abierta')->first();
        if ($existente) {
            return redirect()->route('secretaria.caja.index')
                ->with('error', 'Ya tienes una caja abierta.');
        }

        $data = $request->validate([
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
        ]);

        $anioActivo = AnioEscolar::where('activo', true)->firstOrFail();

        $caja = Caja::create([
            'anio_escolar_id' => $anioActivo->id,
            'usuario_id'      => Auth::id(),
            'saldo_inicial'   => $data['saldo_inicial'],
            'fecha_apertura'  => now(),
            'estado'          => 'abierta',
        ]);

        AuditoriaService::log(Auth::id(), 'abrir_caja', 'cajas', $caja->id, $data, $request);

        return redirect()->route('secretaria.caja.index')
            ->with('status', 'Caja abierta correctamente.');
    }

    public function cierre(Caja $caja)
    {
        abort_if($caja->usuario_id !== Auth::id() || $caja->estado !== 'abierta', 403);

        $pagos = $caja->getPagos()->load('deudas.concepto');
        $totalesPorMetodo = collect();
        foreach ($pagos as $pago) {
            foreach ($pago->metodos_decoded as $m) {
                $nombre = $m['metodo'] ?? 'Otro';
                $totalesPorMetodo[$nombre] = ($totalesPorMetodo[$nombre] ?? 0) + (float) ($m['monto'] ?? 0);
            }
        }
        $totalEfectivo    = (float) ($totalesPorMetodo->get('Efectivo', 0));
        $totalDigital     = (float) ($totalesPorMetodo->except('Efectivo')->sum());
        $totalGeneral     = $totalEfectivo + $totalDigital;
        $cantidadPagos    = $pagos->count();

        $denominaciones   = self::DENOMINACIONES;
        $imagenesDenom    = self::IMAGENES_DENOM;
        $imagenesMetodo   = self::IMAGENES_METODO;

        return view('secretaria.caja.cierre', compact(
            'caja',
            'pagos',
            'totalesPorMetodo',
            'totalEfectivo',
            'totalDigital',
            'totalGeneral',
            'cantidadPagos',
            'denominaciones',
            'imagenesDenom',
            'imagenesMetodo',
        ));
    }

    public function cerrar(Request $request, Caja $caja)
    {
        abort_if($caja->usuario_id !== Auth::id() || $caja->estado !== 'abierta', 403);

        $data = $request->validate([
            'denominaciones'         => ['required', 'array'],
            'denominaciones.*'       => ['nullable', 'integer', 'min:0'],
            'confirmacion_digital'   => ['nullable', 'array'],
            'confirmacion_digital.*' => ['nullable', 'numeric', 'min:0'],
            'observaciones'          => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($data['denominaciones'] as $denominacion => $cantidad) {
            $cant = (int) $cantidad;
            if ($cant > 0) {
                $caja->denominaciones()->create([
                    'denominacion' => $denominacion,
                    'cantidad'     => $cant,
                ]);
            }
        }

        $confirmacionDigital = null;
        if (!empty($data['confirmacion_digital'])) {
            $confirmacionDigital = array_map('floatval', $data['confirmacion_digital']);
        }

        $caja->update([
            'estado'               => 'cerrada',
            'fecha_cierre'         => now(),
            'confirmacion_digital' => $confirmacionDigital,
            'observaciones'        => $data['observaciones'] ?? null,
        ]);

        AuditoriaService::log(Auth::id(), 'cerrar_caja', 'cajas', $caja->id, [], $request);

        return redirect()->route('secretaria.caja.historial')
            ->with('status', 'Caja cerrada correctamente.');
    }

    public function historial()
    {
        $cajas = Caja::where('usuario_id', Auth::id())
            ->with('denominaciones')
            ->orderByDesc('fecha_apertura')
            ->paginate(10);

        $imagenesMetodo = self::IMAGENES_METODO;

        return view('secretaria.caja.historial', compact('cajas', 'imagenesMetodo'));
    }

    public function show(Caja $caja)
    {
        $pagos = $caja->getPagos()->load([
            'deudas.concepto',
            'deudas.matricula.estudiante',
        ]);

        $totalesPorMetodo = collect();
        foreach ($pagos as $pago) {
            foreach ($pago->metodos_decoded as $m) {
                $nombre = $m['metodo'] ?? 'Otro';
                $totalesPorMetodo[$nombre] = ($totalesPorMetodo[$nombre] ?? 0) + (float) ($m['monto'] ?? 0);
            }
        }
        $totalEfectivo = (float) ($totalesPorMetodo->get('Efectivo', 0));
        $totalDigital  = (float) ($totalesPorMetodo->except('Efectivo')->sum());
        $totalGeneral  = $totalEfectivo + $totalDigital;

        return view('admin.caja.show', compact(
            'caja',
            'pagos',
            'totalesPorMetodo',
            'totalEfectivo',
            'totalDigital',
            'totalGeneral',
        ));
    }

    public function historialGlobal(Request $request)
    {
        $query = Caja::with(['usuario', 'denominaciones'])
            ->orderByDesc('fecha_apertura');

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        $cajas = $query->paginate(20)->withQueryString();

        $secretarias = \App\Models\User::role(['Secretaria', 'SuperAdmin'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.caja.historial_secretarias', compact('cajas', 'secretarias'));
    }
}
