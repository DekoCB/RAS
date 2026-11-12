<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaDocente;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsistenciaDocenteController extends Controller
{
    public function index(Request $request)
    {
        $docente = Docente::where('user_id', Auth::id())->first();

        if (!$docente) {
            return redirect()->route('docente.dashboard')
                ->with('error', 'No se encontró tu perfil de docente.');
        }

        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $tipo  = $request->input('tipo'); // ENTRADA|SALIDA|null

        $query = AsistenciaDocente::query()
            ->with(['registradoPor'])
            ->where('docente_id', $docente->id)
            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
            ->when($tipo, fn($q) => $q->where('tipo', $tipo))
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        $asistencias = $query->paginate(15)->withQueryString();

        return view('docente.asistencia_docente.index', compact(
            'asistencias',
            'desde',
            'hasta',
            'tipo'
        ));
    }
}