<?php

namespace App\Http\Controllers\Auxiliar;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\AsistenciaAuxiliar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MisAsistenciasController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $anio  = AnioEscolar::where('activo', true)->first();
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $tipo  = $request->input('tipo');

        $asistencias = AsistenciaAuxiliar::with('registrador')
            ->where('user_id', $user->id)
            ->when($anio, fn($q) => $q->where('anio_escolar_id', $anio->id))
            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
            ->when($tipo,  fn($q) => $q->where('tipo', $tipo))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('auxiliar.mis_asistencias.index', compact(
            'user', 'asistencias', 'anio', 'desde', 'hasta', 'tipo'
        ));
    }
}
