<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Deuda;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $anioActivo = AnioEscolar::where('activo', true)->first();
        $anioId = $anioActivo?->id;

        $estudiantesTotal = Estudiante::count();

        $matriculasBase = Matricula::query();
        if ($anioId) {
            $matriculasBase->where('anio_escolar_id', $anioId);
        }
        $matriculasTotal = (clone $matriculasBase)->count();
        $matriculasHoy = (clone $matriculasBase)
            ->whereDate('created_at', Carbon::today())
            ->count();
        $matriculasRecientes = (clone $matriculasBase)
            ->with(['estudiante', 'grado', 'seccion'])
            ->where('estado', Matricula::ESTADO_REGISTRADA)
            ->orderByRaw('COALESCE(fecha_matricula, created_at) desc')
            ->limit(6)
            ->get();

        $pagosBase = Pago::query();
        if ($anioId) {
            $pagosBase->where('anio_escolar_id', $anioId);
        }
        $pagosHoy = (clone $pagosBase)
            ->whereDate('fecha_pago', Carbon::today())
            ->count();
        $pagosMontoHoy = (clone $pagosBase)
            ->whereDate('fecha_pago', Carbon::today())
            ->sum('monto');
        $pagosPendientes = (clone $pagosBase)
            ->where('estado', 'pendiente')
            ->count();
        $pagosRecientes = (clone $pagosBase)
            ->with(['deudas.matricula.estudiante', 'deudas.concepto'])
            ->orderByDesc('fecha_pago')
            ->limit(6)
            ->get();

        $deudasPendientesMonto = Deuda::when($anioId, fn($q) => $q->where('anio_escolar_id', $anioId))
            ->where('estado', 'pendiente')
            ->sum('saldo');

        $morososMes = Deuda::when($anioId, fn($q) => $q->where('anio_escolar_id', $anioId))
            ->where('estado', 'pendiente')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', Carbon::today())
            ->distinct('matricula_id')
            ->count('matricula_id');

        return view('dashboards.secretaria', [
            'anioActivo' => $anioActivo,
            'estudiantesTotal' => $estudiantesTotal,
            'matriculasTotal' => $matriculasTotal,
            'matriculasHoy' => $matriculasHoy,
            'matriculasRecientes' => $matriculasRecientes,
            'pagosHoy' => $pagosHoy,
            'pagosMontoHoy' => $pagosMontoHoy,
            'pagosPendientes' => $pagosPendientes,
            'pagosRecientes' => $pagosRecientes,
            'deudasPendientesMonto' => $deudasPendientesMonto,
            'morososMes' => $morososMes,
        ]);
    }
}
