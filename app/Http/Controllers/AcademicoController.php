<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Evaluacion;
use App\Models\EvaluacionResultado;
use App\Models\Grado;
use App\Models\InformeDescriptivo;
use App\Models\ProyectoPedagogico;
use App\Models\Recuperacion;
use App\Models\Rubrica;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MODULO 5 - Academico: lo que faltaba del documento.
 *
 * RF-34 informes descriptivos de Inicial, RF-35 rubricas, RF-37 examenes
 * y ranking, RF-38 recuperacion y nivelacion, RF-39 proyectos, y RF-31
 * la modalidad unidocencia / polidocencia por grado.
 */
class AcademicoController extends Controller
{
    // ── RF-31: unidocencia y polidocencia ─────────────────────────────

    public function modalidades()
    {
        return view('academico.modalidades', [
            'grados' => Grado::orderBy('orden')->get(),
        ]);
    }

    public function guardarModalidad(Request $request, Grado $grado)
    {
        $request->validate([
            'modalidad' => ['required', 'in:unidocencia,polidocencia'],
        ]);

        $grado->update(['modalidad' => $request->input('modalidad')]);

        return back()->with('status', 'Modalidad del grado actualizada.');
    }

    // ── RF-34: informes descriptivos de Inicial ───────────────────────

    public function informes(Request $request)
    {
        $tipo = $request->query('tipo');

        $informes = InformeDescriptivo::with(['estudiante', 'docente'])
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // RF-34 aplica a Inicial: solo se ofrecen esos estudiantes.
        $gradosInicial = Grado::where('nivel', 'Inicial')->pluck('id');

        return view('academico.informes', [
            'informes'    => $informes,
            'tipos'       => InformeDescriptivo::TIPOS,
            'tipo'        => $tipo,
            'anios'       => AnioEscolar::orderByDesc('id')->get(),
            'estudiantes' => Estudiante::where('estado', 'activo')
                ->whereHas('matriculas', fn ($m) => $m->whereIn('grado_id', $gradosInicial))
                ->orderBy('apellidos')
                ->get(['id', 'nombres', 'apellidos']),
        ]);
    }

    public function guardarInforme(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'estudiante_id'   => ['required', 'exists:estudiantes,id'],
            'tipo'            => ['required', 'in:mensual,bimestral'],
            'periodo'         => ['required', 'string', 'max:60'],
            'logros'          => ['nullable', 'string'],
            'dificultades'    => ['nullable', 'string'],
            'recomendaciones' => ['nullable', 'string'],
        ]);

        // Un informe por estudiante, anio, tipo y periodo: se actualiza si existe.
        InformeDescriptivo::updateOrCreate(
            [
                'estudiante_id'   => $data['estudiante_id'],
                'anio_escolar_id' => $data['anio_escolar_id'],
                'tipo'            => $data['tipo'],
                'periodo'         => $data['periodo'],
            ],
            [
                'logros'          => $data['logros'] ?? null,
                'dificultades'    => $data['dificultades'] ?? null,
                'recomendaciones' => $data['recomendaciones'] ?? null,
            ]
        );

        return back()->with('status', 'Informe descriptivo guardado.');
    }

    public function eliminarInforme(InformeDescriptivo $informe)
    {
        $informe->delete();

        return back()->with('status', 'Informe eliminado.');
    }

    // ── RF-35: rubricas ───────────────────────────────────────────────

    public function rubricas()
    {
        return view('academico.rubricas', [
            'rubricas' => Rubrica::with('curso')->orderByDesc('id')->paginate(15),
            'cursos'   => Curso::orderBy('nombre')->get(),
            'anios'    => AnioEscolar::orderByDesc('id')->get(),
            'niveles'  => Rubrica::NIVELES_LOGRO,
        ]);
    }

    public function guardarRubrica(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'curso_id'        => ['nullable', 'exists:cursos,id'],
            'nivel'           => ['nullable', 'string', 'in:Inicial,Primaria,Secundaria'],
            'titulo'          => ['required', 'string', 'max:180'],
            'descripcion'     => ['nullable', 'string'],
            'criterio'        => ['nullable', 'array'],
            'criterio.*'      => ['nullable', 'string', 'max:255'],
        ]);

        // Cada criterio guarda su descriptor por nivel de desempeno (1 a 4).
        $criterios = [];
        foreach ($request->input('criterio', []) as $nombre => $descriptores) {
            if (is_string($nombre) && trim($nombre) !== '') {
                $criterios[] = ['nombre' => $nombre, 'descriptores' => $descriptores];
            }
        }

        Rubrica::create([
            'anio_escolar_id' => $data['anio_escolar_id'],
            'curso_id'        => $data['curso_id'] ?? null,
            'nivel'           => $data['nivel'] ?? null,
            'titulo'          => $data['titulo'],
            'descripcion'     => $data['descripcion'] ?? null,
            'criterios'       => $criterios ?: null,
            'activo'          => true,
            'creado_por'      => Auth::id(),
        ]);

        return back()->with('status', 'Rubrica registrada.');
    }

    public function eliminarRubrica(Rubrica $rubrica)
    {
        $rubrica->delete();

        return back()->with('status', 'Rubrica eliminada.');
    }

    // ── RF-37: examenes y ranking ─────────────────────────────────────

    public function evaluaciones(Request $request)
    {
        $tipo = $request->query('tipo');

        return view('academico.evaluaciones', [
            'evaluaciones' => Evaluacion::with(['curso', 'seccion'])
                ->withCount('resultados')
                ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
                ->orderByDesc('fecha')
                ->paginate(15)
                ->withQueryString(),
            'tipos'     => Evaluacion::TIPOS,
            'tipo'      => $tipo,
            'cursos'    => Curso::orderBy('nombre')->get(),
            'secciones' => Seccion::with('grado')->orderBy('id')->get(),
            'anios'     => AnioEscolar::orderByDesc('id')->get(),
        ]);
    }

    public function guardarEvaluacion(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'curso_id'        => ['nullable', 'exists:cursos,id'],
            'seccion_id'      => ['nullable', 'exists:secciones,id'],
            'tipo'            => ['required', 'in:' . implode(',', array_keys(Evaluacion::TIPOS))],
            'titulo'          => ['required', 'string', 'max:180'],
            'periodo'         => ['nullable', 'string', 'max:60'],
            'fecha'           => ['required', 'date'],
            'puntaje_maximo'  => ['required', 'numeric', 'min:1'],
        ]);

        Evaluacion::create($data);

        return back()->with('status', 'Evaluacion registrada.');
    }

    /** Ranking de una evaluacion y carga de puntajes. */
    public function ranking(Evaluacion $evaluacion)
    {
        $evaluacion->load(['curso', 'seccion.grado']);

        // Los candidatos son los matriculados en la seccion de la evaluacion
        $estudiantes = Estudiante::where('estado', 'activo')
            ->when($evaluacion->seccion_id, fn ($q) => $q->whereHas(
                'matriculas',
                fn ($m) => $m->where('seccion_id', $evaluacion->seccion_id)
            ))
            ->orderBy('apellidos')
            ->get(['id', 'nombres', 'apellidos']);

        return view('academico.ranking', [
            'evaluacion'  => $evaluacion,
            'ranking'     => $evaluacion->ranking(),
            'estudiantes' => $estudiantes,
        ]);
    }

    public function guardarResultado(Request $request, Evaluacion $evaluacion)
    {
        $data = $request->validate([
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'puntaje'       => ['required', 'numeric', 'min:0'],
            'observacion'   => ['nullable', 'string', 'max:200'],
        ]);

        if ($data['puntaje'] > (float) $evaluacion->puntaje_maximo) {
            return back()->withErrors([
                'puntaje' => 'El puntaje supera el maximo de la evaluacion (' . $evaluacion->puntaje_maximo . ').',
            ]);
        }

        EvaluacionResultado::updateOrCreate(
            ['evaluacion_id' => $evaluacion->id, 'estudiante_id' => $data['estudiante_id']],
            ['puntaje' => $data['puntaje'], 'observacion' => $data['observacion'] ?? null]
        );

        return back()->with('status', 'Puntaje registrado.');
    }

    public function eliminarEvaluacion(Evaluacion $evaluacion)
    {
        $evaluacion->delete();

        return redirect()->route('academico.evaluaciones')->with('status', 'Evaluacion eliminada.');
    }

    // ── RF-38: recuperacion, refuerzo y nivelacion ────────────────────

    public function recuperaciones(Request $request)
    {
        $tipo = $request->query('tipo');

        return view('academico.recuperaciones', [
            'recuperaciones' => Recuperacion::with(['estudiante', 'curso'])
                ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString(),
            'tipos'  => Recuperacion::TIPOS,
            'tipo'   => $tipo,
            'cursos' => Curso::orderBy('nombre')->get(),
            'anios'  => AnioEscolar::orderByDesc('id')->get(),
            'costo'  => (float) (\App\Models\ConceptoPago::where('tipo', 'recuperacion')->value('monto_base') ?? 0),
            'estudiantes' => Estudiante::where('estado', 'activo')
                ->orderBy('apellidos')
                ->get(['id', 'nombres', 'apellidos']),
            'resumen' => [
                'total'     => Recuperacion::count(),
                'pagadas'   => Recuperacion::where('pagado', true)->count(),
                'aprobadas' => Recuperacion::whereNotNull('nota_final')->where('nota_final', '>=', 11)->count(),
            ],
        ]);
    }

    public function guardarRecuperacion(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'estudiante_id'   => ['required', 'exists:estudiantes,id'],
            'curso_id'        => ['nullable', 'exists:cursos,id'],
            'tipo'            => ['required', 'in:' . implode(',', array_keys(Recuperacion::TIPOS))],
            'periodo'         => ['nullable', 'string', 'max:60'],
            'fecha'           => ['nullable', 'date'],
            'nota_original'   => ['nullable', 'numeric', 'min:0', 'max:20'],
            'nota_final'      => ['nullable', 'numeric', 'min:0', 'max:20'],
            'costo'           => ['nullable', 'numeric', 'min:0'],
            'pagado'          => ['nullable', 'boolean'],
            'observacion'     => ['nullable', 'string'],
        ]);

        foreach (['curso_id', 'periodo', 'fecha', 'nota_original', 'nota_final', 'observacion'] as $campo) {
            $data[$campo] = $data[$campo] ?? null;
        }

        $data['costo']  = $data['costo'] ?? 0;
        $data['pagado'] = $request->boolean('pagado');

        Recuperacion::create($data + ['registrado_por' => Auth::id()]);

        return back()->with('status', 'Registro de recuperacion guardado.');
    }

    public function eliminarRecuperacion(Recuperacion $recuperacion)
    {
        $recuperacion->delete();

        return back()->with('status', 'Registro eliminado.');
    }

    // ── RF-39: proyectos y metodos ────────────────────────────────────

    public function proyectos(Request $request)
    {
        return view('academico.proyectos', [
            'proyectos' => ProyectoPedagogico::with('curso')
                ->orderByDesc('id')
                ->paginate(15),
            'tipos'  => ProyectoPedagogico::TIPOS,
            'cursos' => Curso::orderBy('nombre')->get(),
            'anios'  => AnioEscolar::orderByDesc('id')->get(),
        ]);
    }

    public function guardarProyecto(Request $request)
    {
        $data = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anios_escolares,id'],
            'titulo'          => ['required', 'string', 'max:180'],
            'tipo'            => ['required', 'in:proyecto,metodo'],
            'nivel'           => ['nullable', 'string', 'in:Inicial,Primaria,Secundaria'],
            'curso_id'        => ['nullable', 'exists:cursos,id'],
            'descripcion'     => ['nullable', 'string'],
            'fecha_inicio'    => ['nullable', 'date'],
            'fecha_fin'       => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'responsable'     => ['nullable', 'string', 'max:150'],
            'estado'          => ['required', 'in:en_curso,terminado'],
        ]);

        foreach (['nivel', 'curso_id', 'descripcion', 'fecha_inicio', 'fecha_fin', 'responsable'] as $campo) {
            $data[$campo] = $data[$campo] ?? null;
        }

        ProyectoPedagogico::create($data);

        return back()->with('status', 'Proyecto registrado.');
    }

    public function eliminarProyecto(ProyectoPedagogico $proyecto)
    {
        $proyecto->delete();

        return back()->with('status', 'Proyecto eliminado.');
    }
}
