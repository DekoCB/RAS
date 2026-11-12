<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Deuda;
use App\Models\Estudiante;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeudaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->first() : null;

        $deudas = collect();
        $saldoTotal = 0;
        $resumen = [
            'total_monto' => 0,
            'saldo_pendiente' => 0,
            'saldo_vencido' => 0,
            'saldo_por_vencer' => 0,
            'monto_pagado' => 0,
        ];
        $filtro = $request->input('f', 'todas');

        if ($estudiante) {
            $baseQuery = Deuda::whereHas('matricula', function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            })->with('concepto');

            $hoy = Carbon::today();

            $resumen['total_monto'] = (clone $baseQuery)->sum('monto');
            $resumen['saldo_pendiente'] = (clone $baseQuery)
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->sum('saldo');
            $resumen['saldo_vencido'] = (clone $baseQuery)
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', $hoy)
                ->sum('saldo');
            $resumen['saldo_por_vencer'] = (clone $baseQuery)
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->where(function ($query) use ($hoy) {
                    $query->whereNull('fecha_vencimiento')
                        ->orWhereDate('fecha_vencimiento', '>=', $hoy);
                })
                ->sum('saldo');
            $resumen['monto_pagado'] = max($resumen['total_monto'] - $resumen['saldo_pendiente'], 0);

            $deudasQuery = (clone $baseQuery);

            switch ($filtro) {
                case 'pendientes':
                    $deudasQuery->whereIn('estado', ['pendiente', 'parcial']);
                    break;
                case 'vencidas':
                    $deudasQuery->whereIn('estado', ['pendiente', 'parcial'])
                        ->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '<', $hoy);
                    break;
                case 'por-vencer':
                    $deudasQuery->whereIn('estado', ['pendiente', 'parcial'])
                        ->where(function ($query) use ($hoy) {
                            $query->whereNull('fecha_vencimiento')
                                ->orWhereDate('fecha_vencimiento', '>=', $hoy);
                        });
                    break;
                case 'pagadas':
                    $deudasQuery->where('estado', 'pagado');
                    break;
                case 'parciales':
                    $deudasQuery->where('estado', 'parcial');
                    break;
                default:
                    break;
            }

            $deudas = $deudasQuery
                ->orderBy('fecha_vencimiento')
                ->paginate(12)
                ->withQueryString();

            $saldoTotal = Deuda::whereHas('matricula', function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            })
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->sum('saldo');
        }

        return view('estudiante.deudas.index', compact(
            'estudiante',
            'deudas',
            'saldoTotal',
            'resumen',
            'filtro'
        ));
    }
}
