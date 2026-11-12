<?php

namespace App\Http\Controllers;

use App\Models\ComedorEvidencia;
use App\Models\ComedorRegistro;
use App\Models\ConceptoPago;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\HorarioInstitucional;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * MODULO 8 - Comedor R.A.S.
 *
 * RF-47: cobro del servicio a estudiantes y docentes.
 * RF-48: recepcion diaria de almuerzos, incluido quien trae el suyo.
 * RF-49: evidencia fotografica y de video del servicio.
 */
class ComedorController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $this->fecha($request);

        $registros = ComedorRegistro::with(['estudiante', 'docente'])
            ->delDia($fecha)
            ->orderBy('hora_recepcion')
            ->orderByDesc('id')
            ->get();

        $resumen = [
            'comedor'     => $registros->where('origen', 'comedor')->count(),
            'propio'      => $registros->where('origen', 'propio')->count(),
            'docentes'    => $registros->whereNotNull('docente_id')->count(),
            'por_cobrar'  => $registros->where('origen', 'comedor')->where('pagado', false)->sum('monto'),
            'cobrado'     => $registros->where('pagado', true)->sum('monto'),
        ];

        return view('comedor.index', [
            'fecha'     => $fecha,
            'registros' => $registros,
            'resumen'   => $resumen,
            'tarifas'   => $this->tarifas(),
            'horarios'  => HorarioInstitucional::whereNull('grado_id')
                ->whereNotNull('almuerzo_inicio')
                ->orderBy('nivel')
                ->get(),
        ] + $this->personas());
    }

    /** RF-48: registra la recepcion del almuerzo de una persona. */
    public function registrar(Request $request)
    {
        $data = $request->validate([
            'fecha'          => ['required', 'date'],
            'tipo_persona'   => ['required', 'in:estudiante,docente'],
            'estudiante_id'  => ['nullable', 'exists:estudiantes,id'],
            'docente_id'     => ['nullable', 'exists:docentes,id'],
            'origen'         => ['required', 'in:comedor,propio'],
            'hora_recepcion' => ['nullable', 'date_format:H:i'],
            'observacion'    => ['nullable', 'string', 'max:255'],
        ]);

        $esEstudiante = $data['tipo_persona'] === 'estudiante';
        $personaId    = $esEstudiante ? ($data['estudiante_id'] ?? null) : ($data['docente_id'] ?? null);

        if (!$personaId) {
            return back()->withInput()->withErrors([
                'tipo_persona' => 'Selecciona a la persona que recibe el almuerzo.',
            ]);
        }

        // Una persona solo se registra una vez por dia.
        $yaRegistrado = ComedorRegistro::delDia($data['fecha'])
            ->when($esEstudiante,
                fn ($q) => $q->where('estudiante_id', $personaId),
                fn ($q) => $q->where('docente_id', $personaId))
            ->exists();

        if ($yaRegistrado) {
            return back()->withInput()->withErrors([
                'tipo_persona' => 'Esa persona ya tiene registro de almuerzo en la fecha indicada.',
            ]);
        }

        // Quien trae su propio almuerzo no genera cobro (RF-47 y RF-48).
        $tarifas = $this->tarifas();
        $monto   = $data['origen'] === 'comedor'
            ? ($esEstudiante ? $tarifas['estudiante'] : $tarifas['docente'])
            : 0;

        ComedorRegistro::create([
            'fecha'          => $data['fecha'],
            'estudiante_id'  => $esEstudiante ? $personaId : null,
            'docente_id'     => $esEstudiante ? null : $personaId,
            'origen'         => $data['origen'],
            'hora_recepcion' => $data['hora_recepcion'] ?? now()->format('H:i'),
            'monto'          => $monto,
            'pagado'         => false,
            'observacion'    => $data['observacion'] ?? null,
            'registrado_por' => Auth::id(),
        ]);

        return back()->with('status', 'Recepcion de almuerzo registrada.');
    }

    /** RF-47: marca el consumo como cobrado. */
    public function cobrar(ComedorRegistro $registro)
    {
        if ($registro->origen !== 'comedor') {
            return back()->withErrors([
                'cobro' => 'Ese registro no genera cobro: la persona trajo su propio almuerzo.',
            ]);
        }

        $registro->update(['pagado' => true]);

        return back()->with('status', 'Consumo marcado como cobrado.');
    }

    public function eliminar(ComedorRegistro $registro)
    {
        $registro->delete();

        return back()->with('status', 'Registro eliminado.');
    }

    // ── Evidencias (RF-49) ────────────────────────────────────────────

    public function evidencias(Request $request)
    {
        return view('comedor.evidencias', [
            'evidencias' => ComedorEvidencia::orderByDesc('fecha')->orderByDesc('id')->paginate(24),
        ]);
    }

    public function subirEvidencia(Request $request)
    {
        $data = $request->validate([
            'fecha'       => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'archivo'     => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm', 'max:51200'],
        ]);

        $archivo   = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $tipo      = in_array($extension, ['mp4', 'webm'], true) ? 'video' : 'foto';

        ComedorEvidencia::create([
            'fecha'       => $data['fecha'],
            'tipo'        => $tipo,
            'archivo'     => $archivo->store('comedor', 'public'),
            'descripcion' => $data['descripcion'] ?? null,
            'subido_por'  => Auth::id(),
        ]);

        return back()->with('status', 'Evidencia cargada.');
    }

    public function eliminarEvidencia(ComedorEvidencia $evidencia)
    {
        if (Storage::disk('public')->exists($evidencia->archivo)) {
            Storage::disk('public')->delete($evidencia->archivo);
        }

        $evidencia->delete();

        return back()->with('status', 'Evidencia eliminada.');
    }

    // ── Apoyo ─────────────────────────────────────────────────────────

    private function fecha(Request $request): string
    {
        $fecha = $request->query('fecha');

        return $fecha && strtotime($fecha)
            ? Carbon::parse($fecha)->toDateString()
            : now()->toDateString();
    }

    /** Los montos salen del tarifario, no van escritos a mano. */
    private function tarifas(): array
    {
        return [
            'estudiante' => (float) (ConceptoPago::where('tipo', 'comedor')->value('monto_base') ?? 0),
            'docente'    => (float) (ConceptoPago::where('tipo', 'comedor_docente')->value('monto_base') ?? 0),
        ];
    }

    private function personas(): array
    {
        return [
            'estudiantes' => Estudiante::where('estado', 'activo')
                ->orderBy('apellidos')->orderBy('nombres')
                ->get(['id', 'nombres', 'apellidos']),
            'docentes' => Docente::where('estado', 'activo')
                ->orderBy('apellidos')->orderBy('nombres')
                ->get(['id', 'nombres', 'apellidos']),
        ];
    }
}
