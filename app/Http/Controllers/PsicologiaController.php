<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\PsicologiaAtencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MODULO 9 - Psicologia (RF-50, RF-51).
 *
 * El acceso al modulo esta cerrado por permiso porque el RNF-05 exige
 * proteger la confidencialidad de esta informacion.
 */
class PsicologiaController extends Controller
{
    public function index(Request $request)
    {
        $area   = $request->query('area');
        $estado = $request->query('estado');

        $atenciones = PsicologiaAtencion::with('estudiante')
            ->when($area, fn ($q) => $q->where('area', $area))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Cuantas atenciones lleva cada una de las siete areas
        $porArea = PsicologiaAtencion::selectRaw('area, count(*) as total')
            ->groupBy('area')
            ->pluck('total', 'area');

        return view('psicologia.index', [
            'atenciones'  => $atenciones,
            'areas'       => PsicologiaAtencion::AREAS,
            'estados'     => PsicologiaAtencion::ESTADOS,
            'porArea'     => $porArea,
            'area'        => $area,
            'estado'      => $estado,
            'estudiantes' => Estudiante::where('estado', 'activo')
                ->orderBy('apellidos')
                ->get(['id', 'nombres', 'apellidos']),
            'resumen' => [
                'total'      => PsicologiaAtencion::count(),
                'abiertas'   => PsicologiaAtencion::where('estado', 'abierta')->count(),
                'seguimiento' => PsicologiaAtencion::where('estado', 'en_seguimiento')->count(),
                'citas'      => PsicologiaAtencion::whereNotNull('proxima_cita')
                    ->whereDate('proxima_cita', '>=', now()->toDateString())
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        PsicologiaAtencion::create($data + ['registrado_por' => Auth::id()]);

        return back()->with('status', 'Atencion registrada.');
    }

    public function update(Request $request, PsicologiaAtencion $atencion)
    {
        $atencion->update($this->validar($request));

        return back()->with('status', 'Atencion actualizada.');
    }

    public function destroy(PsicologiaAtencion $atencion)
    {
        $atencion->delete();

        return back()->with('status', 'Atencion eliminada.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'estudiante_id' => ['nullable', 'exists:estudiantes,id'],
            'area'          => ['required', 'in:' . implode(',', array_keys(PsicologiaAtencion::AREAS))],
            'motivo'        => ['required', 'string', 'max:200'],
            'fecha'         => ['required', 'date'],
            'responsable'   => ['nullable', 'string', 'max:150'],
            'estado'        => ['required', 'in:' . implode(',', array_keys(PsicologiaAtencion::ESTADOS))],
            'observacion'   => ['nullable', 'string'],
            'acuerdos'      => ['nullable', 'string'],
            'proxima_cita'  => ['nullable', 'date', 'after_or_equal:fecha'],
            'confidencial'  => ['nullable', 'boolean'],
        ]);

        foreach (['estudiante_id', 'responsable', 'observacion', 'acuerdos', 'proxima_cita'] as $campo) {
            $data[$campo] = $data[$campo] ?? null;
        }

        $data['confidencial'] = $request->boolean('confidencial', true);

        return $data;
    }
}
