<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Deuda;
use App\Models\Estudiante;
use App\Models\Nota;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $estudiante = $user ? Estudiante::where('user_id', $user->id)->first() : null;

        $asistencias = collect();
        $notas = collect();
        $deudas = collect();
        $resumen = [
            'asistencias' => 0,
            'notas' => 0,
            'deudas' => 0,
            'saldo' => 0,
        ];

        if ($estudiante) {
            $asistencias = Asistencia::where('estudiante_id', $estudiante->id)
                ->with('seccion.grado')
                ->orderByDesc('fecha')
                ->take(10)
                ->get();

            $notas = Nota::where('estudiante_id', $estudiante->id)
                ->with('curso')
                ->orderByDesc('created_at')
                ->take(10)
                ->get();

            $deudas = Deuda::whereHas('matricula', function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            })
                ->with('concepto')
                ->orderBy('fecha_vencimiento')
                ->take(10)
                ->get();

            $resumen['asistencias'] = Asistencia::where('estudiante_id', $estudiante->id)->count();
            $resumen['notas'] = Nota::where('estudiante_id', $estudiante->id)->count();
            $resumen['deudas'] = Deuda::whereHas('matricula', function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            })
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->count();

            $resumen['saldo'] = Deuda::whereHas('matricula', function ($query) use ($estudiante) {
                $query->where('estudiante_id', $estudiante->id);
            })
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->sum('saldo');
        }

        return view('estudiante.dashboard', compact('estudiante', 'asistencias', 'notas', 'deudas', 'resumen'));
    }
}
