<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\CartaCompromiso;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Reunion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Reuniones, capacitaciones y cartas de compromiso.
 *
 * RF-43 y RF-44 (Coordinacion Pedagogica), RF-60 (capacitaciones y
 * pasantias de docentes), RF-64, RF-65 y RF-66 (padres de familia).
 */
class ReunionController extends Controller
{
    // ── Reuniones (RF-43, RF-44, RF-64) ───────────────────────────────

    public function index(Request $request)
    {
        $tipo   = $request->query('tipo');
        $estado = $request->query('estado');

        $reuniones = Reunion::with('grado')
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('fecha')
            ->paginate(15)
            ->withQueryString();

        return view('reuniones.index', [
            'reuniones' => $reuniones,
            'tipos'     => Reunion::TIPOS,
            'estados'   => Reunion::ESTADOS,
            'grados'    => Grado::orderBy('orden')->get(),
            'tipo'      => $tipo,
            'estado'    => $estado,
            'resumen'   => [
                'programadas' => Reunion::where('estado', 'programada')->count(),
                'realizadas'  => Reunion::where('estado', 'realizada')->count(),
                'con_acta'    => Reunion::whereNotNull('acta_archivo')->count(),
                'total'       => Reunion::count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validarReunion($request);

        Reunion::create($data + [
            'acta_archivo'   => $this->guardarActa($request),
            'registrado_por' => Auth::id(),
        ]);

        return back()->with('status', 'Reunion registrada.');
    }

    public function update(Request $request, Reunion $reunion)
    {
        $data = $this->validarReunion($request);

        if ($acta = $this->guardarActa($request)) {
            $this->borrarArchivo($reunion->acta_archivo);
            $data['acta_archivo'] = $acta;
        }

        $reunion->update($data);

        return back()->with('status', 'Reunion actualizada.');
    }

    public function destroy(Reunion $reunion)
    {
        $this->borrarArchivo($reunion->acta_archivo);
        $reunion->delete();

        return back()->with('status', 'Reunion eliminada.');
    }

    /** RF-44: deja constancia de que la reunion fue notificada. */
    public function notificar(Reunion $reunion)
    {
        $reunion->update(['notificada' => true]);

        return back()->with('status', 'Reunion marcada como notificada.');
    }

    // ── Capacitaciones (RF-60, RF-66) ─────────────────────────────────

    public function capacitaciones(Request $request)
    {
        $dirigido = $request->query('dirigido_a');

        return view('reuniones.capacitaciones', [
            'capacitaciones' => Capacitacion::when($dirigido, fn ($q) => $q->where('dirigido_a', $dirigido))
                ->orderByDesc('fecha_inicio')
                ->paginate(15)
                ->withQueryString(),
            'dirigidos' => Capacitacion::DIRIGIDO,
            'tipos'     => Capacitacion::TIPOS,
            'dirigido'  => $dirigido,
        ]);
    }

    public function guardarCapacitacion(Request $request)
    {
        $data = $request->validate([
            'dirigido_a'   => ['required', 'in:' . implode(',', array_keys(Capacitacion::DIRIGIDO))],
            'tipo'         => ['required', 'in:' . implode(',', array_keys(Capacitacion::TIPOS))],
            'titulo'       => ['required', 'string', 'max:180'],
            'descripcion'  => ['nullable', 'string'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'lugar'        => ['nullable', 'string', 'max:150'],
            'facilitador'  => ['nullable', 'string', 'max:150'],
            'horas'        => ['nullable', 'integer', 'min:1', 'max:1000'],
            'evidencia'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ]);

        unset($data['evidencia']);

        Capacitacion::create($data + [
            'evidencia' => $request->hasFile('evidencia')
                ? $request->file('evidencia')->store('capacitaciones', 'public')
                : null,
            'registrado_por' => Auth::id(),
        ]);

        return back()->with('status', 'Capacitacion registrada.');
    }

    public function eliminarCapacitacion(Capacitacion $capacitacion)
    {
        $this->borrarArchivo($capacitacion->evidencia);
        $capacitacion->delete();

        return back()->with('status', 'Capacitacion eliminada.');
    }

    // ── Cartas de compromiso (RF-65) ──────────────────────────────────

    public function compromisos(Request $request)
    {
        return view('reuniones.compromisos', [
            'cartas' => CartaCompromiso::with(['estudiante', 'apoderado'])
                ->orderByDesc('anio')
                ->orderByDesc('id')
                ->paginate(15),
            'estudiantes' => Estudiante::where('estado', 'activo')
                ->orderBy('apellidos')
                ->get(['id', 'nombres', 'apellidos']),
            'resumen' => [
                'total'     => CartaCompromiso::count(),
                'firmadas'  => CartaCompromiso::where('firmada', true)->count(),
                'pendiente' => CartaCompromiso::where('firmada', false)->count(),
            ],
        ]);
    }

    public function guardarCompromiso(Request $request)
    {
        $data = $request->validate([
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'anio'          => ['required', 'integer', 'min:2000', 'max:2100'],
            'titulo'        => ['nullable', 'string', 'max:180'],
            'compromisos'   => ['nullable', 'string'],
            'firmada'       => ['nullable', 'boolean'],
            'fecha_firma'   => ['nullable', 'date'],
            'archivo'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ]);

        // Una sola carta por estudiante y anio: si ya existe, se actualiza.
        $carta = CartaCompromiso::firstOrNew([
            'estudiante_id' => $data['estudiante_id'],
            'anio'          => $data['anio'],
        ]);

        $carta->fill([
            'titulo'      => $data['titulo'] ?? 'Carta de compromiso',
            'compromisos' => $data['compromisos'] ?? null,
            'firmada'     => $request->boolean('firmada'),
            'fecha_firma' => $data['fecha_firma'] ?? null,
        ]);

        if ($request->hasFile('archivo')) {
            $this->borrarArchivo($carta->archivo);
            $carta->archivo = $request->file('archivo')->store('compromisos', 'public');
        }

        $carta->registrado_por = Auth::id();
        $carta->save();

        return back()->with('status', 'Carta de compromiso guardada.');
    }

    public function eliminarCompromiso(CartaCompromiso $carta)
    {
        $this->borrarArchivo($carta->archivo);
        $carta->delete();

        return back()->with('status', 'Carta de compromiso eliminada.');
    }

    // ── Apoyo ─────────────────────────────────────────────────────────

    private function validarReunion(Request $request): array
    {
        $data = $request->validate([
            'tipo'        => ['required', 'in:' . implode(',', array_keys(Reunion::TIPOS))],
            'titulo'      => ['required', 'string', 'max:180'],
            'agenda'      => ['nullable', 'string'],
            'fecha'       => ['required', 'date'],
            'lugar'       => ['nullable', 'string', 'max:150'],
            'responsable' => ['nullable', 'string', 'max:150'],
            'nivel'       => ['nullable', 'string', 'in:Inicial,Primaria,Secundaria'],
            'grado_id'    => ['nullable', 'exists:grados,id'],
            'estado'      => ['required', 'in:' . implode(',', array_keys(Reunion::ESTADOS))],
            'acuerdos'    => ['nullable', 'string'],
            'asistentes'  => ['nullable', 'integer', 'min:0', 'max:5000'],
            'acta'        => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:8192'],
        ]);

        unset($data['acta']);

        foreach (['agenda', 'lugar', 'responsable', 'nivel', 'grado_id', 'acuerdos', 'asistentes'] as $campo) {
            $data[$campo] = $data[$campo] ?? null;
        }

        return $data;
    }

    private function guardarActa(Request $request): ?string
    {
        return $request->hasFile('acta')
            ? $request->file('acta')->store('actas', 'public')
            : null;
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
