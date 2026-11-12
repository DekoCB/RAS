<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grado;
use Illuminate\Http\Request;

class NivelGradoController extends Controller
{
    public function index()
    {
        $grados = Grado::withCount(['secciones', 'matriculas'])
            ->orderBy('nivel')
            ->orderBy('nombre')
            ->get();

        $niveles = $grados->groupBy(function ($grado) {
            return $grado->nivel ?: 'Sin nivel';
        });

        $nivelesDisponibles = $grados->pluck('nivel')->filter()->unique()->values();

        return view('admin.niveles_grados.index', compact('niveles', 'nivelesDisponibles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nivel' => ['required', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        Grado::create([
            'nivel' => $data['nivel'],
            'nombre' => $data['nombre'],
        ]);

        return redirect()
            ->route('admin-sistema.niveles-grados.index')
            ->with('status', 'Grado creado.');
    }

    public function update(Request $request, Grado $grado)
    {
        $data = $request->validate([
            'nivel' => ['required', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $grado->update([
            'nivel' => $data['nivel'],
            'nombre' => $data['nombre'],
        ]);

        return redirect()
            ->route('admin-sistema.niveles-grados.index')
            ->with('status', 'Grado actualizado.');
    }

    public function destroy(Grado $grado)
    {
        if ($grado->secciones()->exists() || $grado->matriculas()->exists()) {
            return redirect()
                ->route('admin-sistema.niveles-grados.index')
                ->with('error', 'No se puede eliminar un grado con secciones o matriculas.');
        }

        $grado->delete();

        return redirect()
            ->route('admin-sistema.niveles-grados.index')
            ->with('status', 'Grado eliminado.');
    }
}
