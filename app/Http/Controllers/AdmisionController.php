<?php

namespace App\Http\Controllers;

use App\Models\AdmisionExamen;
use App\Models\AnioEscolar;
use App\Models\ConceptoPago;
use App\Models\Grado;
use App\Models\Postulante;
use App\Models\PostulanteDocumento;
use App\Models\PostulanteEtapa;
use App\Models\PostulanteExamen;
use App\Models\PostulantePago;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * MODULO 2 - Admision (RF-11 a RF-20).
 *
 * El expediente se guarda desde el primer paso, asi que una postulacion
 * a medias se puede retomar despues en lugar de perderse en la sesion.
 */
class AdmisionController extends Controller
{
    private const NIVELES = ['Inicial', 'Primaria', 'Secundaria'];

    // ── Listado ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $estado = $request->query('estado');
        $nivel  = $request->query('nivel');
        $origen = $request->query('origen');
        $buscar = trim((string) $request->query('q', ''));

        $postulantes = Postulante::with(['sede', 'grado'])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($nivel, fn ($q) => $q->where('nivel', $nivel))
            ->when($origen, fn ($q) => $q->where('origen', $origen))
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('dni', 'like', "%{$buscar}%")
                        ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admision.index', [
            'postulantes' => $postulantes,
            'resumen'     => [
                'total'       => Postulante::count(),
                'en_proceso'  => Postulante::where('estado', 'en_proceso')->count(),
                'admitidos'   => Postulante::where('estado', 'admitido')->count(),
                'borradores'  => Postulante::where('estado', 'borrador')->count(),
                'web'         => Postulante::where('estado', 'preinscrito')->count(),
            ],
            'estados' => Postulante::ESTADOS,
            'origenes' => Postulante::ORIGENES,
            'niveles' => self::NIVELES,
            'estado'  => $estado,
            'nivel'   => $nivel,
            'origen'  => $origen,
            'buscar'  => $buscar,
        ]);
    }

    // ── Asistente de 5 pasos (RF-12) ──────────────────────────────────

    /** Paso 1: postulante, sede y grado (RF-11). */
    public function crear()
    {
        return view('admision.paso1', [
            'postulante' => new Postulante(),
            'sedes'      => Sede::where('activo', true)->orderBy('orden')->get(),
            'grados'     => Grado::orderBy('orden')->get(),
            'niveles'    => self::NIVELES,
            'paso'       => 1,
        ]);
    }

    public function guardarPaso1(Request $request, ?Postulante $postulante = null)
    {
        $data = $this->validarPaso1($request);

        if ($error = $this->sedeAtiendeNivel($data['sede_id'] ?? null, $data['nivel'])) {
            return back()->withInput()->withErrors(['sede_id' => $error]);
        }

        if ($postulante && $postulante->exists) {
            $postulante->update($data);
        } else {
            $anio = $this->anioActivo();

            $postulante = Postulante::create($data + [
                'anio_escolar_id' => $anio->id,
                'codigo'          => Postulante::siguienteCodigo($anio->id, $anio->nombre),
                'estado'          => 'borrador',
                'registrado_por'  => Auth::id(),
            ]);

            $this->sembrarEtapas($postulante);
        }

        $this->avanzar($postulante, 2);

        return redirect()->route('admision.paso2', $postulante);
    }

    /** Paso 2: apoderado que presenta la postulacion. */
    public function paso2(Postulante $postulante)
    {
        return view('admision.paso2', ['postulante' => $postulante, 'paso' => 2]);
    }

    public function guardarPaso2(Request $request, Postulante $postulante)
    {
        $data = $request->validate([
            'apoderado_parentesco'       => ['required', 'in:Madre,Padre,Tutor'],
            'apoderado_dni'              => ['nullable', 'string', 'max:20'],
            'apoderado_nombres'          => ['required', 'string', 'max:120'],
            'apoderado_apellidos'        => ['required', 'string', 'max:120'],
            'apoderado_fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'apoderado_sexo'             => ['nullable', 'in:M,F'],
            'apoderado_estado_civil'     => ['nullable', 'in:Soltero(a),Casado(a),Conviviente'],
            'apoderado_ciudad'           => ['nullable', 'string', 'max:120'],
            'apoderado_direccion'        => ['nullable', 'string', 'max:255'],
            'apoderado_telefono'         => ['nullable', 'string', 'max:40'],
            'apoderado_email'            => ['nullable', 'email', 'max:150'],
            // Segundo apoderado (opcional)
            'tiene_segundo_apoderado'     => ['nullable', 'in:0,1'],
            'apoderado2_parentesco'       => ['required_if:tiene_segundo_apoderado,1', 'nullable', 'in:Madre,Padre,Tutor'],
            'apoderado2_dni'              => ['nullable', 'string', 'max:20'],
            'apoderado2_nombres'          => ['required_if:tiene_segundo_apoderado,1', 'nullable', 'string', 'max:120'],
            'apoderado2_apellidos'        => ['required_if:tiene_segundo_apoderado,1', 'nullable', 'string', 'max:120'],
            'apoderado2_fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'apoderado2_sexo'             => ['nullable', 'in:M,F'],
            'apoderado2_estado_civil'     => ['nullable', 'in:Soltero(a),Casado(a),Conviviente'],
            'apoderado2_ciudad'           => ['nullable', 'string', 'max:120'],
            'apoderado2_direccion'        => ['nullable', 'string', 'max:255'],
            'apoderado2_telefono'         => ['nullable', 'string', 'max:40'],
            'apoderado2_email'            => ['nullable', 'email', 'max:150'],
        ], [
            'apoderado2_parentesco.required_if' => 'Indica la relacion del segundo apoderado.',
            'apoderado2_nombres.required_if'    => 'Los nombres del segundo apoderado son obligatorios.',
            'apoderado2_apellidos.required_if'  => 'Los apellidos del segundo apoderado son obligatorios.',
        ]);

        // Si se desmarca "hay un segundo apoderado", se limpian sus datos.
        unset($data['tiene_segundo_apoderado']);
        if (!$request->boolean('tiene_segundo_apoderado')) {
            foreach (array_keys($data) as $campo) {
                if (str_starts_with($campo, 'apoderado2_')) {
                    $data[$campo] = null;
                }
            }
        }

        $postulante->update($data);
        $this->avanzar($postulante, 3);

        return redirect()->route('admision.paso3', $postulante);
    }

    /** Paso 3: documentos (RF-14). */
    public function paso3(Postulante $postulante)
    {
        return view('admision.paso3', [
            'postulante' => $postulante->load('documentos'),
            'tipos'      => PostulanteDocumento::tipos(),
            'faltantes'  => $postulante->documentosFaltantes(),
            'paso'       => 3,
        ]);
    }

    public function subirDocumento(Request $request, Postulante $postulante)
    {
        $request->validate([
            'tipo'    => ['required', 'string', 'in:' . implode(',', array_keys(PostulanteDocumento::tipos()))],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ]);

        // Un documento por tipo: al resubir se reemplaza el anterior.
        $previo = $postulante->documentos()->where('tipo', $request->input('tipo'))->first();
        if ($previo) {
            $this->borrarArchivo($previo->archivo);
            $previo->delete();
        }

        $archivo = $request->file('archivo');

        $postulante->documentos()->create([
            'tipo'            => $request->input('tipo'),
            'archivo'         => $archivo->store('admision/documentos', 'public'),
            'nombre_original' => $archivo->getClientOriginalName(),
            'subido_por'      => Auth::id(),
        ]);

        return back()->with('status', 'Documento cargado.');
    }

    public function eliminarDocumento(PostulanteDocumento $documento)
    {
        $this->borrarArchivo($documento->archivo);
        $documento->delete();

        // Se elimina desde el paso 3 o desde el expediente: volver a donde se estaba.
        return back()->with('status', 'Documento eliminado.');
    }

    public function continuarPaso3(Postulante $postulante)
    {
        $this->avanzar($postulante, 4);

        return redirect()->route('admision.paso4', $postulante);
    }

    /** Paso 4: pago del derecho de admision y voucher (RF-13). */
    public function paso4(Postulante $postulante)
    {
        return view('admision.paso4', [
            'postulante' => $postulante->load('pagos'),
            'tarifa'     => $this->tarifaAdmision(),
            'metodos'    => PostulantePago::METODOS,
            'paso'       => 4,
        ]);
    }

    public function registrarPago(Request $request, Postulante $postulante)
    {
        $data = $request->validate([
            'monto'            => ['required', 'numeric', 'min:0'],
            'metodo'           => ['required', 'in:' . implode(',', array_keys(PostulantePago::METODOS))],
            'numero_operacion' => ['nullable', 'string', 'max:60'],
            'fecha'            => ['required', 'date'],
            'observacion'      => ['nullable', 'string', 'max:255'],
            'voucher'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $postulante->pagos()->create([
            'monto'            => $data['monto'],
            'metodo'           => $data['metodo'],
            'numero_operacion' => $data['numero_operacion'] ?? null,
            'fecha'            => $data['fecha'],
            'observacion'      => $data['observacion'] ?? null,
            'voucher'          => $request->hasFile('voucher')
                ? $request->file('voucher')->store('admision/vouchers', 'public')
                : null,
            'estado'           => 'pendiente',
            'registrado_por'   => Auth::id(),
        ]);

        return back()->with('status', 'Pago registrado. Queda pendiente de validacion.');
    }

    public function validarPago(Request $request, PostulantePago $pago)
    {
        $request->validate(['accion' => ['required', 'in:validado,rechazado']]);

        $pago->update([
            'estado'       => $request->input('accion'),
            'validado_por' => Auth::id(),
        ]);

        return back()->with('status', 'Pago ' . $request->input('accion') . '.');
    }

    public function continuarPaso4(Postulante $postulante)
    {
        $this->avanzar($postulante, 5);

        return redirect()->route('admision.paso5', $postulante);
    }

    /** Paso 5: cronograma de etapas (RF-15, RF-16, RF-19). */
    public function paso5(Postulante $postulante)
    {
        return view('admision.paso5', [
            'postulante' => $postulante->load('etapas'),
            'etapas'     => Postulante::ETAPAS,
            'estados'    => PostulanteEtapa::ESTADOS,
            'paso'       => 5,
        ]);
    }

    public function finalizar(Postulante $postulante)
    {
        if ($postulante->enCaptura()) {
            $postulante->update(['estado' => 'en_proceso']);
        }

        $postulante->update(['paso_actual' => Postulante::TOTAL_PASOS]);

        return redirect()->route('admision.expediente', $postulante)
            ->with('status', 'Postulacion registrada. El expediente queda en proceso.');
    }

    // ── Expediente ────────────────────────────────────────────────────

    public function expediente(Postulante $postulante)
    {
        $postulante->load(['sede', 'grado', 'documentos', 'etapas', 'pagos', 'examenes.examen', 'matricula.estudiante', 'matricula.grado', 'matricula.seccion']);

        return view('admision.expediente', [
            'postulante' => $postulante,
            'etapas'     => Postulante::ETAPAS,
            'estados'    => PostulanteEtapa::ESTADOS,
            'examenes'   => AdmisionExamen::where('nivel', $postulante->nivel)
                ->where('activo', true)
                ->orderByDesc('id')
                ->get(),
            'faltantes'  => $postulante->documentosFaltantes(),
            'tipos'      => PostulanteDocumento::tipos(),
        ]);
    }

    /** Programa o cierra una etapa del proceso. */
    public function guardarEtapa(Request $request, Postulante $postulante)
    {
        $data = $request->validate([
            'etapa'            => ['required', 'in:' . implode(',', array_keys(Postulante::ETAPAS))],
            'estado'           => ['required', 'in:' . implode(',', array_keys(PostulanteEtapa::ESTADOS))],
            'fecha_programada' => ['nullable', 'date'],
            'fecha_realizada'  => ['nullable', 'date'],
            'responsable'      => ['nullable', 'string', 'max:120'],
            'observacion'      => ['nullable', 'string', 'max:1000'],
        ]);

        // El formulario solo envia estado y fecha programada; los demas campos
        // se conservan si ya existian. Al completar, la fecha realizada se
        // registra sola para no pedirla aparte.
        $valores = [
            'estado'           => $data['estado'],
            'fecha_programada' => $data['fecha_programada'] ?? null,
        ];
        foreach (['fecha_realizada', 'responsable', 'observacion'] as $campo) {
            if ($request->has($campo)) {
                $valores[$campo] = $data[$campo] ?? null;
            }
        }
        if ($data['estado'] === 'completada' && !$request->filled('fecha_realizada')) {
            $existente = PostulanteEtapa::where('postulante_id', $postulante->id)->where('etapa', $data['etapa'])->first();
            $valores['fecha_realizada'] = $existente?->fecha_realizada ?? now();
        }

        PostulanteEtapa::updateOrCreate(
            ['postulante_id' => $postulante->id, 'etapa' => $data['etapa']],
            $valores
        );

        return back()->with('status', 'Etapa actualizada.');
    }

    /** RF-17: registra el resultado del examen de admision. */
    public function registrarExamen(Request $request, Postulante $postulante)
    {
        $data = $request->validate([
            'examen_id'   => ['required', 'exists:admision_examenes,id'],
            'fecha'       => ['nullable', 'date'],
            'puntaje'     => ['nullable', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string', 'max:500'],
        ]);

        $examen = AdmisionExamen::findOrFail($data['examen_id']);

        if ($examen->nivel !== $postulante->nivel) {
            return back()->withErrors([
                'examen_id' => 'Ese examen es de otro nivel educativo.',
            ]);
        }

        if (isset($data['puntaje']) && $data['puntaje'] > (float) $examen->puntaje_maximo) {
            return back()->withErrors([
                'puntaje' => 'El puntaje supera el maximo del examen (' . $examen->puntaje_maximo . ').',
            ]);
        }

        PostulanteExamen::updateOrCreate(
            ['postulante_id' => $postulante->id, 'examen_id' => $examen->id],
            [
                'fecha'        => $data['fecha'] ?? null,
                'puntaje'      => $data['puntaje'] ?? null,
                'observacion'  => $data['observacion'] ?? null,
                'evaluado_por' => Auth::id(),
            ]
        );

        return back()->with('status', 'Resultado del examen registrado.');
    }

    /** RF-18: publica el resultado al apoderado. */
    public function publicarResultado(Request $request, Postulante $postulante)
    {
        $data = $request->validate([
            'estado'      => ['required', 'in:admitido,no_admitido,desistido'],
            'canal'       => ['nullable', 'string', 'max:120'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $postulante->update([
            'estado'                 => $data['estado'],
            'resultado_canal'        => $data['canal'] ?? null,
            'observacion'            => $data['observacion'] ?? $postulante->observacion,
            'resultado_publicado_at' => now(),
        ]);

        return back()->with('status', 'Resultado publicado.' . (!empty($data['canal']) ? ' Comunicado por ' . $data['canal'] . '.' : ''));
    }

    /**
     * Pasa un admitido al asistente de matricula con los pasos 1 y 2 ya
     * llenados con los datos del expediente. La matricula se enlaza al
     * postulante cuando el asistente la crea (guardarPaso2).
     */
    public function matricular(Postulante $postulante)
    {
        if ($postulante->matricula_id) {
            return redirect()->route($this->prefijoMatricula() . 'matriculas.resumen', $postulante->matricula_id)
                ->with('status', 'Este postulante ya tiene una matricula generada.');
        }

        if ($postulante->estado !== 'admitido') {
            return back()->withErrors(['estado' => 'Solo se puede matricular a un postulante admitido.']);
        }

        $postulante->loadMissing('grado');

        // Se descarta cualquier borrador del asistente que haya quedado en la
        // sesion; el comando matriculas:limpiar-abandonadas se encarga de su registro.
        session()->forget([
            'matricula.id', 'matricula.nivel', 'matricula.grado', 'matricula.seccion',
            'matricula.anio_escolar_id', 'matricula.periodo_academico_id',
            'matricula.estudiante_id', 'matricula.apoderado_id', 'matricula.deuda_matricula_id',
            'matricula.pago_id', 'matricula.estudiante_credenciales', 'matricula.return_to',
            'matricula.return_label', 'matricula.estudiante_creado_en_wizard',
            'matricula.apoderado_creado_en_wizard', 'matricula.paso2_form', 'matricula.paso3_form',
            'matricula.postulante_id',
        ]);

        session([
            'matricula.postulante_id' => $postulante->id,
            'matricula.nivel'         => $postulante->nivel,
            'matricula.grado'         => $postulante->grado?->nombre ?? '',
            'matricula.paso2_form'    => $postulante->datosParaMatricula(),
        ]);

        return redirect()->route($this->prefijoMatricula() . 'matriculas.paso1')
            ->with('status', "Datos de {$postulante->nombreCompleto()} cargados desde admision. Elige la seccion y continua.");
    }

    /** El asistente de matricula vive bajo dos prefijos segun el rol. */
    private function prefijoMatricula(): string
    {
        return Auth::user()?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
    }

    public function eliminar(Postulante $postulante)
    {
        foreach ($postulante->documentos as $documento) {
            $this->borrarArchivo($documento->archivo);
        }
        foreach ($postulante->pagos as $pago) {
            $this->borrarArchivo($pago->voucher);
        }

        $postulante->delete();

        return redirect()->route('admision.index')->with('status', 'Expediente eliminado.');
    }

    // ── Examenes por nivel (RF-17) ────────────────────────────────────

    public function examenes()
    {
        return view('admision.examenes', [
            'examenes' => AdmisionExamen::with('anioEscolar')->orderByDesc('id')->get(),
            'anios'    => AnioEscolar::orderByDesc('id')->get(),
            'niveles'  => self::NIVELES,
        ]);
    }

    public function guardarExamen(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'nivel'           => ['required', 'in:' . implode(',', self::NIVELES)],
            'titulo'          => ['required', 'string', 'max:180'],
            'descripcion'     => ['nullable', 'string'],
            'puntaje_maximo'  => ['required', 'numeric', 'min:1'],
            'puntaje_minimo'  => ['required', 'numeric', 'min:0'],
        ]);

        if ($data['puntaje_minimo'] > $data['puntaje_maximo']) {
            return back()->withInput()->withErrors([
                'puntaje_minimo' => 'El puntaje minimo no puede superar al maximo.',
            ]);
        }

        AdmisionExamen::create($data + ['activo' => true]);

        return back()->with('status', 'Examen de admision registrado.');
    }

    public function eliminarExamen(AdmisionExamen $examen)
    {
        $examen->delete();

        return back()->with('status', 'Examen eliminado.');
    }

    // ── Apoyo ─────────────────────────────────────────────────────────

    private function validarPaso1(Request $request): array
    {
        $data = $request->validate([
            'nombres'             => ['required', 'string', 'max:120'],
            'apellidos'           => ['required', 'string', 'max:120'],
            'dni'                 => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento'    => ['nullable', 'date', 'before:today'],
            'sexo'                => ['nullable', 'in:M,F'],
            'direccion'           => ['nullable', 'string', 'max:255'],
            'colegio_procedencia' => ['nullable', 'string', 'max:180'],
            'sede_id'             => ['nullable', 'exists:sedes,id'],
            'nivel'               => ['required', 'in:' . implode(',', self::NIVELES)],
            'grado_id'            => ['nullable', 'exists:grados,id'],
        ]);

        // Las claves opcionales ausentes no vuelven de validate().
        foreach (['dni', 'fecha_nacimiento', 'sexo', 'direccion', 'colegio_procedencia', 'sede_id', 'grado_id'] as $campo) {
            $data[$campo] = $data[$campo] ?? null;
        }

        return $data;
    }

    /** RF-11: la sede elegida debe atender el nivel al que se postula. */
    private function sedeAtiendeNivel(?int $sedeId, string $nivel): ?string
    {
        if (!$sedeId) {
            return null;
        }

        $sede = Sede::find($sedeId);

        if ($sede && !$sede->atiendeNivel($nivel)) {
            return $sede->nombre . ' no atiende el nivel ' . $nivel . '.';
        }

        return null;
    }

    private function sembrarEtapas(Postulante $postulante): void
    {
        foreach (array_keys(Postulante::ETAPAS) as $etapa) {
            $postulante->etapas()->create(['etapa' => $etapa, 'estado' => 'pendiente']);
        }
    }

    /** El paso guardado solo avanza, nunca retrocede al reeditar. */
    private function avanzar(Postulante $postulante, int $paso): void
    {
        if ($postulante->paso_actual < $paso) {
            $postulante->update(['paso_actual' => $paso]);
        }
    }

    private function anioActivo(): AnioEscolar
    {
        return AnioEscolar::where('activo', 1)->first()
            ?? AnioEscolar::orderByDesc('id')->firstOrFail();
    }

    private function tarifaAdmision(): float
    {
        return (float) (ConceptoPago::where('tipo', 'admision')->value('monto_base') ?? 0);
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
