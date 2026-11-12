<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarnetController extends Controller
{
    public function descargar(Request $request)
    {
        $user = $request->user();
        $estudiante = $user ? Estudiante::where('user_id', $user->id)->first() : null;

        if (!$estudiante) {
            abort(403, 'No tienes un perfil de estudiante asociado.');
        }

        if (!$estudiante->carnet_codigo) {
            do {
                $codigo = 'JP-' . strtoupper(Str::random(8));
            } while (Estudiante::where('carnet_codigo', $codigo)->exists());

            $estudiante->carnet_codigo = $codigo;
            $estudiante->save();
        }

        $matricula = $estudiante->matriculas()
            ->with(['grado', 'seccion', 'anioEscolar'])
            ->latest()
            ->first();

        $qr = null;
        try {
            if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
                $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(220)
                    ->margin(1)
                    ->generate($estudiante->carnet_codigo);
                $qr = 'data:image/svg+xml;base64,' . base64_encode($svg);
            } elseif (class_exists(\SimpleSoftwareIO\QrCode\Generator::class)) {
                $generator = new \SimpleSoftwareIO\QrCode\Generator;
                $svg = $generator->format('svg')
                    ->size(220)
                    ->margin(1)
                    ->generate($estudiante->carnet_codigo);
                $qr = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        } catch (\Throwable $e) {
            $qr = null;
        }

        $autoPrint = true;

        return view('estudiante.carnet', compact('estudiante', 'matricula', 'qr', 'autoPrint'));
    }
}
