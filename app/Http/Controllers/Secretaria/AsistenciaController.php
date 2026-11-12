<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Seccion;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:asistencia.ver')->only(['index']);
    }

    public function index(Request $request)
    {
        $q = $request->input('q');
        $fecha = $request->input('fecha');
        $seccionId = $request->input('seccion_id');
        $estado = $request->input('estado');

        $asistencias = Asistencia::with(['estudiante', 'seccion.grado', 'registradoPor'])
            ->when($fecha, function ($query) use ($fecha) {
                $query->whereDate('fecha', $fecha);
            })
            ->when($seccionId, function ($query) use ($seccionId) {
                $query->where('seccion_id', $seccionId);
            })
            ->when($estado, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->when($q, function ($query) use ($q) {
                $query->whereHas('estudiante', function ($estudianteQuery) use ($q) {
                    $estudianteQuery
                        ->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhere('dni', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('fecha')
            ->paginate(15)
            ->withQueryString();

        $secciones = Seccion::with('grado')
            ->orderBy('nombre')
            ->get();

        return view('secretaria.asistencias.index', compact('asistencias', 'secciones'));
    }
}
