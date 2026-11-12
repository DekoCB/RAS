<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::withCount('secciones')
            ->orderBy('nombre')
            ->get();

        $maxCodigo = Curso::query()
            ->whereNotNull('codigo')
            ->get(['codigo'])
            ->reduce(function ($carry, $curso) {
                $codigo = (string) $curso->codigo;
                if (!preg_match('/^CUR-(\d+)$/', $codigo, $match)) {
                    return $carry;
                }
                $numero = (int) $match[1];
                return max($carry, $numero);
            }, 0);

        $codigoSugerido = 'CUR-' . str_pad((string) ($maxCodigo + 1), 6, '0', STR_PAD_LEFT);

        return view('admin.cursos.index', compact('cursos', 'codigoSugerido'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['nullable', 'string', 'max:50'],
        ]);

        Curso::create($data);

        return redirect()
            ->route('admin-sistema.cursos.index')
            ->with('status', 'Curso creado.');
    }

    public function update(Request $request, Curso $curso)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['nullable', 'string', 'max:50'],
        ]);

        $curso->update($data);

        return redirect()
            ->route('admin-sistema.cursos.index')
            ->with('status', 'Curso actualizado.');
    }

    public function destroy(Curso $curso)
    {
        if ($curso->secciones()->exists()) {
            return redirect()
                ->route('admin-sistema.cursos.index')
                ->with('error', 'No se puede eliminar un curso con secciones asignadas.');
        }

        $curso->delete();

        return redirect()
            ->route('admin-sistema.cursos.index')
            ->with('status', 'Curso eliminado.');
    }
}
