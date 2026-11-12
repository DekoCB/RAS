<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\PastoralRegistro;
use App\Models\Postulante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * MODULO 11 - Pastoral (RF-56).
 *
 * El test pastoral y la carta de declaracion de fe se aplican durante la
 * admision (RF-15), asi que un registro puede colgar de un postulante o
 * de un estudiante ya matriculado.
 */
class PastoralController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->query('tipo');

        $registros = PastoralRegistro::with(['postulante', 'estudiante'])
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('pastoral.index', [
            'registros'  => $registros,
            'tipos'      => PastoralRegistro::TIPOS,
            'resultados' => PastoralRegistro::RESULTADOS,
            'tipo'       => $tipo,
            // Solo los postulantes que siguen en carrera necesitan entrevista
            'postulantes' => Postulante::whereIn('estado', ['borrador', 'en_proceso'])
                ->orderBy('apellidos')
                ->get(['id', 'nombres', 'apellidos', 'codigo']),
            'estudiantes' => Estudiante::where('estado', 'activo')
                ->orderBy('apellidos')
                ->get(['id', 'nombres', 'apellidos']),
            'resumen' => [
                'total'     => PastoralRegistro::count(),
                'tests'     => PastoralRegistro::where('tipo', 'test_pastoral')->count(),
                'cartas'    => PastoralRegistro::where('tipo', 'carta_fe')->count(),
                'favorable' => PastoralRegistro::where('resultado', 'favorable')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'persona'       => ['required', 'in:postulante,estudiante'],
            'postulante_id' => ['nullable', 'exists:postulantes,id'],
            'estudiante_id' => ['nullable', 'exists:estudiantes,id'],
            'tipo'          => ['required', 'in:' . implode(',', array_keys(PastoralRegistro::TIPOS))],
            'fecha'         => ['required', 'date'],
            'responsable'   => ['nullable', 'string', 'max:150'],
            'resultado'     => ['nullable', 'in:' . implode(',', array_keys(PastoralRegistro::RESULTADOS))],
            'observacion'   => ['nullable', 'string'],
            'archivo'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ]);

        $esPostulante = $data['persona'] === 'postulante';
        $personaId    = $esPostulante ? ($data['postulante_id'] ?? null) : ($data['estudiante_id'] ?? null);

        if (!$personaId) {
            return back()->withInput()->withErrors([
                'persona' => 'Selecciona a la persona sobre la que se registra.',
            ]);
        }

        PastoralRegistro::create([
            'postulante_id'  => $esPostulante ? $personaId : null,
            'estudiante_id'  => $esPostulante ? null : $personaId,
            'tipo'           => $data['tipo'],
            'fecha'          => $data['fecha'],
            'responsable'    => $data['responsable'] ?? null,
            'resultado'      => $data['resultado'] ?? 'pendiente',
            'observacion'    => $data['observacion'] ?? null,
            'archivo'        => $request->hasFile('archivo')
                ? $request->file('archivo')->store('pastoral', 'public')
                : null,
            'registrado_por' => Auth::id(),
        ]);

        return back()->with('status', 'Registro pastoral guardado.');
    }

    public function destroy(PastoralRegistro $registro)
    {
        if ($registro->archivo && Storage::disk('public')->exists($registro->archivo)) {
            Storage::disk('public')->delete($registro->archivo);
        }

        $registro->delete();

        return back()->with('status', 'Registro eliminado.');
    }
}
