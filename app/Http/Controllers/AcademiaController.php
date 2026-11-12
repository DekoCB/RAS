<?php

namespace App\Http\Controllers;

use App\Models\AcademiaAlumno;
use App\Models\AcademiaAsistencia;
use App\Models\AcademiaEvaluacion;
use App\Models\AcademiaHorario;
use App\Models\AcademiaMaterial;
use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * MODULO 10 - Real Academia preuniversitaria (RF-52 a RF-55).
 *
 * Funciona de 4:00 a 5:30 p.m. de lunes a viernes (RF-52) y cobra
 * pensiones distintas a los alumnos del colegio y a los libres (RF-54).
 */
class AcademiaController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->query('tipo');

        $alumnos = AcademiaAlumno::with(['estudiante', 'anioEscolar'])
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->orderByDesc('activo')
            ->orderBy('apellidos')
            ->paginate(15)
            ->withQueryString();

        return view('academia.index', [
            'alumnos'     => $alumnos,
            'tipos'       => AcademiaAlumno::TIPOS,
            'tipo'        => $tipo,
            'anios'       => AnioEscolar::orderByDesc('id')->get(),
            'estudiantes' => Estudiante::where('estado', 'activo')
                ->orderBy('apellidos')
                ->get(['id', 'nombres', 'apellidos']),
            'pensiones'   => $this->pensiones(),
            'resumen'     => [
                'total'    => AcademiaAlumno::where('activo', true)->count(),
                'regular'  => AcademiaAlumno::where('activo', true)->where('tipo', 'regular')->count(),
                'libre'    => AcademiaAlumno::where('activo', true)->where('tipo', 'libre')->count(),
                'ingresos' => AcademiaAlumno::where('ingreso_universidad', true)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validarAlumno($request);

        if ($error = $this->validarIdentidad($data)) {
            return back()->withInput()->withErrors($error);
        }

        // RF-54: la pension depende de si es alumno del colegio o libre.
        $pensiones = $this->pensiones();
        $data['pension'] = $data['pension'] ?: $pensiones[$data['tipo']];

        AcademiaAlumno::create($data);

        return back()->with('status', 'Alumno registrado en la academia.');
    }

    public function update(Request $request, AcademiaAlumno $alumno)
    {
        $data = $this->validarAlumno($request);

        if ($error = $this->validarIdentidad($data)) {
            return back()->withInput()->withErrors($error);
        }

        if ($request->hasFile('evidencia_ingreso')) {
            $this->borrarArchivo($alumno->evidencia_ingreso);
            $data['evidencia_ingreso'] = $request->file('evidencia_ingreso')
                ->store('academia/ingresos', 'public');
        }

        $alumno->update($data);

        return back()->with('status', 'Alumno actualizado.');
    }

    public function destroy(AcademiaAlumno $alumno)
    {
        $this->borrarArchivo($alumno->evidencia_ingreso);
        $alumno->delete();

        return back()->with('status', 'Alumno eliminado de la academia.');
    }

    /** Expediente del alumno: asistencia, notas y materiales (RF-53, RF-55). */
    public function alumno(AcademiaAlumno $alumno)
    {
        $alumno->load(['estudiante', 'asistencias', 'evaluaciones', 'materiales']);

        $asistencias = $alumno->asistencias->sortByDesc('fecha')->take(30);
        $totales     = $alumno->asistencias->countBy('estado');

        return view('academia.alumno', [
            'alumno'      => $alumno,
            'asistencias' => $asistencias,
            'totales'     => $totales,
            'estados'     => AcademiaAsistencia::ESTADOS,
        ]);
    }

    public function registrarAsistencia(Request $request, AcademiaAlumno $alumno)
    {
        $data = $request->validate([
            'fecha'       => ['required', 'date'],
            'estado'      => ['required', 'in:' . implode(',', array_keys(AcademiaAsistencia::ESTADOS))],
            'observacion' => ['nullable', 'string', 'max:200'],
        ]);

        // Una marca por alumno y dia: si ya existe se corrige.
        AcademiaAsistencia::updateOrCreate(
            ['alumno_id' => $alumno->id, 'fecha' => $data['fecha']],
            [
                'estado'         => $data['estado'],
                'observacion'    => $data['observacion'] ?? null,
                'registrado_por' => Auth::id(),
            ]
        );

        return back()->with('status', 'Asistencia registrada.');
    }

    public function registrarEvaluacion(Request $request, AcademiaAlumno $alumno)
    {
        $data = $request->validate([
            'titulo'         => ['required', 'string', 'max:180'],
            'curso'          => ['nullable', 'string', 'max:120'],
            'fecha'          => ['required', 'date'],
            'puntaje'        => ['nullable', 'numeric', 'min:0'],
            'puntaje_maximo' => ['required', 'numeric', 'min:1'],
            'observacion'    => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($data['puntaje']) && $data['puntaje'] > $data['puntaje_maximo']) {
            return back()->withErrors(['puntaje' => 'El puntaje supera el maximo de la evaluacion.']);
        }

        $alumno->evaluaciones()->create($data);

        return back()->with('status', 'Evaluacion registrada.');
    }

    public function registrarMaterial(Request $request, AcademiaAlumno $alumno)
    {
        $data = $request->validate([
            'titulo'        => ['required', 'string', 'max:180'],
            'curso'         => ['nullable', 'string', 'max:120'],
            'fecha_entrega' => ['required', 'date'],
            'cantidad'      => ['required', 'integer', 'min:1', 'max:999'],
            'observacion'   => ['nullable', 'string', 'max:200'],
        ]);

        $alumno->materiales()->create($data + ['registrado_por' => Auth::id()]);

        return back()->with('status', 'Material registrado.');
    }

    // ── Horarios por area (RF-52, RF-53) ──────────────────────────────

    public function horarios()
    {
        return view('academia.horarios', [
            'horarios' => AcademiaHorario::orderBy('area')->orderBy('dia')->orderBy('hora_inicio')->get(),
            'dias'     => AcademiaHorario::DIAS,
        ]);
    }

    public function guardarHorario(Request $request)
    {
        $data = $request->validate([
            'area'        => ['required', 'string', 'max:120'],
            'curso'       => ['required', 'string', 'max:120'],
            'dia'         => ['required', 'integer', 'min:1', 'max:5'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin'    => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'docente'     => ['nullable', 'string', 'max:150'],
        ]);

        AcademiaHorario::create($data + ['activo' => true]);

        return back()->with('status', 'Horario registrado.');
    }

    public function eliminarHorario(AcademiaHorario $horario)
    {
        $horario->delete();

        return back()->with('status', 'Horario eliminado.');
    }

    // ── Apoyo ─────────────────────────────────────────────────────────

    private function validarAlumno(Request $request): array
    {
        $data = $request->validate([
            'anio_escolar_id'     => ['required', 'exists:anios_escolares,id'],
            'tipo'                => ['required', 'in:regular,libre'],
            'estudiante_id'       => ['nullable', 'exists:estudiantes,id'],
            'nombres'             => ['nullable', 'string', 'max:120'],
            'apellidos'           => ['nullable', 'string', 'max:120'],
            'dni'                 => ['nullable', 'string', 'max:20'],
            'telefono'            => ['nullable', 'string', 'max:40'],
            'area'                => ['nullable', 'string', 'max:120'],
            'pension'             => ['nullable', 'numeric', 'min:0'],
            'universidad_destino' => ['nullable', 'string', 'max:180'],
            'ingreso_universidad' => ['nullable', 'boolean'],
            'evidencia_ingreso'   => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
            'activo'              => ['nullable', 'boolean'],
        ]);

        unset($data['evidencia_ingreso']);

        foreach (['estudiante_id', 'nombres', 'apellidos', 'dni', 'telefono', 'area', 'universidad_destino'] as $campo) {
            $data[$campo] = $data[$campo] ?? null;
        }

        $data['pension']             = $data['pension'] ?? null;
        $data['ingreso_universidad'] = $request->boolean('ingreso_universidad');
        $data['activo']              = $request->boolean('activo', true);

        return $data;
    }

    /** Un alumno regular se enlaza al colegio; uno libre trae sus datos. */
    private function validarIdentidad(array $data): ?array
    {
        if ($data['tipo'] === 'regular' && !$data['estudiante_id']) {
            return ['estudiante_id' => 'Un alumno regular debe estar enlazado a un estudiante del colegio.'];
        }

        if ($data['tipo'] === 'libre' && (!$data['nombres'] || !$data['apellidos'])) {
            return ['nombres' => 'Un estudiante libre necesita nombres y apellidos propios.'];
        }

        return null;
    }

    /** RF-54: los montos salen del tarifario, no van escritos a mano. */
    private function pensiones(): array
    {
        return [
            'regular' => (float) (ConceptoPago::where('tipo', 'academia')->value('monto_base') ?? 0),
            'libre'   => (float) (ConceptoPago::where('tipo', 'academia_libre')->value('monto_base') ?? 0),
        ];
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
