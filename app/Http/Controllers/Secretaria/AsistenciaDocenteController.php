<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\AnioEscolar;
use App\Models\AsistenciaAuxiliar;
use App\Models\AsistenciaDocente;
use App\Models\AsistenciaSecretaria;
use App\Models\Docente;
use App\Models\User;
use App\Support\CarnetCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsistenciaDocenteController extends Controller
{
    public function index()
    {
        return view('secretaria.asistencias_docentes.index');
    }

    public function scan(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:255'],
            'tipo'   => ['nullable', 'in:ENTRADA,SALIDA'],
            'fecha'  => ['nullable', 'date'], // ✅ para pruebas (solo se usará en local)
        ]);

        // ✅ Normalizar código: quitar espacios/saltos, mayúsculas
        $codigo = CarnetCode::normalize($data['codigo']);

        $tipo = $data['tipo'] ?? 'ENTRADA';

        // Año escolar activo (si no hay, igual registramos sin año)
        $anio = AnioEscolar::where('activo', true)->first();

        // ✅ Fecha (solo local permite simular)
        $hoy = (app()->environment('local') && !empty($data['fecha']))
            ? Carbon::parse($data['fecha'], 'America/Lima')->toDateString()
            : Carbon::now('America/Lima')->toDateString();

        // ✅ Buscar docente por carnet_codigo (case-insensitive)
        $docente = CarnetCode::findDocente($codigo);

        if (!$docente) {
            // Buscar en users por carnet_codigo (Secretaria, Auxiliar, Director, SuperAdmin)
            $personal = CarnetCode::findUser($codigo);

            if ($personal) {
                $rolNombre = $personal->roles->first()?->name ?? 'Personal';

                // Auxiliar → asistencias_auxiliar
                if ($personal->hasRole('Auxiliar')) {
                    $yaExiste = AsistenciaAuxiliar::where('user_id', $personal->id)
                        ->whereDate('fecha', $hoy)
                        ->where('tipo', $tipo)
                        ->exists();

                    if ($yaExiste) {
                        return response()->json([
                            'ok'     => false,
                            'message' => "Ya registrado hoy ($tipo).",
                            'docente' => $personal->name,
                        ], 409);
                    }

                    AsistenciaAuxiliar::create([
                        'anio_escolar_id' => $anio?->id,
                        'user_id'         => $personal->id,
                        'fecha'           => $hoy,
                        'tipo'            => $tipo,
                        'registrado_por'  => Auth::id(),
                    ]);
                } else {
                    // Secretaria, Director, SuperAdmin → asistencias_secretaria
                    $yaExiste = AsistenciaSecretaria::where('user_id', $personal->id)
                        ->whereDate('fecha', $hoy)
                        ->where('tipo', $tipo)
                        ->exists();

                    if ($yaExiste) {
                        return response()->json([
                            'ok'     => false,
                            'message' => "Ya registrado hoy ($tipo).",
                            'docente' => $personal->name,
                        ], 409);
                    }

                    AsistenciaSecretaria::create([
                        'anio_escolar_id' => $anio?->id,
                        'user_id'         => $personal->id,
                        'fecha'           => $hoy,
                        'tipo'            => $tipo,
                        'registrado_por'  => Auth::id(),
                    ]);
                }

                return response()->json([
                    'ok'     => true,
                    'message' => "Asistencia registrada ($tipo) — $rolNombre",
                    'docente' => $personal->name,
                    'fecha'   => $hoy,
                ]);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Carnet no encontrado: ' . $codigo . '. Si fue generado en otro ambiente, vuelve a imprimirlo desde produccion.',
            ], 404);
        }

        // Si está inactivo, bloquear
        if (($docente->estado ?? 'activo') !== 'activo') {
            return response()->json([
                'ok' => false,
                'message' => 'El docente está inactivo.',
                'docente' => $docente->apellidos . ' ' . $docente->nombres,
            ], 422);
        }

        // Evitar duplicado por día y tipo
        $yaExiste = AsistenciaDocente::where('docente_id', $docente->id)
            ->whereDate('fecha', $hoy)
            ->where('tipo', $tipo)
            ->exists();

        if ($yaExiste) {
            return response()->json([
                'ok' => false,
                'message' => "Ya registrado hoy ($tipo).",
                'docente' => $docente->apellidos . ' ' . $docente->nombres,
            ], 409);
        }

        AsistenciaDocente::create([
            'anio_escolar_id' => $anio?->id,
            'docente_id'      => $docente->id,
            'fecha'           => $hoy,
            'tipo'            => $tipo,
            'registrado_por'  => Auth::id(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => "Asistencia registrada ($tipo)",
            'docente' => $docente->apellidos . ' ' . $docente->nombres,
            'fecha' => $hoy,
        ]);
    }

    public function historial(Request $request)
    {
        $anio = AnioEscolar::where('activo', true)->first();

        $fecha = $request->input('fecha'); // YYYY-MM-DD
        $tipo = $request->input('tipo');   // ENTRADA|SALIDA|null
        $q = trim((string) $request->input('q', ''));

        $query = AsistenciaDocente::query()
            ->with(['docente'])
            ->when($anio, function ($sub) use ($anio) {
                $sub->where('anio_escolar_id', $anio->id);
            })
            ->when($fecha, function ($sub) use ($fecha) {
                $sub->whereDate('fecha', $fecha);
            })
            ->when($tipo, function ($sub) use ($tipo) {
                $sub->where('tipo', $tipo);
            })
            ->when($q !== '', function ($sub) use ($q) {
                $sub->whereHas('docente', function ($d) use ($q) {
                    $d->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhere('dni', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        $asistencias = $query->paginate(15)->withQueryString();

        return view('secretaria.asistencias_docentes.historial', compact(
            'asistencias',
            'fecha',
            'tipo',
            'q',
            'anio'
        ));
    }
}
