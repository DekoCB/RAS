<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoMantenimiento;
use App\Models\DefensaCivilActividad;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * MODULO 14 - Administrativo.
 *
 * RF-70: inmobiliario y operaciones de infraestructura.
 * RF-71: defensa civil.
 */
class PatrimonioController extends Controller
{
    // ── Activos e infraestructura (RF-70) ─────────────────────────────

    public function activos(Request $request)
    {
        $categoria = $request->query('categoria');
        $estado    = $request->query('estado');
        $buscar    = trim((string) $request->query('q', ''));

        $activos = Activo::with(['sede'])
            ->withCount('mantenimientos')
            ->withSum('mantenimientos', 'costo')
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('codigo', 'like', "%{$buscar}%")
                ->orWhere('ubicacion', 'like', "%{$buscar}%")))
            ->orderBy('codigo')
            ->paginate(15)
            ->withQueryString();

        $porCategoria = Activo::selectRaw('categoria, count(*) as total')->groupBy('categoria')->pluck('total', 'categoria');

        return view('patrimonio.activos', [
            'activos'    => $activos,
            'categorias' => Activo::CATEGORIAS,
            'estados'    => Activo::ESTADOS,
            'sedes'      => Sede::orderBy('orden')->get(),
            'categoria'  => $categoria,
            'estado'     => $estado,
            'buscar'     => $buscar,
            'porCategoria' => $porCategoria,
            'resumen'    => [
                'total'         => Activo::count(),
                'operativos'    => Activo::where('estado', 'operativo')->count(),
                'mantenimiento' => Activo::where('estado', 'mantenimiento')->count(),
                'baja'          => Activo::where('estado', 'baja')->count(),
                'valor'         => Activo::where('estado', '!=', 'baja')->sum('valor'),
                'gasto_anio'    => ActivoMantenimiento::whereYear('fecha', now()->year)->sum('costo'),
            ],
        ]);
    }

    public function guardarActivo(Request $request)
    {
        $data = $request->validate([
            'codigo'            => ['nullable', 'string', 'max:60'],
            'nombre'            => ['required', 'string', 'max:180'],
            'categoria'         => ['required', 'in:' . implode(',', array_keys(Activo::CATEGORIAS))],
            'sede_id'           => ['nullable', 'exists:sedes,id'],
            'ubicacion'         => ['nullable', 'string', 'max:180'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'valor'             => ['nullable', 'numeric', 'min:0'],
            'estado'            => ['required', 'in:' . implode(',', array_keys(Activo::ESTADOS))],
            'observacion'       => ['nullable', 'string'],
        ]);

        // El codigo es unico: se comprueba antes para dar un mensaje claro.
        if (!empty($data['codigo']) && Activo::where('codigo', $data['codigo'])->exists()) {
            return back()->withInput()->withErrors(['codigo' => 'Ese codigo ya esta en uso.']);
        }

        Activo::create($data);

        return back()->with('status', 'Activo registrado.');
    }

    public function eliminarActivo(Activo $activo)
    {
        $activo->delete();

        return back()->with('status', 'Activo eliminado.');
    }

    public function mantenimientos(Activo $activo)
    {
        return view('patrimonio.mantenimientos', [
            'activo'          => $activo,
            'mantenimientos'  => $activo->mantenimientos()->orderByDesc('fecha')->get(),
        ]);
    }

    public function guardarMantenimiento(Request $request, Activo $activo)
    {
        $data = $request->validate([
            'fecha'       => ['required', 'date'],
            'tipo'        => ['required', 'in:preventivo,correctivo'],
            'responsable' => ['nullable', 'string', 'max:150'],
            'costo'       => ['nullable', 'numeric', 'min:0'],
            'detalle'     => ['nullable', 'string'],
        ]);

        $activo->mantenimientos()->create($data);

        return back()->with('status', 'Mantenimiento registrado.');
    }

    public function eliminarMantenimiento(ActivoMantenimiento $mantenimiento)
    {
        $mantenimiento->delete();

        return back()->with('status', 'Mantenimiento eliminado.');
    }

    // ── Defensa Civil (RF-71) ─────────────────────────────────────────

    public function defensaCivil(Request $request)
    {
        $tipo = $request->query('tipo');

        $actividades = DefensaCivilActividad::with('sede')
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->orderByDesc('fecha')
            ->paginate(15)
            ->withQueryString();

        return view('patrimonio.defensa-civil', [
            'actividades' => $actividades,
            'tipos'       => DefensaCivilActividad::TIPOS,
            'resultados'  => DefensaCivilActividad::RESULTADOS,
            'sedes'       => Sede::orderBy('orden')->get(),
            'tipo'        => $tipo,
            'resumen'     => [
                'total'      => DefensaCivilActividad::count(),
                'simulacros' => DefensaCivilActividad::where('tipo', 'simulacro')->count(),
                'observadas' => DefensaCivilActividad::where('resultado', 'observado')->count(),
                'proximas'   => DefensaCivilActividad::whereNotNull('proxima_fecha')
                    ->whereDate('proxima_fecha', '>=', now()->toDateString())
                    ->count(),
            ],
        ]);
    }

    public function guardarDefensaCivil(Request $request)
    {
        $data = $request->validate([
            'sede_id'       => ['nullable', 'exists:sedes,id'],
            'tipo'          => ['required', 'in:' . implode(',', array_keys(DefensaCivilActividad::TIPOS))],
            'titulo'        => ['required', 'string', 'max:180'],
            'fecha'         => ['required', 'date'],
            'responsable'   => ['nullable', 'string', 'max:150'],
            'participantes' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'resultado'     => ['nullable', 'in:' . implode(',', array_keys(DefensaCivilActividad::RESULTADOS))],
            'observacion'   => ['nullable', 'string'],
            'proxima_fecha' => ['nullable', 'date', 'after_or_equal:fecha'],
            'evidencia'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ]);

        unset($data['evidencia']);

        DefensaCivilActividad::create($data + [
            'evidencia' => $request->hasFile('evidencia')
                ? $request->file('evidencia')->store('defensa-civil', 'public')
                : null,
        ]);

        return back()->with('status', 'Actividad registrada.');
    }

    public function eliminarDefensaCivil(DefensaCivilActividad $actividad)
    {
        if ($actividad->evidencia && Storage::disk('public')->exists($actividad->evidencia)) {
            Storage::disk('public')->delete($actividad->evidencia);
        }

        $actividad->delete();

        return back()->with('status', 'Actividad eliminada.');
    }
}
