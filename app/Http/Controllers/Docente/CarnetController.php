<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Support\CarnetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarnetController extends Controller
{
    public function show(Request $request)
    {
        $userId = Auth::id();

        $docente = Docente::where('user_id', $userId)->first();

        if (!$docente) {
            return redirect()->route('docente.dashboard')
                ->with('error', 'No se encontró el perfil del docente.');
        }

        // Por si el docente es antiguo y no tiene carnet_codigo
        if (empty($docente->carnet_codigo)) {
            $docente->carnet_codigo = $this->generarCarnetCodigoDocente($docente);
            $docente->save();
        }

        return view('docente.carnet', compact('docente'));
    }

    private function generarCarnetCodigoDocente(Docente $docente): string
    {
        return CarnetCode::generateDocenteCode($docente);
    }
}
