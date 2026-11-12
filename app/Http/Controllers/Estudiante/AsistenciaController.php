<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Estudiante;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->first() : null;

        $asistencias = collect();
        $anioSeleccionado = (int) Carbon::now()->format('Y');
        $mesSeleccionado = Carbon::now()->format('m');
        $mesesDisponibles = collect();
        $aniosDisponibles = collect();
        $resumen = [
            'total' => 0,
            'presente' => 0,
            'tarde' => 0,
            'ausente' => 0,
            'justificado' => 0,
        ];

        if ($estudiante) {
            $anio = $request->input('anio');
            if (is_string($anio) && preg_match('/^\d{4}$/', $anio)) {
                $anioSeleccionado = (int) $anio;
            }

            $mes = $request->input('mes');
            if (is_string($mes) && preg_match('/^(0[1-9]|1[0-2])$/', $mes)) {
                $mesSeleccionado = $mes;
            }

            $mesCarbon = Carbon::create($anioSeleccionado, (int) $mesSeleccionado, 1)->startOfMonth();
            $inicio = $mesCarbon->copy()->startOfMonth();
            $fin = $mesCarbon->copy()->endOfMonth();

            $asistencias = Asistencia::where('estudiante_id', $estudiante->id)
                ->with('seccion.grado')
                ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
                ->orderBy('fecha')
                ->get();

            foreach ($asistencias as $asistencia) {
                $estado = strtolower((string) $asistencia->estado);
                if (array_key_exists($estado, $resumen)) {
                    $resumen[$estado]++;
                }
            }

            $resumen['total'] = $asistencias->count();

            $aniosDisponibles = Asistencia::where('estudiante_id', $estudiante->id)
                ->selectRaw('YEAR(fecha) as anio')
                ->distinct()
                ->orderByDesc('anio')
                ->pluck('anio');

            if ($aniosDisponibles->isEmpty()) {
                $aniosDisponibles = collect([$anioSeleccionado]);
            } elseif (!$aniosDisponibles->contains($anioSeleccionado)) {
                $aniosDisponibles->push($anioSeleccionado);
                $aniosDisponibles = $aniosDisponibles->sortDesc()->values();
            }

            $mesesDisponibles = collect(range(1, 12))->map(function ($month) use ($anioSeleccionado) {
                $fecha = Carbon::create($anioSeleccionado, $month, 1);
                return [
                    'value' => $fecha->format('m'),
                    'label' => $fecha->locale('es')->translatedFormat('F'),
                ];
            });
        }

        return view('estudiante.asistencias.index', compact(
            'estudiante',
            'asistencias',
            'mesesDisponibles',
            'mesSeleccionado',
            'aniosDisponibles',
            'anioSeleccionado',
            'resumen'
        ));
    }
}
