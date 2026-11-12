<?php

namespace App\Http\Controllers\Auxiliar;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\Asistencia;
use App\Models\Seccion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        $totalSecciones = Seccion::with('grado')->count();

        $asistenciasHoy = Asistencia::when($anio, fn($q) => $q->where('anio_escolar_id', $anio->id))
            ->whereDate('fecha', Carbon::today())
            ->count();

        $ultimasAsistencias = Asistencia::with(['estudiante', 'seccion.grado'])
            ->when($anio, fn($q) => $q->where('anio_escolar_id', $anio->id))
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('dashboards.auxiliar', compact(
            'anio',
            'totalSecciones',
            'asistenciasHoy',
            'ultimasAsistencias',
        ));
    }
}
