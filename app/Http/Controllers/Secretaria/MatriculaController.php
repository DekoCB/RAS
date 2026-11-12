<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\AnioEscolar;
use App\Models\Apoderado;
use App\Models\ConceptoPago;
use App\Models\CronogramaPension;
use App\Models\Curso;
use App\Models\Deuda;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\InventarioMovimiento;
use App\Models\InventarioCategoria;
use App\Models\InventarioProducto;
use App\Models\Matricula;
use App\Models\MatriculaCargo;
use App\Models\MatriculaDescuento;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use App\Models\Postulante;
use App\Models\Seccion;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Tarifa;
use App\Models\Descuento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Throwable;

class MatriculaController extends Controller
{
    private function routePrefix(): string
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
    }

    private function seccionesIndexRoute(): string
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user?->hasRole('SuperAdmin')
            ? 'admin-sistema.gestionar-secciones.index'
            : 'gestionar-secciones.index';
    }

    public function index()
    {
        $req       = request();
        $dni       = $req->input('dni');
        $gradoId   = $req->input('grado_id');
        $seccionId = $req->input('seccion_id');
        $nivel     = $req->input('nivel', '');

        $nivelesValidos = ['Inicial', 'Primaria', 'Secundaria'];
        if (!in_array($nivel, $nivelesValidos, true)) {
            $nivel = '';
        }

        // ── Mini-card counts (año actual) ──────────────────────────
        $anioActualId = AnioEscolar::where('activo', true)->value('id');

        $baseCount = Matricula::when($anioActualId, fn($q) => $q->where('anio_escolar_id', $anioActualId));
        $countTotal     = (clone $baseCount)->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA, Matricula::ESTADO_RETIRADA, Matricula::ESTADO_EN_PROCESO])->count();
        $countRegistradas = (clone $baseCount)->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA])->count();
        $countRetiradas   = (clone $baseCount)->where('estado', Matricula::ESTADO_RETIRADA)->count();

        // ── Base para lista (sin filtro de nivel) ──────────────────
        $baseList = Matricula::whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA])
            ->when($dni,       fn($q) => $q->whereHas('estudiante', fn($sq) => $sq->where('dni', 'like', "%{$dni}%")))
            ->when($gradoId,   fn($q) => $q->where('grado_id',   $gradoId))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId));

        // ── Conteos por nivel para los tabs ────────────────────────
        $conteosPorNivel = [];
        foreach ($nivelesValidos as $n) {
            $conteosPorNivel[$n] = (clone $baseList)
                ->whereHas('grado', fn($g) => $g->where('nivel', $n))
                ->count();
        }

        // ── Lista paginada ─────────────────────────────────────────
        $matriculas = Matricula::with(['estudiante', 'grado', 'seccion', 'anioEscolar', 'periodoAcademico', 'registradoPor'])
            ->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA])
            ->when($dni,       fn($q) => $q->whereHas('estudiante', fn($sq) => $sq->where('dni', 'like', "%{$dni}%")))
            ->when($gradoId,   fn($q) => $q->where('grado_id',   $gradoId))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId))
            ->when($nivel !== '', fn($q) => $q->whereHas('grado', fn($g) => $g->where('nivel', $nivel)))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // ── Pre-matriculas: lo que llega del formulario web de Admision ──
        $prePendientes = $this->prePendientes();
        $preMatriculas = $this->preMatriculasQuery($dni, $gradoId)
            ->paginate(10, ['*'], 'pre')
            ->withQueryString();

        // ── Secciones para modal gestionar + filtro ────────────────
        $secciones = Seccion::with(['grado', 'aula'])
            ->withCount('matriculas')
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        $seccionesPorNivel = $secciones->groupBy(fn($s) => $s->grado?->nivel ?: 'Sin nivel');
        $nivelActivo       = $seccionesPorNivel->keys()->first();

        // ── Grados y secciones para los filtros ───────────────────
        $grados    = Grado::orderBy('nombre')->get(['id', 'nombre', 'nivel']);
        $seccionesParaFiltro = $gradoId
            ? $secciones->where('grado_id', $gradoId)->values()
            : $secciones;

        return view('secretaria.matriculas.index', compact(
            'matriculas',
            'seccionesPorNivel',
            'nivelActivo',
            'countTotal',
            'countRegistradas',
            'countRetiradas',
            'grados',
            'seccionesParaFiltro',
            'nivel',
            'conteosPorNivel',
            'preMatriculas',
            'prePendientes',
        ));
    }

    /**
     * Pantalla propia de Pre-matriculas (Alumnado > Pre-matriculas).
     *
     * Lista lo que las familias envian desde el formulario de Admision del
     * sitio web. Nadie queda matriculado por ahi: secretaria revisa el caso
     * aqui y lo continua en el modulo de Admision.
     */
    public function preMatriculas()
    {
        $req    = request();
        $dni    = $req->input('dni');
        $estado = $req->input('estado');

        if (!array_key_exists((string) $estado, Postulante::ESTADOS)) {
            $estado = null;
        }

        $preMatriculas = $this->preMatriculasQuery($dni, null, $estado)
            ->paginate(15, ['*'], 'pre')
            ->withQueryString();

        return view('secretaria.matriculas.pre_matriculas', [
            'preMatriculas' => $preMatriculas,
            'prePendientes' => $this->prePendientes(),
        ]);
    }

    /** Postulantes que entraron por la web, los mas recientes primero. */
    private function preMatriculasQuery(?string $dni = null, $gradoId = null, ?string $estado = null)
    {
        return Postulante::with(['grado', 'sede', 'anioEscolar'])
            ->where('origen', 'web')
            ->when($dni, fn($q) => $q->where(fn($sq) => $sq
                ->where('dni', 'like', "%{$dni}%")
                ->orWhere('apoderado_dni', 'like', "%{$dni}%")))
            ->when($gradoId, fn($q) => $q->where('grado_id', $gradoId))
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->orderByDesc('preinscrito_at')
            ->orderByDesc('id');
    }

    /** Pre-matriculas que todavia esperan que secretaria las atienda. */
    private function prePendientes(): int
    {
        return Postulante::where('origen', 'web')
            ->whereIn('estado', ['preinscrito', 'borrador', 'en_proceso'])
            ->count();
    }

    public function exportarMatriculas()
    {
        $req       = request();
        $dni       = $req->input('dni');
        $gradoId   = $req->input('grado_id');
        $seccionId = $req->input('seccion_id');

        $matriculas = Matricula::with(['estudiante', 'grado', 'seccion', 'anioEscolar', 'periodoAcademico', 'registradoPor'])
            ->where('estado', Matricula::ESTADO_REGISTRADA)
            ->when($dni,       fn($q) => $q->whereHas('estudiante', fn($sq) => $sq->where('dni', 'like', "%{$dni}%")))
            ->when($gradoId,   fn($q) => $q->where('grado_id',   $gradoId))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId))
            ->orderByDesc('id')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matrículas');

        // Cabecera
        $headers = ['#', 'DNI', 'Estudiante', 'Grado', 'Sección', 'Periodo', 'Fecha Matrícula', 'Estado', 'Registrado por'];
        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        foreach ($matriculas as $i => $m) {
            $row = $i + 2;
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $m->estudiante?->dni ?? '-');
            $sheet->setCellValue("C{$row}", trim(($m->estudiante?->nombres ?? '') . ' ' . ($m->estudiante?->apellidos ?? '')));
            $sheet->setCellValue("D{$row}", $m->grado?->nombre ?? '-');
            $sheet->setCellValue("E{$row}", $m->seccion?->nombre ?? '-');
            $sheet->setCellValue("F{$row}", ($m->periodoAcademico?->codigo ?? '') . ($m->periodoAcademico?->nombre ? ' - ' . $m->periodoAcademico->nombre : ''));
            $sheet->setCellValue("G{$row}", $m->fecha_matricula?->format('d/m/Y') ?? $m->created_at?->format('d/m/Y') ?? '-');
            $sheet->setCellValue("H{$row}", strtoupper($m->estado ?? 'registrada'));
            $sheet->setCellValue("I{$row}", $m->registradoPor?->name ?? '-');
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'REPORTE_MATRICULAS_' . now()->format('d-m-Y') . '.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function directorSecciones()
    {
        $secciones = Seccion::with(['grado', 'aula'])
            ->withCount('matriculas')
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();

        $seccionesPorNivel = $secciones->groupBy(fn($seccion) => $seccion->grado?->nivel ?: 'Sin nivel');
        $nivelActivo = $seccionesPorNivel->keys()->first();

        $cursos = Curso::orderBy('nombre')->get(['id', 'nombre']);

        $aniosIds = $secciones
            ->pluck('anio_escolar_id')
            ->filter()
            ->unique()
            ->values();

        $periodosPorAnio = PeriodoAcademico::whereIn('anio_escolar_id', $aniosIds)
            ->orderBy('orden')
            ->get(['id', 'anio_escolar_id', 'nombre', 'codigo', 'orden', 'activo'])
            ->groupBy('anio_escolar_id');

        $periodoActivoPorAnio = collect();
        foreach ($aniosIds as $anioId) {
            $periodoActivoPorAnio->put(
                $anioId,
                PeriodoAcademico::actualParaAnio((int) $anioId)
                    ?? ($periodosPorAnio->get($anioId)?->first())
            );
        }

        $periodosPorSeccion = [];
        $cursosAsignadosPorSeccion = [];

        foreach ($secciones as $seccion) {
            $periodo = $periodoActivoPorAnio->get($seccion->anio_escolar_id);
            if (!$periodo) {
                continue;
            }

            $periodosPorSeccion[$seccion->id] = [
                'id' => (int) $periodo->id,
                'nombre' => (string) $periodo->nombre,
                'codigo' => (string) $periodo->codigo,
            ];

            $cursosAsignadosPorSeccion[$seccion->id] = DB::table('seccion_curso')
                ->where('anio_escolar_id', $seccion->anio_escolar_id)
                ->where('periodo_academico_id', $periodo->id)
                ->where('seccion_id', $seccion->id)
                ->pluck('curso_id')
                ->map(fn($cursoId) => (int) $cursoId)
                ->values()
                ->all();
        }

        /** @var \App\Models\User|null $userAuth */
        $userAuth = Auth::user();
        $seccionesRoutePrefix = $userAuth?->hasRole('SuperAdmin')
            ? 'admin-sistema.gestionar-secciones'
            : 'gestionar-secciones';

        $gradosPorNivel = Grado::orderBy('orden')->orderBy('nombre')
            ->get(['id', 'nombre', 'nivel'])
            ->groupBy(fn($g) => $g->nivel ?: 'Sin nivel')
            ->map(fn($grupo) => $grupo->pluck('nombre')->unique()->values());

        return view('director.secciones.index', compact(
            'seccionesPorNivel',
            'nivelActivo',
            'cursos',
            'periodosPorSeccion',
            'cursosAsignadosPorSeccion',
            'seccionesRoutePrefix',
            'gradosPorNivel'
        ));
    }

    public function resumen(Matricula $matricula)
    {
        $matricula->load([
            'estudiante.apoderados',
            'estudiante.user',
            'grado',
            'seccion',
            'anioEscolar',
            'periodoAcademico',
            'cargos',
            'descuentos',
            'deudas',
        ]);

        $credenciales = null;
        $estudiante = $matricula->estudiante;
        if ($estudiante?->user) {
            $passwordTemporal = null;
            if (!empty($estudiante->password_temporal)) {
                try {
                    $passwordTemporal = Crypt::decryptString($estudiante->password_temporal);
                } catch (DecryptException $exception) {
                    $passwordTemporal = null;
                }
            }

            $credenciales = [
                'email' => $estudiante->user->dni ?? $estudiante->user->email,
                'password' => $passwordTemporal,
            ];
        }

        return view('secretaria.matriculas.resumen', compact('matricula', 'credenciales'));
    }

    public function notaVenta(Matricula $matricula)
    {
        $matricula->load([
            'estudiante.apoderados',
            'grado',
            'seccion',
            'anioEscolar',
            'periodoAcademico',
            'cargos',
            'descuentos',
            'deudas.concepto',
            'deudas.pagos',
        ]);

        $apoderado = $matricula->estudiante?->apoderados
            ?->firstWhere('pivot.principal', true)
            ?? $matricula->estudiante?->apoderados?->first();

        // Deudas de Matrícula y Gastos Internos (conceptos fijos del promotor)
        // Matrícula primero, luego Gastos Internos
        $deudasMatricula = $matricula->deudas
            ->filter(fn ($d) => in_array($d->concepto?->tipo, ['matricula', 'gastos_internos']))
            ->sortBy(fn ($d) => $d->concepto?->tipo === 'matricula' ? 0 : 1);

        // El descuento 5% del hijo menor ya está baked-in en el monto de la deuda (S/.95).
        // Solo restamos otros descuentos (beca, etc.) para no duplicar.
        $totalDescuentos = $matricula->descuentos
            ->where('tipo', '!=', 'hermano_menor')
            ->sum('monto');

        $subtotalDeudas = $deudasMatricula->sum('monto');
        $subtotalCargos = $matricula->cargos->sum('subtotal');
        $totalCargos    = $subtotalDeudas + $subtotalCargos;
        $totalFinal     = max($totalCargos - $totalDescuentos, 0);

        // Pago más reciente vinculado a las deudas de matrícula/gastos
        $pago = $matricula->deudas
            ->flatMap(fn ($d) => $d->pagos)
            ->sortByDesc('id')
            ->first();

        return view('secretaria.matriculas.nota-venta', compact(
            'matricula',
            'apoderado',
            'deudasMatricula',
            'totalCargos',
            'totalDescuentos',
            'totalFinal',
            'pago'
        ));
    }

    public function buscarApoderado(Request $request): JsonResponse
    {
        $dni = preg_replace('/\D/', '', (string) $request->input('dni', ''));
        if (strlen($dni) !== 8) {
            return response()->json(['found' => false]);
        }

        $apoderado = Apoderado::where('dni', $dni)->first();
        if (!$apoderado) {
            return response()->json(['found' => false]);
        }

        $relacion = $apoderado->estudiantes()->orderByPivot('id', 'desc')->value('relacion');

        return response()->json([
            'found'        => true,
            'relacion'     => $relacion,
            'nombres'      => $apoderado->nombres,
            'apellidos'    => $apoderado->apellidos,
            'nacimiento'   => $apoderado->fecha_nacimiento,
            'sexo'         => $apoderado->sexo,
            'estado_civil' => $apoderado->estado_civil,
            'ciudad'       => $apoderado->ciudad,
            'direccion'    => $apoderado->direccion,
            'telefono'     => $apoderado->telefono,
            'email'        => $apoderado->email,
        ]);
    }

    public function resetCredenciales(Matricula $matricula): JsonResponse
    {
        $matricula->load(['estudiante.user']);
        $estudiante = $matricula->estudiante;

        if (!$estudiante) {
            return response()->json(['message' => 'No se encontro el estudiante.'], 404);
        }

        $dni = trim((string) $estudiante->dni);
        if ($dni === '') {
            return response()->json(['message' => 'El estudiante no tiene DNI registrado.'], 422);
        }

        $usuarioLogin = $this->usuarioLoginDesdeDni($dni);
        $user = $estudiante->user_id ? $estudiante->user : null;
        if (!$user) {
            $user = $this->buscarUsuarioEstudiantePorDni($dni);
        }

        $passwordTemporal = $dni;

        if (!$user) {
            $user = User::create([
                'name' => trim($estudiante->nombres . ' ' . $estudiante->apellidos),
                'email' => $usuarioLogin,
                'dni' => $usuarioLogin,
                'password' => $passwordTemporal,
            ]);
            $user->assignRole('Estudiante');
        } else {
            $user->dni = $usuarioLogin;
            if (trim((string) $user->email) === '') {
                $user->email = $usuarioLogin;
            }
            $user->password = $passwordTemporal;
            $user->save();

            if (!$user->hasRole('Estudiante')) {
                $user->assignRole('Estudiante');
            }
        }

        if (!$estudiante->user_id) {
            $estudiante->user_id = $user->id;
        }

        $estudiante->password_temporal = Crypt::encryptString($passwordTemporal);
        $estudiante->save();

        return response()->json([
            'email' => $user->dni ?? $user->email,
            'password' => $passwordTemporal,
        ]);
    }

    public function setCredenciales(Request $request, Matricula $matricula): JsonResponse
    {
        $matricula->load(['estudiante.user']);
        $estudiante = $matricula->estudiante;

        if (!$estudiante) {
            return response()->json(['message' => 'No se encontro el estudiante.'], 404);
        }

        $dni = trim((string) $estudiante->dni);
        if ($dni === '') {
            return response()->json(['message' => 'El estudiante no tiene DNI registrado.'], 422);
        }

        $usuarioLogin = $this->usuarioLoginDesdeDni($dni);
        $user = $estudiante->user_id ? $estudiante->user : null;
        if (!$user) {
            $user = $this->buscarUsuarioEstudiantePorDni($dni);
        }

        if (!$user) {
            $user = User::create([
                'name' => trim($estudiante->nombres . ' ' . $estudiante->apellidos),
                'email' => $usuarioLogin,
                'dni' => $usuarioLogin,
                'password' => $dni,
            ]);
            $user->assignRole('Estudiante');
        } else {
            $user->dni = $usuarioLogin;
            if (trim((string) $user->email) === '') {
                $user->email = $usuarioLogin;
            }
            $user->password = $dni;
            $user->save();

            if (!$user->hasRole('Estudiante')) {
                $user->assignRole('Estudiante');
            }
        }

        if (!$estudiante->user_id) {
            $estudiante->user_id = $user->id;
        }

        $estudiante->password_temporal = Crypt::encryptString($dni);
        $estudiante->save();

        return response()->json([
            'email' => $user->dni ?? $user->email,
            'password' => $dni,
        ]);
    }

    public function paso1()
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (!$anio) {
            $anioActual = (string) date('Y');
            $anio = AnioEscolar::create([
                'nombre' => $anioActual,
                'fecha_inicio' => "{$anioActual}-01-01",
                'fecha_fin' => "{$anioActual}-12-31",
                'activo' => true,
            ]);
        }

        if (!$anio->periodosAcademicos()->exists()) {
            $codigoBase = trim((string) $anio->nombre);
            $now = now();
            $anioCalendario = (int) preg_replace('/\D+/', '', (string) $anio->nombre);
            if ($anioCalendario < 2000) {
                $anioCalendario = (int) date('Y');
            }
            $finVacacional = Carbon::create($anioCalendario, 2, 1)->endOfMonth()->toDateString();

            $anio->periodosAcademicos()->createMany([
                [
                    'codigo' => $codigoBase . '-0',
                    'nombre' => 'Vacacional',
                    'fecha_inicio' => "{$anioCalendario}-01-01",
                    'fecha_fin' => $finVacacional,
                    'es_vacacional' => true,
                    'orden' => 0,
                    'activo' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'codigo' => $codigoBase . '-1',
                    'nombre' => 'Periodo 1',
                    'fecha_inicio' => "{$anioCalendario}-03-01",
                    'fecha_fin' => "{$anioCalendario}-06-30",
                    'es_vacacional' => false,
                    'orden' => 1,
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'codigo' => $codigoBase . '-2',
                    'nombre' => 'Periodo 2',
                    'fecha_inicio' => "{$anioCalendario}-08-01",
                    'fecha_fin' => "{$anioCalendario}-12-31",
                    'es_vacacional' => false,
                    'orden' => 2,
                    'activo' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        $periodos = $anio
            ? $anio->periodosAcademicos()->orderBy('orden')->get()
            : collect();

        $periodoActivo = $anio ? PeriodoAcademico::actualParaAnio($anio->id) : null;
        if (!$periodoActivo && $periodos->isNotEmpty()) {
            $periodoActivo = $periodos->first();
        }

        $seccionesPorNivel = $anio
            ? Seccion::with('grado')
                ->where('anio_escolar_id', $anio->id)
                ->get()
                ->sort(function ($a, $b) {
                    $gradoA = $a->grado?->nombre ?? '';
                    $gradoB = $b->grado?->nombre ?? '';
                    $cmpGrado = strnatcasecmp($gradoA, $gradoB);
                    return $cmpGrado !== 0 ? $cmpGrado : strnatcasecmp($a->nombre, $b->nombre);
                })
                ->groupBy(fn($s) => $s->grado?->nivel ?: 'Sin nivel')
            : collect();

        $admitidos = $this->admitidosParaMatricula();

        return view('secretaria.matriculas.matricula_paso1', compact('anio', 'periodos', 'periodoActivo', 'seccionesPorNivel', 'admitidos'));
    }

    /**
     * Admitidos en Admision que todavia no tienen matricula, en el formato que
     * usan los selectores "Cargar desde Admision" de los pasos 1 y 2.
     */
    private function admitidosParaMatricula()
    {
        return \App\Models\Postulante::with('grado')
            ->where('estado', 'admitido')
            ->whereNull('matricula_id')
            ->orderBy('apellidos')->orderBy('nombres')
            ->get()
            ->map(fn ($p) => [
                'id'     => $p->id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombreCompleto(),
                'nivel'  => $p->nivel,
                'grado'  => $p->grado?->nombre,
                'datos'  => $p->datosParaMatricula(),
            ])
            ->values();
    }

    public function guardarPaso1(Request $request)
    {
        $request->validate([
            'nivel' => 'required',
            'grado' => 'required',
            'seccion' => 'required',
            'periodo_academico_id' => 'required|exists:periodos_academicos,id',
            'postulante_id' => 'nullable|integer|exists:postulantes,id',
        ]);

        // Admitido elegido en "Cargar desde Admision": el paso 2 se llena con su expediente.
        if ($request->has('postulante_id')) {
            $postulante = $request->filled('postulante_id')
                ? \App\Models\Postulante::where('estado', 'admitido')->whereNull('matricula_id')->find($request->postulante_id)
                : null;

            if ($postulante) {
                // El nivel y el grado son los del expediente de admision, no se pueden cambiar aqui.
                $request->merge([
                    'nivel' => $postulante->nivel,
                    'grado' => $postulante->grado?->nombre ?? $request->grado,
                ]);
                session([
                    'matricula.postulante_id' => $postulante->id,
                    'matricula.paso2_form'    => $postulante->datosParaMatricula(),
                ]);
            } elseif ((int) session('matricula.postulante_id', 0) > 0) {
                // Se quito la seleccion: el asistente sigue como matricula normal.
                session()->forget(['matricula.postulante_id', 'matricula.paso2_form']);
            }
        }

        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();
        if (!$anio) {
            return back()->withErrors(['periodo_academico_id' => 'No hay anio escolar activo.']);
        }

        $periodo = PeriodoAcademico::where('id', $request->periodo_academico_id)
            ->where('anio_escolar_id', $anio->id)
            ->first();

        if (!$periodo) {
            return back()->withErrors(['periodo_academico_id' => 'El periodo seleccionado no pertenece al anio activo.']);
        }

        session([
            'matricula.nivel' => $request->nivel,
            'matricula.grado' => $request->grado,
            'matricula.seccion' => $request->seccion,
            'matricula.anio_escolar_id' => $anio->id,
            'matricula.periodo_academico_id' => $periodo->id,
        ]);

        return redirect()->route($this->routePrefix() . 'matriculas.paso2');
    }

    public function paso2()
    {
        $paso2 = session('matricula.paso2_form', []);
        if (!is_array($paso2)) {
            $paso2 = [];
        }

        $admitidos = $this->admitidosParaMatricula();

        return view('secretaria.matriculas.matricula_paso2', compact('paso2', 'admitidos'));
    }

    public function guardarSeccionModal(Request $request)
    {
        $request->validate([
            'nivel_academico' => 'required|string|max:50',
            'nombre_grado_seccion' => 'required|string|max:120',
            'vacantes' => 'required|integer|min:1|max:200',
        ]);

        $partes = array_values(array_filter(array_map('trim', explode('-', (string) $request->nombre_grado_seccion, 2))));

        if (count($partes) < 2) {
            return back()
                ->withInput()
                ->with('open_gestionar_secciones_modal', true)
                ->withErrors([
                    'nombre_grado_seccion' => 'Usa el formato: GRADO - SECCIÓN. Ejemplo: PRIMERO - A',
                ]);
        }

        [$gradoNombre, $seccionNombre] = $partes;

        $anio = AnioEscolar::where('activo', true)->first();
        if (!$anio) {
            $anio = AnioEscolar::create([
                'nombre' => (string) date('Y'),
                'fecha_inicio' => date('Y-01-01'),
                'fecha_fin' => date('Y-12-31'),
                'activo' => true,
            ]);
        }

        DB::transaction(function () use ($request, $anio, $gradoNombre, $seccionNombre) {
            $grado = Grado::firstOrCreate(
                ['nombre' => $gradoNombre, 'nivel' => $request->nivel_academico],
                ['orden' => 0]
            );

            $seccion = Seccion::firstOrNew([
                'anio_escolar_id' => $anio->id,
                'grado_id' => $grado->id,
                'nombre' => $seccionNombre,
            ]);

            if ($seccion->exists && $seccion->aula) {
                $seccion->aula->update([
                    'capacidad' => $request->vacantes,
                ]);
            } else {
                $aula = Aula::create([
                    'nombre' => sprintf('%s %s', $gradoNombre, $seccionNombre),
                    'capacidad' => $request->vacantes,
                ]);

                $seccion->aula_id = $aula->id;
            }

            $seccion->save();
        });

        $routeName = $request->routeIs('gestionar-secciones.*') || $request->routeIs('admin-sistema.gestionar-secciones.*')
            ? $this->seccionesIndexRoute()
            : $this->routePrefix() . 'matriculas.index';

        if (!Route::has($routeName)) {
            $routeName = $this->routePrefix() . 'matriculas.index';
        }

        $redirect = redirect()
            ->route($routeName)
            ->with('status', 'Grado/Sección guardado correctamente.');

        if (!$request->routeIs('director.*')) {
            $redirect->with('open_gestionar_secciones_modal', true);
        }

        return $redirect;
    }

    public function guardarCursosSeccionDirector(Request $request, Seccion $seccion)
    {
        $data = $request->validate([
            'assigning_seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'cursos' => ['nullable', 'array'],
            'cursos.*' => ['integer', 'exists:cursos,id'],
        ]);

        if ((int) $data['assigning_seccion_id'] !== (int) $seccion->id) {
            return redirect()
                ->route($this->seccionesIndexRoute())
                ->with('error', 'No se pudo identificar la seccion para asignar cursos.')
                ->withInput();
        }

        $periodo = PeriodoAcademico::where('id', (int) $data['periodo_academico_id'])
            ->where('anio_escolar_id', $seccion->anio_escolar_id)
            ->first();

        if (!$periodo) {
            return redirect()
                ->route($this->seccionesIndexRoute())
                ->withErrors([
                    'periodo_academico_id' => 'El periodo seleccionado no pertenece al anio escolar de la seccion.',
                ])
                ->withInput();
        }

        $cursoIds = collect($data['cursos'] ?? [])
            ->map(fn($cursoId) => (int) $cursoId)
            ->filter(fn(int $cursoId) => $cursoId > 0)
            ->unique()
            ->values();

        DB::transaction(function () use ($seccion, $periodo, $cursoIds) {
            $baseQuery = DB::table('seccion_curso')
                ->where('anio_escolar_id', $seccion->anio_escolar_id)
                ->where('periodo_academico_id', $periodo->id)
                ->where('seccion_id', $seccion->id);

            $cursosActuales = (clone $baseQuery)
                ->pluck('curso_id')
                ->map(fn($cursoId) => (int) $cursoId)
                ->all();

            $cursosSeleccionados = $cursoIds->all();
            $idsParaEliminar = array_values(array_diff($cursosActuales, $cursosSeleccionados));
            $idsParaInsertar = array_values(array_diff($cursosSeleccionados, $cursosActuales));

            if (!empty($idsParaEliminar)) {
                (clone $baseQuery)
                    ->whereIn('curso_id', $idsParaEliminar)
                    ->delete();
            }

            if (!empty($idsParaInsertar)) {
                $ahora = now();
                $registros = collect($idsParaInsertar)
                    ->map(fn(int $cursoId) => [
                        'anio_escolar_id' => $seccion->anio_escolar_id,
                        'periodo_academico_id' => $periodo->id,
                        'seccion_id' => $seccion->id,
                        'curso_id' => $cursoId,
                        'docente_id' => null,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ])
                    ->all();

                DB::table('seccion_curso')->insertOrIgnore($registros);
            }
        });

        return redirect()
            ->route($this->seccionesIndexRoute())
            ->with('status', "Cursos actualizados para {$periodo->nombre} en la seccion seleccionada.");
    }

    public function actualizarSeccionDirector(Request $request, Seccion $seccion)
    {
        $request->validate([
            'editing_seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'edit_nivel_academico' => ['required', 'string', 'max:50'],
            'edit_grado_nombre' => ['required', 'string', 'max:120'],
            'edit_seccion_nombre' => ['required', 'string', 'max:120'],
            'edit_vacantes' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        if ((int) $request->input('editing_seccion_id') !== (int) $seccion->id) {
            return redirect()
                ->route($this->seccionesIndexRoute())
                ->with('error', 'No se pudo identificar la seccion a editar.')
                ->withInput();
        }

        $nivelAcademico = trim((string) $request->input('edit_nivel_academico'));
        $gradoNombre = trim((string) $request->input('edit_grado_nombre'));
        $seccionNombre = trim((string) $request->input('edit_seccion_nombre'));
        $vacantes = $request->filled('edit_vacantes')
            ? (int) $request->input('edit_vacantes')
            : null;

        DB::transaction(function () use ($seccion, $nivelAcademico, $gradoNombre, $seccionNombre, $vacantes) {
            $grado = Grado::firstOrCreate(
                ['nombre' => $gradoNombre, 'nivel' => $nivelAcademico],
                ['orden' => 0]
            );

            $existeDuplicada = Seccion::where('anio_escolar_id', $seccion->anio_escolar_id)
                ->where('grado_id', $grado->id)
                ->where('nombre', $seccionNombre)
                ->where('id', '!=', $seccion->id)
                ->exists();

            if ($existeDuplicada) {
                throw ValidationException::withMessages([
                    'edit_seccion_nombre' => 'Ya existe una seccion con ese nombre para el grado y anio escolar seleccionados.',
                ]);
            }

            $seccion->grado_id = $grado->id;
            $seccion->nombre = $seccionNombre;
            $seccion->save();

            Matricula::where('seccion_id', $seccion->id)->update(['grado_id' => $grado->id]);

            if ($seccion->aula) {
                $seccion->aula->update([
                    'nombre' => sprintf('%s %s', $gradoNombre, $seccionNombre),
                    'capacidad' => $vacantes,
                ]);
            } elseif (!is_null($vacantes)) {
                $aula = Aula::create([
                    'nombre' => sprintf('%s %s', $gradoNombre, $seccionNombre),
                    'capacidad' => $vacantes,
                ]);

                $seccion->aula_id = $aula->id;
                $seccion->save();
            }
        });

        return redirect()
            ->route($this->seccionesIndexRoute())
            ->with('status', 'Seccion actualizada correctamente.');
    }

    public function eliminarSeccionDirector(Seccion $seccion)
    {
        $bloqueos = collect([
            'matriculas' => $seccion->matriculas()->count(),
            'asistencias' => $seccion->asistencias()->count(),
            'horarios' => $seccion->horarios()->count(),
            'conceptos de notas' => DB::table('nota_conceptos')->where('seccion_id', $seccion->id)->count(),
        ])->filter(fn ($total) => $total > 0);

        if ($bloqueos->isNotEmpty()) {
            $detalle = $bloqueos
                ->map(fn ($total, $nombre) => "{$nombre} ({$total})")
                ->values()
                ->implode(', ');

            return redirect()
                ->route($this->seccionesIndexRoute())
                ->with('error', "No se puede eliminar la seccion porque tiene registros asociados: {$detalle}.");
        }

        $aulaId = $seccion->aula_id;
        $seccion->delete();

        if ($aulaId && !Seccion::where('aula_id', $aulaId)->exists()) {
            Aula::where('id', $aulaId)->delete();
        }

        return redirect()
            ->route($this->seccionesIndexRoute())
            ->with('status', 'Seccion eliminada correctamente.');
    }

    public function guardarPaso2(Request $request)
    {
        $request->merge([
            'alumno_dni'     => preg_replace('/\D+/', '', (string) $request->input('alumno_dni')),
            'apoderado_dni'  => preg_replace('/\D+/', '', (string) $request->input('apoderado_dni')),
            'apoderado2_dni' => preg_replace('/\D+/', '', (string) $request->input('apoderado2_dni', '')),
        ]);

        $nivel = trim((string) session('matricula.nivel', ''));
        $gradoNombre = trim((string) session('matricula.grado', ''));
        $seccionNombre = trim((string) session('matricula.seccion', ''));
        $anioId = (int) session('matricula.anio_escolar_id', 0);
        $periodoId = (int) session('matricula.periodo_academico_id', 0);

        // Respaldo: recuperar los datos del paso 1 desde el formulario del paso 2
        // cuando la sesion del wizard fue limpiada entre requests.
        $nivelForm = trim((string) $request->input('wizard_nivel', ''));
        $gradoForm = trim((string) $request->input('wizard_grado', ''));
        $seccionForm = trim((string) $request->input('wizard_seccion', ''));
        $anioForm = (int) $request->input('wizard_anio_escolar_id', 0);
        $periodoForm = (int) $request->input('wizard_periodo_academico_id', 0);

        if ($nivel === '' && $nivelForm !== '') {
            $nivel = $nivelForm;
        }
        if ($gradoNombre === '' && $gradoForm !== '') {
            $gradoNombre = $gradoForm;
        }
        if ($seccionNombre === '' && $seccionForm !== '') {
            $seccionNombre = $seccionForm;
        }
        if ($anioId <= 0 && $anioForm > 0) {
            $anioId = $anioForm;
        }
        if ($periodoId <= 0 && $periodoForm > 0) {
            $periodoId = $periodoForm;
        }

        if ($nivel !== '' && $gradoNombre !== '' && $seccionNombre !== '' && $periodoId > 0) {
            session([
                'matricula.nivel' => $nivel,
                'matricula.grado' => $gradoNombre,
                'matricula.seccion' => $seccionNombre,
                'matricula.periodo_academico_id' => $periodoId,
                'matricula.anio_escolar_id' => $anioId > 0 ? $anioId : null,
            ]);
        }

        $estudianteIdActual = session('matricula.estudiante_id');

        // Verificar si el alumno ya tiene matrícula activa/registrada en el año+periodo actual
        $alumnoDniRule = function ($attribute, $value, $fail) use ($anioId, $periodoId, $estudianteIdActual) {
            $estudiante = \App\Models\Estudiante::where('dni', $value)->first();
            if (!$estudiante) {
                return; // DNI nuevo, ok
            }
            // Si es el mismo alumno del wizard en curso, permitir
            if ($estudianteIdActual && (int) $estudianteIdActual === (int) $estudiante->id) {
                return;
            }
            // Bloquear solo si ya tiene matrícula activa o registrada en el mismo año+periodo
            $yaMatriculado = \App\Models\Matricula::where('estudiante_id', $estudiante->id)
                ->when($anioId, fn($q) => $q->where('anio_escolar_id', $anioId))
                ->when($periodoId, fn($q) => $q->where('periodo_academico_id', $periodoId))
                ->whereIn('estado', [\App\Models\Matricula::ESTADO_REGISTRADA, \App\Models\Matricula::ESTADO_ACTIVA])
                ->exists();
            if ($yaMatriculado) {
                $fail('Este alumno ya tiene una matrícula activa en el año/periodo seleccionado.');
            }
        };

        $request->validate([
            'postulante_id' => 'nullable|integer|exists:postulantes,id',
            'situacion_alumno' => 'nullable|string|max:100',
            'institucion_procedencia' => 'nullable|string|max:255',
            'repitente' => 'nullable',
            'alumno_dni' => ['required', 'digits:8', $alumnoDniRule],
            'alumno_nombres' => 'required|string|max:255',
            'alumno_apellidos' => 'required|string|max:255',
            'alumno_nacimiento' => 'required|date',
            'alumno_sexo' => 'required|string|in:Masculino,Femenino',
            'alumno_ciudad' => 'nullable|string|max:120',
            'alumno_direccion' => 'nullable|string|max:255',
            'apoderado_relacion' => 'required|string|in:Madre,Padre,Tutor',
            'apoderado_dni' => 'required|digits:8',
            'apoderado_nombres' => 'required|string|max:255',
            'apoderado_apellidos' => 'required|string|max:255',
            'apoderado_nacimiento' => 'required|date',
            'apoderado_sexo' => 'required|string|in:Masculino,Femenino',
            'apoderado_estado_civil' => 'nullable|string|max:50',
            'apoderado_ciudad' => 'nullable|string|max:120',
            'apoderado_direccion' => 'nullable|string|max:255',
            'apoderado_telefono' => 'nullable|string|max:30',
            'apoderado_email' => 'nullable|email|max:255',
            // Segundo apoderado (opcional)
            'tiene_segundo_apoderado' => 'nullable|in:0,1',
            'apoderado2_relacion'     => 'required_if:tiene_segundo_apoderado,1|nullable|string|in:Madre,Padre,Tutor',
            'apoderado2_dni'          => 'required_if:tiene_segundo_apoderado,1|nullable|digits:8',
            'apoderado2_nombres'      => 'required_if:tiene_segundo_apoderado,1|nullable|string|max:255',
            'apoderado2_apellidos'    => 'required_if:tiene_segundo_apoderado,1|nullable|string|max:255',
            'apoderado2_nacimiento'   => 'required_if:tiene_segundo_apoderado,1|nullable|date',
            'apoderado2_sexo'         => 'required_if:tiene_segundo_apoderado,1|nullable|string|in:Masculino,Femenino',
            'apoderado2_estado_civil' => 'nullable|string|max:50',
            'apoderado2_ciudad'       => 'nullable|string|max:120',
            'apoderado2_direccion'    => 'nullable|string|max:255',
            'apoderado2_telefono'     => 'nullable|string|max:30',
            'apoderado2_email'        => 'nullable|email|max:255',
        ], [
            'alumno_dni.required' => 'El DNI del alumno es obligatorio.',
            'alumno_dni.digits' => 'El DNI del alumno debe tener exactamente 8 digitos numericos.',
            'alumno_nacimiento.required' => 'La fecha de nacimiento del alumno es obligatoria.',
            'alumno_sexo.required' => 'El sexo del alumno es obligatorio.',
            'alumno_sexo.in' => 'Selecciona un sexo valido para el alumno.',
            'apoderado_relacion.required' => 'El tipo de relacion es obligatorio.',
            'apoderado_relacion.in' => 'Selecciona un tipo de relacion valido.',
            'apoderado_dni.required' => 'El DNI del apoderado es obligatorio.',
            'apoderado_dni.digits' => 'El DNI del apoderado debe tener exactamente 8 digitos numericos.',
            'apoderado_nacimiento.required' => 'La fecha de nacimiento del apoderado es obligatoria.',
            'apoderado_sexo.required' => 'El sexo del apoderado es obligatorio.',
            'apoderado_sexo.in' => 'Selecciona un sexo valido para el apoderado.',
        ]);

        session([
            'matricula.paso2_form' => $request->only([
                'situacion_alumno',
                'institucion_procedencia',
                'repitente',
                'alumno_dni',
                'alumno_nombres',
                'alumno_apellidos',
                'alumno_nacimiento',
                'alumno_sexo',
                'alumno_ciudad',
                'alumno_direccion',
                'apoderado_relacion',
                'apoderado_dni',
                'apoderado_nombres',
                'apoderado_apellidos',
                'apoderado_nacimiento',
                'apoderado_sexo',
                'apoderado_estado_civil',
                'apoderado_ciudad',
                'apoderado_direccion',
                'apoderado_telefono',
                'apoderado_email',
                'tiene_segundo_apoderado',
                'apoderado2_relacion',
                'apoderado2_dni',
                'apoderado2_nombres',
                'apoderado2_apellidos',
                'apoderado2_nacimiento',
                'apoderado2_sexo',
                'apoderado2_estado_civil',
                'apoderado2_ciudad',
                'apoderado2_direccion',
                'apoderado2_telefono',
                'apoderado2_email',
            ]),
        ]);

        if ($nivel === '' || $gradoNombre === '' || $seccionNombre === '') {
            return redirect()
                ->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Primero selecciona nivel, grado y seccion.');
        }

        $anio = $anioId ? AnioEscolar::find($anioId) : AnioEscolar::where('activo', true)->first();
        if (!$anio) {
            $anio = AnioEscolar::create([
                'nombre' => (string) date('Y'),
                'fecha_inicio' => date('Y-01-01'),
                'fecha_fin' => date('Y-12-31'),
                'activo' => true,
            ]);
        }

        if ($periodoId <= 0) {
            return redirect()
                ->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Selecciona un periodo academico antes de continuar.');
        }

        $periodo = PeriodoAcademico::find($periodoId);
        if (!$periodo) {
            return redirect()
                ->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'El periodo academico seleccionado ya no existe. Vuelve a seleccionarlo.');
        }

        if ((int) ($anio->id ?? 0) !== (int) $periodo->anio_escolar_id) {
            $anio = AnioEscolar::find($periodo->anio_escolar_id) ?? $anio;
        }

        if ($anio) {
            session([
                'matricula.anio_escolar_id' => $anio->id,
                'matricula.periodo_academico_id' => $periodo->id,
            ]);
        }

        $grado = Grado::firstOrCreate(
            ['nombre' => $gradoNombre, 'nivel' => $nivel],
            ['orden' => 0]
        );

        $seccion = Seccion::firstOrCreate([
            'anio_escolar_id' => $anio->id,
            'grado_id' => $grado->id,
            'nombre' => $seccionNombre,
        ]);

        $repitente = in_array(mb_strtolower((string) $request->repitente), ['si', '1', 'true'], true);

        $matriculaId = null;
        $estudianteId = null;
        $apoderadoId = null;
        $credenciales = null;
        $estudianteCreadoEnWizard = false;
        $apoderadoCreadoEnWizard = false;

        DB::transaction(function () use (
            $request,
            $anio,
            $periodoId,
            $grado,
            $seccion,
            $repitente,
            &$matriculaId,
            &$estudianteId,
            &$apoderadoId,
            &$credenciales,
            &$estudianteCreadoEnWizard,
            &$apoderadoCreadoEnWizard
        ) {
            $estudiante = $request->filled('alumno_dni')
                ? Estudiante::firstOrNew(['dni' => $request->alumno_dni])
                : new Estudiante();
            $esNuevoEstudiante = !$estudiante->exists;
            $estudianteCreadoEnWizard = $esNuevoEstudiante;

            $estudiante->fill([
                'nombres' => $request->alumno_nombres,
                'apellidos' => $request->alumno_apellidos,
                'dni' => $request->alumno_dni,
                'fecha_nacimiento' => $request->alumno_nacimiento,
                'sexo' => $request->alumno_sexo,
                'ciudad' => $request->alumno_ciudad,
                'direccion' => $request->alumno_direccion,
            ]);
            if ($esNuevoEstudiante) {
                $estudiante->estado = 'en_proceso';
            }
            $estudiante->save();
            $this->asegurarCodigosEstudiante($estudiante);

            $dni = trim((string) $request->alumno_dni);
            if ($dni !== '') {
                $usuarioLogin = $this->usuarioLoginDesdeDni($dni);

                $user = $estudiante->user_id ? User::find($estudiante->user_id) : null;
                if (!$user) {
                    $user = $this->buscarUsuarioEstudiantePorDni($dni);
                }

                if (!$user) {
                    $passwordTemporal = $dni;
                    $user = User::create([
                        'name' => trim($estudiante->nombres . ' ' . $estudiante->apellidos),
                        'email' => $usuarioLogin,
                        'dni' => $usuarioLogin,
                        'password' => $passwordTemporal,
                    ]);
                    $user->assignRole('Estudiante');

                    $estudiante->password_temporal = Crypt::encryptString($passwordTemporal);
                    $estudiante->save();

                    $credenciales = [
                        'email' => $usuarioLogin,
                        'password' => $passwordTemporal,
                    ];
                } else {
                    $user->dni = $usuarioLogin;
                    $user->password = $dni;
                    if (trim((string) $user->email) === '') {
                        $user->email = $usuarioLogin;
                    }
                    $user->save();

                    if (!$user->hasRole('Estudiante')) {
                        $user->assignRole('Estudiante');
                    }
                }

                if (!$estudiante->user_id) {
                    $estudiante->user_id = $user->id;
                    $estudiante->save();
                }
            }

            $estudianteId = $estudiante->id;

            $apoderado = $request->filled('apoderado_dni')
                ? Apoderado::firstOrNew(['dni' => $request->apoderado_dni])
                : new Apoderado();
            $apoderadoCreadoEnWizard = !$apoderado->exists;

            $apoderado->fill([
                'nombres' => $request->apoderado_nombres,
                'apellidos' => $request->apoderado_apellidos,
                'dni' => $request->apoderado_dni,
                'fecha_nacimiento' => $request->apoderado_nacimiento,
                'sexo' => $request->apoderado_sexo,
                'estado_civil' => $request->apoderado_estado_civil,
                'ciudad' => $request->apoderado_ciudad,
                'direccion' => $request->apoderado_direccion,
                'telefono' => $request->apoderado_telefono,
                'email' => $request->apoderado_email,
            ]);
            $apoderado->save();
            $apoderadoId = $apoderado->id;

            $estudiante->apoderados()->syncWithoutDetaching([
                $apoderado->id => [
                    'relacion' => $request->apoderado_relacion,
                    'principal' => true,
                ],
            ]);

            $matriculaKeys = [
                'anio_escolar_id' => $anio->id,
                'periodo_academico_id' => $periodoId,
                'estudiante_id' => $estudiante->id,
            ];

            $matriculaExistente = Matricula::where($matriculaKeys)->first();
            $estadoMatricula = ($matriculaExistente?->estado === Matricula::ESTADO_REGISTRADA)
                ? Matricula::ESTADO_REGISTRADA
                : Matricula::ESTADO_EN_PROCESO;

            $fechaMatricula = $estadoMatricula === Matricula::ESTADO_REGISTRADA
                ? ($matriculaExistente?->fecha_matricula ?? date('Y-m-d'))
                : null;

            $matricula = Matricula::updateOrCreate(
                $matriculaKeys,
                [
                    'grado_id' => $grado->id,
                    'seccion_id' => $seccion->id,
                    'situacion_alumno' => $request->situacion_alumno,
                    'institucion_procedencia' => $request->institucion_procedencia,
                    'repitente' => $repitente,
                    'fecha_matricula' => $fechaMatricula,
                    'estado' => $estadoMatricula,
                    'registrado_por_id' => Auth::id(),
                ]
            );
            $matriculaId = $matricula->id;
        });

        session([
            'matricula.id' => $matriculaId,
            'matricula.estudiante_id' => $estudianteId,
            'matricula.apoderado_id' => $apoderadoId,
            'matricula.estudiante_creado_en_wizard' => $estudianteCreadoEnWizard,
            'matricula.apoderado_creado_en_wizard' => $apoderadoCreadoEnWizard,
        ]);

        // Si el asistente se abrio desde Admision, dejar enlazado el expediente
        // con la matricula para que el admitido no se matricule dos veces.
        $postulanteId = (int) ($request->input('postulante_id') ?: session('matricula.postulante_id', 0));
        if ($postulanteId > 0 && $matriculaId) {
            session(['matricula.postulante_id' => $postulanteId]);
            \App\Models\Postulante::whereKey($postulanteId)
                ->where('estado', 'admitido')
                ->update(['matricula_id' => $matriculaId]);
        }

        if ($credenciales) {
            session([
                'matricula.estudiante_credenciales' => $credenciales,
            ]);
        }

        return redirect()->route($this->routePrefix() . 'matriculas.paso3');
    }

    public function paso3()
    {
        $matriculaId = session('matricula.id');
        if (!$matriculaId) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Primero completa los pasos anteriores.');
        }

        $paso3 = session('matricula.paso3_form', []);
        if (
            !is_array($paso3)
            || (int) ($paso3['matricula_id'] ?? 0) !== (int) $matriculaId
        ) {
            $paso3 = [];
        }

        $matricula = Matricula::with(['grado', 'anioEscolar'])->findOrFail($matriculaId);

        $anioId = $matricula->anio_escolar_id;
        $nivel = $matricula->grado?->nivel; // Inicial/Primaria/Secundaria

        if (!$nivel) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'No se pudo determinar el nivel del alumno.');
        }

        $this->asegurarTarifasBasePorNivel($anioId, $nivel);

        // Detectar si el periodo seleccionado es vacacional
        $periodoModel  = $matricula->periodo_academico_id
            ? PeriodoAcademico::find($matricula->periodo_academico_id)
            : null;
        $esVacacional  = $periodoModel && $periodoModel->es_vacacional;

        if ($esVacacional) {
            // En periodo vacacional no se cobra matricula; solo pension mensual.
            $conceptoPension = $this->obtenerConceptoPension();
            if (!$conceptoPension) {
                return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                    ->with('error', 'No existe el concepto de pago "Pensión".');
            }

            $tarifaPensionVacacional = Tarifa::where('anio_escolar_id', $anioId)
                ->where('nivel', $nivel)
                ->where('concepto_pago_id', $conceptoPension->id)
                ->where('activo', 1)
                ->where('mes', 0)
                ->first();

            if (!$tarifaPensionVacacional) {
                return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                    ->with('error', "No hay tarifa de Pensión configurada para $nivel en el año escolar activo.");
            }

            return view('secretaria.matriculas.matricula_paso3', [
                'matricula'             => $matricula,
                'esVacacional'          => true,
                'conceptoVacacional'    => null,
                'tarifaVacacional'      => null,
                'tarifaMatricula'       => null,
                'tarifaPension'         => $tarifaPensionVacacional,
                'descuentosDisponibles' => collect(),
                'paso3'                 => $paso3,
            ]);
        }

        // Buscar concepto Matrícula y Pensión (ya existen por tu migración)
        $conceptoMatricula = $this->obtenerConceptoMatricula();
        $conceptoPension   = $this->obtenerConceptoPension();

        if (!$conceptoMatricula || !$conceptoPension) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Faltan conceptos de pago base (Matrícula / Pensión).');
        }

        // Tarifas
        $tarifaMatricula = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoMatricula->id)
            ->where('activo', 1)
            ->first();

        $tarifaPension = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where('activo', 1)
            ->where('mes', 0)
            ->first();

        if (!$tarifaMatricula || !$tarifaPension) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', "Faltan tarifas configuradas para $nivel (Matrícula/Pensión) en el año escolar activo.");
        }

        // Tarifas especiales por mes (Julio, Diciembre, etc.) — para mostrar desglose en JS
        $tarifasEspecialesMes = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where('activo', 1)
            ->where('mes', '>', 0)
            ->get()
            ->keyBy('mes')
            ->map(fn($t) => (float) $t->monto)
            ->toArray();

        // (Opcional) Traer descuentos activos configurados (para mostrarlo en UI si quieres)
        $descuentosDisponibles = Descuento::where('activo', 1)
            ->where(function ($q) use ($anioId) {
                $q->whereNull('anio_escolar_id')->orWhere('anio_escolar_id', $anioId);
            })
            ->where(function ($q) use ($nivel) {
                $q->whereNull('nivel')->orWhere('nivel', $nivel);
            })
            ->get();

        // Detección familiar — nueva lógica de costos
        $matricula->loadMissing('estudiante');
        $hermanosPaso3       = $this->detectarHermanos($matricula->estudiante_id, $anioId);
        $esPrimerHijoPaso3   = $hermanosPaso3->isEmpty();
        $esMenorPaso3        = $this->esHijoMenor($matricula->estudiante, $hermanosPaso3);

        // Monto hermano desde tarifario (matricula_hermano), fallback S/100
        $conceptoMatriculaHermano = ConceptoPago::where('tipo', 'matricula_hermano')->first();
        $tarifaMatriculaHermano = $conceptoMatriculaHermano
            ? Tarifa::where('anio_escolar_id', $anioId)
                ->where('nivel', $nivel)
                ->where('concepto_pago_id', $conceptoMatriculaHermano->id)
                ->where('activo', 1)
                ->first()
            : null;
        $montoHermanoPaso3 = $tarifaMatriculaHermano ? (float) $tarifaMatriculaHermano->monto : 100.00;

        $montoMatriculaFija  = $esPrimerHijoPaso3 ? (float) $tarifaMatricula->monto : $montoHermanoPaso3;
        $montoGastosInternos = 0.00;

        // Libro del nivel (auto-incluido en el total)
        // Busca primero por grado específico (ej: "Libros Inicial 2 años"), luego por nivel general
        $gradoNombreLibro = $matricula->grado?->nombre ?? '';
        $libroProducto = ($gradoNombreLibro
            ? InventarioProducto::where('nombre', 'Libros ' . $gradoNombreLibro)->first()
            : null)
            ?? InventarioProducto::where('nombre', 'Libros ' . $nivel)->first();
        $libroMonto          = $libroProducto ? (float) $libroProducto->precio : 0.0;
        $libroNombre         = $libroProducto ? $libroProducto->nombre : 'Libros ' . $nivel;
        $categoriaUniformeId = InventarioCategoria::where('slug', 'uniforme')->value('id');

        return view('secretaria.matriculas.matricula_paso3', [
            'matricula'             => $matricula,
            'esVacacional'          => false,
            'tarifaMatricula'       => $tarifaMatricula,
            'tarifaPension'         => $tarifaPension,
            'descuentosDisponibles' => $descuentosDisponibles,
            'paso3'                 => $paso3,
            'esPrimerHijo'          => $esPrimerHijoPaso3,
            'hermanos'              => $hermanosPaso3,
            'esMenorHijo'           => $esMenorPaso3,
            'montoMatriculaFija'    => $montoMatriculaFija,
            'montoGastosInternos'   => $montoGastosInternos,
            'tarifasEspecialesMes'  => $tarifasEspecialesMes,
            'libroMonto'            => $libroMonto,
            'libroNombre'           => $libroNombre,
            'categoriaUniformeId'   => $categoriaUniformeId,
        ]);
    }

    public function guardarPaso3(Request $request)
    {
        $request->validate([
            // rango de generación (override del periodo)
            'gen_modo' => 'nullable|string|in:todo,n_meses,rango,solo_matricula',
            'mes_gen_inicio' => 'nullable|integer|min:1|max:12',
            'mes_gen_fin'    => 'nullable|integer|min:1|max:12',

            // extras
            'libros_monto' => 'nullable|numeric|min:0',
            'uniformes_monto' => 'nullable|numeric|min:0',
            'utiles_monto' => 'nullable|numeric|min:0',
            'otros_monto' => 'nullable|numeric|min:0',
            'extras' => 'nullable|array',
            'extras.*.producto_id' => 'required|integer|exists:inventario_productos,id',
            'extras.*.cantidad' => 'required|integer|min:1',

            // descuento libre
            'descuento_monto'    => 'nullable|numeric|min:0',
            'descuento_concepto' => 'nullable|string|max:120',

            // pago inicial
            'monto_pagado' => 'nullable|numeric|min:0',
            'abono_matricula' => 'nullable|numeric|min:0',
            'abono_extras'    => 'nullable|numeric|min:0',
            'abono_libros'    => 'nullable|numeric|min:0',
            'abono_pensiones' => 'nullable|numeric|min:0',
            'medios'                   => 'nullable|array',
            'medios.*.metodo'          => 'nullable|string|max:50',
            'medios.*.monto'           => 'nullable|numeric|min:0',
            'medios.*.op'              => 'nullable|string|max:60',
            'fecha_pago' => 'nullable|date',
            'comentario' => 'nullable|string|max:255',
            'voucher' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        // Rango de generación: si viene del formulario, usarlo; si no, se usa el del periodo
        $genModoSolicitado = (string) $request->input('gen_modo', 'todo');
        $soloMatriculaConfirmado = $request->boolean('solo_matricula_confirm');
        $hayRangoPensionesExplcito = $request->filled('mes_gen_inicio') && $request->filled('mes_gen_fin');
        $genModo = self::normalizarModoPensiones(
            $genModoSolicitado,
            $soloMatriculaConfirmado,
            $hayRangoPensionesExplcito
        );
        $generarPensiones = $genModo !== 'solo_matricula';

        $mesGenInicio = $request->filled('mes_gen_inicio') ? (int) $request->mes_gen_inicio : null;
        $mesGenFin    = $request->filled('mes_gen_fin')    ? (int) $request->mes_gen_fin    : null;
        if ($genModo === 'todo') {
            // En "todo" ignora overrides del cliente para usar el rango completo del periodo.
            $mesGenInicio = null;
            $mesGenFin = null;
        }
        if (!$generarPensiones) {
            $mesGenInicio = null;
            $mesGenFin = null;
        }
        // Validar coherencia
        if ($mesGenInicio !== null && $mesGenFin !== null && $mesGenFin < $mesGenInicio) {
            $mesGenFin = $mesGenInicio;
        }

        $matriculaId = session('matricula.id');
        if (!$matriculaId) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Primero completa los pasos anteriores.');
        }

        $matricula = Matricula::with(['grado', 'anioEscolar'])->findOrFail($matriculaId);

        $anioId    = $matricula->anio_escolar_id;
        $periodoId = $matricula->periodo_academico_id;
        $nivel     = $matricula->grado?->nivel;

        if (!$nivel) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'No se pudo determinar el nivel del alumno.');
        }

        $this->asegurarTarifasBasePorNivel($anioId, $nivel);

        Log::info('matricula.paso3.modo_resuelto', [
            'matricula_id' => $matriculaId,
            'usuario_id' => Auth::id(),
            'gen_modo_solicitado' => $genModoSolicitado,
            'gen_modo_resuelto' => $genModo,
            'solo_matricula_confirm' => $soloMatriculaConfirmado,
            'hay_rango_explicitado' => $hayRangoPensionesExplcito,
            'mes_gen_inicio' => $mesGenInicio,
            'mes_gen_fin' => $mesGenFin,
        ]);

        // ── Rama Vacacional ──────────────────────────────────────────────
        $periodoModel = $periodoId ? PeriodoAcademico::find($periodoId) : null;
        if ($periodoModel && $periodoModel->es_vacacional) {
            return $this->guardarPaso3Vacacional($request, $matricula, $periodoModel);
        }
        // ─────────────────────────────────────────────────────────────────

        // Conceptos base
        $conceptoMatricula = $this->obtenerConceptoMatricula();
        $conceptoPension   = $this->obtenerConceptoPension();
        if (!$conceptoMatricula || !$conceptoPension) {
            throw ValidationException::withMessages([
                'libros_monto' => 'Faltan conceptos base de pago (Matrícula/Pensión).',
            ]);
        }

        // Tarifas oficiales (Tarifario)
        $tarifaMatricula = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoMatricula->id)
            ->where('activo', 1)
            ->first();

        $tarifaPension = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where('activo', 1)
            ->first();

        if (!$tarifaMatricula || !$tarifaPension) {
            return back()->withInput()->withErrors([
                'libros_monto' => "Faltan tarifas configuradas para $nivel (Matrícula/Pensión).",
            ]);
        }

        $matriculaMonto = (float) $tarifaMatricula->monto; // único
        $pensionMonto   = (float) $tarifaPension->monto;   // mensual base (tarifario)

        // ── Nueva lógica de costos de matrícula ──────────────────────────────
        $conceptoGastos = ConceptoPago::firstOrCreate(
            ['tipo' => 'gastos_internos'],
            ['nombre' => 'Matrícula (Gastos Internos)', 'monto_base' => 100.00, 'activo' => true]
        );
        $matricula->loadMissing('estudiante');
        $hermanos        = $this->detectarHermanos($matricula->estudiante_id, $anioId);
        $esPrimerHijo    = $hermanos->isEmpty();
        $esMenor         = $this->esHijoMenor($matricula->estudiante, $hermanos);

        // Monto hermano desde tarifario (matricula_hermano), fallback S/100
        $conceptoMatriculaHermano = ConceptoPago::where('tipo', 'matricula_hermano')->first();
        $tarifaMatriculaHermano = $conceptoMatriculaHermano
            ? Tarifa::where('anio_escolar_id', $anioId)
                ->where('nivel', $nivel)
                ->where('concepto_pago_id', $conceptoMatriculaHermano->id)
                ->where('activo', 1)
                ->first()
            : null;
        $montoGastos        = $tarifaMatriculaHermano ? (float) $tarifaMatriculaHermano->monto : 100.00;
        $descuentoGastos    = 0.00;
        $hermanoMenorPrevio = null;
        // Primer hijo: monto del tarifario completo. Hermanos: solo gastos internos del tarifario
        $montoGastosCombinado = $esPrimerHijo ? (float) $tarifaMatricula->monto : $montoGastos;

        // ===== Extras =====
        $librosMonto = (float) ($request->libros_monto ?? 0);
        $uniformesMonto = (float) ($request->uniformes_monto ?? 0);
        $utilesMonto = (float) ($request->utiles_monto ?? 0);
        $otrosMonto = (float) ($request->otros_monto ?? 0);

        $cargos = [];
        if ($librosMonto > 0) {
            $cargos[] = ['concepto' => 'Libros', 'cantidad' => 1, 'precio_unitario' => $librosMonto, 'subtotal' => $librosMonto];
        }
        if ($uniformesMonto > 0) {
            $cargos[] = ['concepto' => 'Uniformes', 'cantidad' => 1, 'precio_unitario' => $uniformesMonto, 'subtotal' => $uniformesMonto];
        }
        if ($utilesMonto > 0) {
            $cargos[] = ['concepto' => 'Útiles escolares', 'cantidad' => 1, 'precio_unitario' => $utilesMonto, 'subtotal' => $utilesMonto];
        }
        if ($otrosMonto > 0) {
            $cargos[] = ['concepto' => 'Otros', 'cantidad' => 1, 'precio_unitario' => $otrosMonto, 'subtotal' => $otrosMonto];
        }

        // Extras / productos seleccionados en el carrito del Paso 3.
        $carritoProductos = collect($this->buildPaso3CarritoState($request))
            ->map(function (array $item): array {
                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'nombre' => trim((string) ($item['nombre'] ?? '')),
                    'precio' => (float) ($item['precio'] ?? 0),
                    'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
                ];
            })
            ->filter(fn (array $item) => $item['id'] > 0 && $item['nombre'] !== '')
            ->groupBy('id')
            ->map(function ($group) {
                $primero = $group->first();

                return [
                    'id' => (int) $primero['id'],
                    'nombre' => $primero['nombre'],
                    'precio' => (float) $primero['precio'],
                    'cantidad' => (int) $group->sum('cantidad'),
                ];
            })
            ->values();

        foreach ($carritoProductos as $productoExtra) {
            $subtotal = round($productoExtra['precio'] * $productoExtra['cantidad'], 2);
            if ($subtotal <= 0) {
                continue;
            }

            $cargos[] = [
                'concepto' => 'Producto: ' . $productoExtra['nombre'],
                'cantidad' => $productoExtra['cantidad'],
                'precio_unitario' => $productoExtra['precio'],
                'subtotal' => $subtotal,
                'inventario_producto_id' => $productoExtra['id'],
            ];
        }

        $extrasTotal = collect($cargos)->sum('subtotal');

        // ===== Descuento libre (monto fijo por pensión mensual) =====
        $descuentos = [];

        $descuentoMonto    = round(max(0, (float) $request->input('descuento_monto', 0)), 2);
        $descuentoConcepto = trim((string) $request->input('descuento_concepto', ''));

        if ($descuentoMonto > 0 && $descuentoConcepto !== '') {
            $descuentos[] = [
                'tipo'        => 'personalizado',
                'porcentaje'  => 0,
                'monto'       => $descuentoMonto,
                'descripcion' => $descuentoConcepto,
            ];
        }

        // Total descuentos del “primer mes” (solo para validar el total estimado mostrado)
        $totalDescuentos = collect($descuentos)->sum('monto');

        $totalMatricula = $montoGastosCombinado;
        $totalPensionConDesc = max($pensionMonto - $totalDescuentos, 0);
        $totalGeneral = $totalMatricula + $totalPensionConDesc + $extrasTotal;

        // ===== Pago inicial =====
        $conceptoLibros = $this->obtenerConceptoLibros();
        $abonoLibrosBruto = (float) ($request->input('abono_libros', 0));
        // Si no hay concepto separado para libros, acumular en matrícula para no perder el abono
        $abonoMatricula = (float) ($request->input('abono_matricula', 0))
                        + (float) ($request->input('abono_extras', 0))
                        + ($conceptoLibros ? 0 : $abonoLibrosBruto);
        $abonoLibros    = $conceptoLibros ? $abonoLibrosBruto : 0;
        $abonoPensiones = (float) ($request->input('abono_pensiones', 0));
        $usarAbonoPorConcepto = $request->filled('abono_matricula') || $request->filled('abono_extras') || $request->filled('abono_libros') || $request->filled('abono_pensiones');

        $montoPagado = $usarAbonoPorConcepto
            ? round($abonoMatricula + $abonoLibros + $abonoPensiones, 2)
            : (float) ($request->monto_pagado ?? 0);
        $registrarPago = $montoPagado > 0;

        // Parsear medios de pago (multi-método)
        $mediosRaw = collect($request->input('medios', []))
            ->filter(fn ($m) => !empty($m['metodo']) && isset($m['monto']) && (float) $m['monto'] > 0)
            ->map(fn ($m) => [
                'metodo' => (string) $m['metodo'],
                'monto'  => round((float) $m['monto'], 2),
                'op'     => isset($m['op']) ? trim((string) $m['op']) : null,
            ])
            ->values();

        if ($registrarPago && $mediosRaw->isEmpty()) {
            return back()->withInput()->withErrors([
                'medios' => 'Indica al menos un medio de pago para registrar el abono.',
            ]);
        }

        if (!$registrarPago && $request->hasFile('voucher')) {
            return back()->withInput()->withErrors([
                'voucher' => 'No puedes subir voucher si no hay un pago registrado.',
            ]);
        }

        // Método para almacenar: JSON si múltiples, string si uno solo (legacy-compatible)
        $metodoJson       = $mediosRaw->count() > 1
            ? $mediosRaw->toJson()
            : ($mediosRaw->first()['metodo'] ?? null);
        $numeroOperacion  = $mediosRaw->count() === 1
            ? ($mediosRaw->first()['op'] ?: null)
            : null;

        $estadoPago = $registrarPago ? 'confirmado' : null;

        $deudaMatriculaId = null;
        $pagoId = null;

        DB::transaction(function () use (
            $request,
            $matricula,
            $anioId,
            $periodoId,
            $conceptoMatricula,
            $conceptoPension,
            $conceptoGastos,
            $esPrimerHijo,
            $montoGastosCombinado,
            $descuentoGastos,
            $esMenor,
            $hermanoMenorPrevio,
            $hermanos,
            $totalMatricula,
            $estadoPago,
            $registrarPago,
            $montoPagado,
            $metodoJson,
            $numeroOperacion,
            $abonoMatricula,
            $abonoLibros,
            $abonoPensiones,
            $usarAbonoPorConcepto,
            $conceptoLibros,
            $cargos,
            $descuentos,
            $mesGenInicio,
            $mesGenFin,
            $generarPensiones,
            &$deudaMatriculaId,
            &$pagoId
        ) {
            $matricula->loadMissing(['grado', 'anioEscolar']);

            // ✅ Evitar duplicados si reenvían Paso 3
            Deuda::where('matricula_id', $matricula->id)
                ->where('concepto_pago_id', $conceptoMatricula->id)
                ->delete();

            // ✅ Si cambian descuentos, regeneramos pensiones pendientes/parciales
            Deuda::where('matricula_id', $matricula->id)
                ->where('concepto_pago_id', $conceptoPension->id)
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->delete();

            MatriculaCargo::where('matricula_id', $matricula->id)->delete();
            MatriculaDescuento::where('matricula_id', $matricula->id)->delete();

            // 1) Deuda única: Matrícula (Gastos Internos)
            //    Primer hijo: S/.260 (S/.160 matrícula familiar + S/.100 gastos)
            //    Hijos siguientes: S/.100 (o S/.95 si es el menor)
            if ($conceptoGastos) {
                Deuda::where('matricula_id', $matricula->id)
                    ->where('concepto_pago_id', $conceptoGastos->id)
                    ->delete();

                $deudaGastos = Deuda::create([
                    'anio_escolar_id'      => $anioId,
                    'periodo_academico_id' => $periodoId,
                    'matricula_id'         => $matricula->id,
                    'concepto_pago_id'     => $conceptoGastos->id,
                    'periodo'              => 'Matrícula (Gastos Internos)',
                    'monto'                => $montoGastosCombinado,
                    'saldo'                => $montoGastosCombinado,
                    'fecha_vencimiento'    => null,
                    'estado'               => 'pendiente',
                ]);

                if (!$deudaMatriculaId) {
                    $deudaMatriculaId = $deudaGastos->id;
                }

                // Registrar descuento del hijo menor para trazabilidad
                if ($esMenor && $descuentoGastos > 0) {
                    MatriculaDescuento::firstOrCreate(
                        ['matricula_id' => $matricula->id, 'tipo' => 'hermano_menor'],
                        [
                            'porcentaje'  => 5,
                            'monto'       => $descuentoGastos,
                            'descripcion' => 'Descuento hijo menor (5% sobre Gastos Internos)',
                        ]
                    );
                }

                // Retroactivo: si este hijo es el nuevo menor, el hermano previo pierde su descuento
                if ($hermanoMenorPrevio) {
                    Deuda::where('matricula_id', $hermanoMenorPrevio['matricula']->id)
                        ->where('concepto_pago_id', $conceptoGastos->id)
                        ->where('estado', 'pendiente')
                        ->update(['monto' => 100.00, 'saldo' => 100.00]);

                    MatriculaDescuento::where('matricula_id', $hermanoMenorPrevio['matricula']->id)
                        ->where('tipo', 'hermano_menor')
                        ->delete();
                }
            }

            // Actualizar observacion (H+/H-/H) de esta matrícula y sus hermanos
            $this->actualizarObservacionesHermanos($matricula, $hermanos, $esPrimerHijo, $esMenor);

            // 2) Pago (si aplica)
            $pago = null;
            if ($registrarPago) {
                $pago = Pago::create([
                    'anio_escolar_id'    => $anioId,
                    'periodo_academico_id' => $periodoId,
                    'monto'              => $montoPagado,
                    'fecha_pago'         => $request->fecha_pago,
                    'metodo'             => $metodoJson,
                    'numero_operacion'   => $numeroOperacion,
                    'estado'             => $estadoPago,
                    'usuario_registra_id' => Auth::id(),
                    'comentario'         => $request->comentario,
                ]);
                $pagoId = $pago->id;

                if ($request->hasFile('voucher')) {
                    $path = $request->file('voucher')->store('vouchers', 'public');

                    Voucher::create([
                        'deuda_id' => $deudaMatriculaId,
                        'pago_id' => $pagoId,
                        'archivo_path' => $path,
                        'estado' => 'pendiente',
                        'comentario_validacion' => null,
                        'usuario_valida_id' => null,
                    ]);
                }
            }

            // 3) Guardar extras
            foreach ($cargos as $cargo) {
                MatriculaCargo::create([
                    'matricula_id' => $matricula->id,
                    'concepto' => $cargo['concepto'],
                    'cantidad' => $cargo['cantidad'],
                    'precio_unitario' => $cargo['precio_unitario'],
                    'subtotal' => $cargo['subtotal'],
                ]);

                $montoCargo = round((float) ($cargo['subtotal'] ?? 0), 2);
                if ($montoCargo <= 0) {
                    continue;
                }

                $conceptoCargoId = ($cargo['concepto'] === 'Libros' && $conceptoLibros)
                    ? $conceptoLibros->id
                    : $conceptoMatricula->id;

                Deuda::create([
                    'anio_escolar_id' => $anioId,
                    'periodo_academico_id' => $periodoId,
                    'matricula_id' => $matricula->id,
                    'concepto_pago_id' => $conceptoCargoId,
                    'periodo' => (string) ($cargo['concepto'] ?? 'Cargo adicional'),
                    'monto' => $montoCargo,
                    'saldo' => $montoCargo,
                    'fecha_vencimiento' => null,
                    'estado' => 'pendiente',
                ]);
            }

            // 4) Guardar descuentos (snapshot)
            foreach ($descuentos as $descuento) {
                MatriculaDescuento::create([
                    'matricula_id' => $matricula->id,
                    'tipo' => $descuento['tipo'],
                    'porcentaje' => $descuento['porcentaje'],
                    'monto' => $descuento['monto'],
                    'descripcion' => $descuento['descripcion'],
                ]);
            }

            // 5) Generar pensiones mensuales (rango dinámico) ya con descuento aplicado
            if ($generarPensiones) {
                $this->generarDeudasPensiones($matricula, $mesGenInicio, $mesGenFin);
            }

            // 6) Aplicar el pago a todas las deudas generadas (matrícula, extras y pensiones)
            if ($registrarPago && $pago) {
                $totalAplicado = 0.00;

                if ($usarAbonoPorConcepto) {
                    // Incluye matrícula + cargos extras (misma concepto_pago_id, distintos periodos)
                    $deudasMatricula = Deuda::where('matricula_id', $matricula->id)
                        ->whereIn('estado', ['pendiente', 'parcial'])
                        ->where('saldo', '>', 0)
                        ->whereIn('concepto_pago_id', array_filter([$conceptoMatricula->id, $conceptoGastos?->id]))
                        ->orderByRaw("CASE WHEN periodo = 'Matrícula' THEN 0 ELSE 1 END")
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $deudasPensiones = Deuda::where('matricula_id', $matricula->id)
                        ->whereIn('estado', ['pendiente', 'parcial'])
                        ->where('saldo', '>', 0)
                        ->where('concepto_pago_id', $conceptoPension->id)
                        ->orderBy('fecha_vencimiento')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $totalAplicado += $this->aplicarPagoEnColeccionDeudas(
                        $pago,
                        $deudasMatricula,
                        (float) $abonoMatricula
                    );

                    if ($abonoLibros > 0 && $conceptoLibros) {
                        $deudasLibros = Deuda::where('matricula_id', $matricula->id)
                            ->whereIn('estado', ['pendiente', 'parcial'])
                            ->where('saldo', '>', 0)
                            ->where('concepto_pago_id', $conceptoLibros->id)
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get();
                        $totalAplicado += $this->aplicarPagoEnColeccionDeudas(
                            $pago,
                            $deudasLibros,
                            (float) $abonoLibros
                        );
                    }

                    $totalAplicado += $this->aplicarPagoEnColeccionDeudas(
                        $pago,
                        $deudasPensiones,
                        (float) $abonoPensiones
                    );
                } else {
                    $deudasPendientes = Deuda::where('matricula_id', $matricula->id)
                        ->whereIn('estado', ['pendiente', 'parcial'])
                        ->where('saldo', '>', 0)
                        ->orderByRaw('CASE WHEN fecha_vencimiento IS NULL THEN 0 ELSE 1 END')
                        ->orderBy('fecha_vencimiento')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    $totalAplicado += $this->aplicarPagoEnColeccionDeudas(
                        $pago,
                        $deudasPendientes,
                        (float) $montoPagado
                    );
                }

                $pago->monto = round($totalAplicado, 2);
                $pago->save();
            }
        });

        session([
            'matricula.deuda_matricula_id' => $deudaMatriculaId,
            'matricula.pago_id' => $pagoId,
            'matricula.paso3_form' => $this->buildPaso3FormState(
                $request,
                $genModo,
                $mesGenInicio,
                $mesGenFin
            ),
        ]);

        return redirect()->route($this->routePrefix() . 'matriculas.paso4');
    }

    public function paso4()
    {
        $matriculaId = session('matricula.id');
        if (!$matriculaId) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Primero completa los pasos anteriores.');
        }

        $matricula = Matricula::with(['grado', 'seccion', 'anioEscolar'])
            ->findOrFail($matriculaId);

        $cursos = Curso::orderBy('nombre')->get();
        $cursosAsignados = collect();
        $asignacionBloqueada = false;

        if ($matricula->seccion) {
            $cursosAsignados = $matricula->seccion->cursos()
                ->wherePivot('anio_escolar_id', $matricula->anio_escolar_id)
                ->wherePivot('periodo_academico_id', $matricula->periodo_academico_id)
                ->orderBy('cursos.nombre')
                ->get();
            $asignacionBloqueada = $cursosAsignados->isNotEmpty();
        }

        if ($cursos->isEmpty()) {
            session([
                'matricula.return_to' => route($this->routePrefix() . 'matriculas.paso4'),
                'matricula.return_label' => 'Volver a matrícula (Paso 4)',
            ]);

            /** @var \App\Models\User|null $usuarioAutenticado */
            $usuarioAutenticado = Auth::user();

            if (
                $usuarioAutenticado instanceof \App\Models\User
                && $usuarioAutenticado->hasRole('SuperAdmin')
                && \Illuminate\Support\Facades\Route::has('admin-sistema.cursos.index')
            ) {
                return redirect()
                    ->route('admin-sistema.cursos.index')
                    ->with('status', 'No hay cursos registrados. Agrega cursos y vuelve a la matrícula.');
            }
        }

        return view('secretaria.matriculas.matricula_paso4_cursos', compact(
            'matricula',
            'cursos',
            'cursosAsignados',
            'asignacionBloqueada'
        ));
    }

    public function guardarPaso4(Request $request)
    {
        $matriculaId = session('matricula.id');
        if (!$matriculaId) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Primero completa los pasos anteriores.');
        }

        $matricula = Matricula::with(['seccion', 'periodoAcademico'])->findOrFail($matriculaId);
        $seccion = $matricula->seccion;

        if (!$seccion) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'No se encontro la seccion de la matricula.');
        }

        $yaAsignados = $seccion->cursos()
            ->wherePivot('anio_escolar_id', $matricula->anio_escolar_id)
            ->wherePivot('periodo_academico_id', $matricula->periodo_academico_id)
            ->exists();

        if ($yaAsignados) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso5');
        }

        $cursoIds = collect($request->input('cursos', []));

        if ($cursoIds->isNotEmpty()) {
            $syncData = [];
            foreach ($cursoIds as $cursoId) {
                $syncData[$cursoId] = [
                    'anio_escolar_id'      => $matricula->anio_escolar_id,
                    'periodo_academico_id' => $matricula->periodo_academico_id,
                ];
            }
            $seccion->cursos()->syncWithoutDetaching($syncData);
        }

        return redirect()->route($this->routePrefix() . 'matriculas.paso5');
    }

    public function paso5()
    {
        $matriculaId = session('matricula.id');
        if (!$matriculaId) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Primero completa los pasos anteriores.');
        }

        $matricula = Matricula::with([
            'estudiante',
            'grado',
            'seccion.cursos',
            'anioEscolar',
            'periodoAcademico',
            'cargos',
            'descuentos',
            'deudas',
        ])->findOrFail($matriculaId);

        $apoderadoId = session('matricula.apoderado_id');
        $apoderado = $apoderadoId ? Apoderado::find($apoderadoId) : null;

        $pagoId = session('matricula.pago_id');
        $pago = $pagoId ? Pago::with('deudas')->find($pagoId) : null;

        $cursosAsignados = collect();
        if ($matricula->seccion) {
            $cursosAsignados = $matricula->seccion->cursos()
                ->wherePivot('anio_escolar_id', $matricula->anio_escolar_id)
                ->wherePivot('periodo_academico_id', $matricula->periodo_academico_id)
                ->orderBy('cursos.nombre')
                ->get();
        }

        $credenciales = session()->pull('matricula.estudiante_credenciales');

        return view('secretaria.matriculas.matricula_paso4', compact(
            'matricula',
            'apoderado',
            'pago',
            'cursosAsignados',
            'credenciales'
        ));
    }

    public function finalizarPaso5(Request $request)
    {
        $matriculaId = session('matricula.id');
        if (!$matriculaId) {
            return redirect()->route($this->routePrefix() . 'matriculas.paso1')
                ->with('error', 'Primero completa los pasos anteriores.');
        }

        $paso3Config = session('matricula.paso3_form', []);
        if (
            !is_array($paso3Config)
            || (int) ($paso3Config['matricula_id'] ?? 0) !== (int) $matriculaId
        ) {
            $paso3Config = [];
        }

        $paso2Config = session('matricula.paso2_form', []);

        try {
            DB::transaction(function () use ($matriculaId, $paso3Config, $paso2Config) {
                /** @var Matricula $matricula */
                $matricula = Matricula::with('estudiante')
                    ->lockForUpdate()
                    ->findOrFail($matriculaId);

                $this->asegurarGeneracionDeudasPaso3($matricula, $paso3Config);

                // ── Guardar segundo apoderado al finalizar (no antes) ─────
                if (
                    ($paso2Config['tiene_segundo_apoderado'] ?? '0') === '1'
                    && !empty($paso2Config['apoderado2_dni'])
                    && $matricula->estudiante_id
                ) {
                    $apoderado2 = Apoderado::firstOrNew(['dni' => $paso2Config['apoderado2_dni']]);
                    $apoderado2->fill([
                        'nombres'          => $paso2Config['apoderado2_nombres'] ?? null,
                        'apellidos'        => $paso2Config['apoderado2_apellidos'] ?? null,
                        'dni'              => $paso2Config['apoderado2_dni'],
                        'fecha_nacimiento' => $paso2Config['apoderado2_nacimiento'] ?? null,
                        'sexo'             => $paso2Config['apoderado2_sexo'] ?? null,
                        'estado_civil'     => $paso2Config['apoderado2_estado_civil'] ?? null,
                        'ciudad'           => $paso2Config['apoderado2_ciudad'] ?? null,
                        'direccion'        => $paso2Config['apoderado2_direccion'] ?? null,
                        'telefono'         => $paso2Config['apoderado2_telefono'] ?? null,
                        'email'            => $paso2Config['apoderado2_email'] ?? null,
                    ]);
                    $apoderado2->save();

                    $estudiante2 = \App\Models\Estudiante::find($matricula->estudiante_id);
                    $estudiante2?->apoderados()->syncWithoutDetaching([
                        $apoderado2->id => [
                            'relacion' => $paso2Config['apoderado2_relacion'] ?? null,
                            'principal' => false,
                        ],
                    ]);
                }

                if ($matricula->estado !== Matricula::ESTADO_REGISTRADA) {
                    // Descontar stock de productos extras solo al finalizar matricula.
                    $this->descontarStockProductosExtras($matricula->id);

                    $matricula->estado = Matricula::ESTADO_REGISTRADA;
                    $matricula->fecha_matricula = $matricula->fecha_matricula ?? date('Y-m-d');
                    $matricula->registrado_por_id = $matricula->registrado_por_id ?? Auth::id();
                    $matricula->save();
                }

                if ($matricula->estudiante) {
                    $this->asegurarCodigosEstudiante($matricula->estudiante);

                    if ($matricula->estudiante->estado === 'en_proceso') {
                        $matricula->estudiante->estado = 'activo';
                        $matricula->estudiante->save();
                    }
                }

                $resumenDeudas = Deuda::where('matricula_id', $matricula->id)
                    ->selectRaw('concepto_pago_id, COUNT(*) as total, SUM(saldo) as saldo_total')
                    ->groupBy('concepto_pago_id')
                    ->pluck('total', 'concepto_pago_id')
                    ->toArray();

                Log::info('matricula.paso5.finalizada', [
                    'matricula_id' => $matricula->id,
                    'usuario_id' => Auth::id(),
                    'paso3_config' => $paso3Config,
                    'resumen_deudas_por_concepto' => $resumenDeudas,
                ]);
            });
        } catch (ValidationException $exception) {
            $primerError = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo finalizar la matricula por un problema de inventario.';

            return redirect()
                ->route($this->routePrefix() . 'matriculas.paso5')
                ->withErrors($exception->errors())
                ->with('error', $primerError)
                ->with('matricula_finalize_error', $primerError);
        } catch (Throwable $exception) {
            Log::error('matricula.paso5.finalizar.error', [
                'matricula_id' => $matriculaId,
                'usuario_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);

            $mensajeError = 'No se pudo realizar la matricula. Intenta nuevamente.';

            return redirect()
                ->route($this->routePrefix() . 'matriculas.paso5')
                ->with('error', $mensajeError)
                ->with('matricula_finalize_error', $mensajeError);
        }

        // Capturar antes de limpiar sesión
        $pagoId = session('matricula.pago_id');

        session()->forget([
            'matricula.id',
            'matricula.nivel',
            'matricula.grado',
            'matricula.seccion',
            'matricula.anio_escolar_id',
            'matricula.periodo_academico_id',
            'matricula.estudiante_id',
            'matricula.apoderado_id',
            'matricula.deuda_matricula_id',
            'matricula.pago_id',
            'matricula.estudiante_credenciales',
            'matricula.return_to',
            'matricula.return_label',
            'matricula.estudiante_creado_en_wizard',
            'matricula.apoderado_creado_en_wizard',
            'matricula.paso2_form',
            'matricula.paso3_form',
            'matricula.postulante_id',
        ]);

        // Si se registró un pago → mostrar el voucher tipo ticket
        if ($pagoId) {
            return redirect()->route($this->routePrefix() . 'pagos.voucher', $pagoId)
                ->with('voucher_after_print_url', route($this->routePrefix() . 'matriculas.index'));
        }

        // Sin pago → redirigir al índice con mensaje
        return redirect()->route($this->routePrefix() . 'matriculas.index')
            ->with('status', 'Matrícula finalizada correctamente.');
    }

    private function usuarioLoginDesdeDni(string $dni): string
    {
        return trim($dni);
    }

    private function buscarUsuarioEstudiantePorDni(string $dni): ?User
    {
        $usuarioLogin = $this->usuarioLoginDesdeDni($dni);
        $correoInstitucional = $usuarioLogin . '@reyschool.edu.pe';
        // Usuarios creados antes del cambio de marca conservan el dominio anterior
        $correoInstitucionalLegacy = $usuarioLogin . '@jeanpiaget.edu.pe';

        return User::where('dni', $usuarioLogin)->first()
            ?? User::where('email', $usuarioLogin)->first()
            ?? User::where('email', $correoInstitucional)->first()
            ?? User::where('email', $correoInstitucionalLegacy)->first();
    }

    private function asegurarGeneracionDeudasPaso3(Matricula $matricula, array $paso3Config = []): void
    {
        $periodo = $matricula->periodo_academico_id
            ? PeriodoAcademico::find($matricula->periodo_academico_id)
            : null;

        if ($periodo && $periodo->es_vacacional) {
            return;
        }

        $modoPensionesSolicitado = (string) ($paso3Config['gen_modo'] ?? 'todo');
        $soloMatriculaConfirmado = (bool) ($paso3Config['solo_matricula_confirm'] ?? false);
        $modoPensiones = self::normalizarModoPensiones(
            $modoPensionesSolicitado,
            $soloMatriculaConfirmado,
            false
        );

        $mesPensionesIni = isset($paso3Config['mes_gen_inicio']) && $paso3Config['mes_gen_inicio'] !== ''
            ? (int) $paso3Config['mes_gen_inicio']
            : null;
        $mesPensionesFin = isset($paso3Config['mes_gen_fin']) && $paso3Config['mes_gen_fin'] !== ''
            ? (int) $paso3Config['mes_gen_fin']
            : null;

        if ($modoPensiones === 'todo') {
            $mesPensionesIni = null;
            $mesPensionesFin = null;
        }

        if ($modoPensiones === 'solo_matricula') {
            return;
        }

        // 1) Genera el bloque seleccionado para el cobro inicial.
        $this->generarDeudasPensiones($matricula, $mesPensionesIni, $mesPensionesFin);
        // 2) Completa meses faltantes del periodo para que queden como pendientes.
        $this->generarDeudasPensiones($matricula, null, null);
    }

    public static function normalizarModoPensiones(
        string $modoSolicitado,
        bool $soloMatriculaConfirmado = false,
        bool $hayRangoPensionesExplicito = false
    ): string {
        $modo = in_array($modoSolicitado, ['todo', 'n_meses', 'rango', 'solo_matricula'], true)
            ? $modoSolicitado
            : 'todo';

        if ($modo === 'solo_matricula' && (!$soloMatriculaConfirmado || $hayRangoPensionesExplicito)) {
            return 'todo';
        }

        return $modo;
    }

    private function descontarStockProductosExtras(int $matriculaId): void
    {
        $carrito = session('matricula.paso3_form.carrito', []);
        if (!is_array($carrito) || empty($carrito)) {
            return;
        }

        $productosSolicitados = collect($carrito)
            ->map(function ($item): array {
                return [
                    'producto_id' => (int) ($item['id'] ?? $item['producto_id'] ?? 0),
                    'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
                ];
            })
            ->filter(fn (array $item) => $item['producto_id'] > 0)
            ->groupBy('producto_id')
            ->map(fn ($group) => (int) $group->sum('cantidad'));

        if ($productosSolicitados->isEmpty()) {
            return;
        }

        $productos = InventarioProducto::query()
            ->whereIn('id', $productosSolicitados->keys()->all())
            ->lockForUpdate()
            ->get(['id', 'nombre', 'precio', 'stock'])
            ->keyBy('id');

        foreach ($productosSolicitados as $productoId => $cantidad) {
            $producto = $productos->get((int) $productoId);

            if (!$producto) {
                throw ValidationException::withMessages([
                    'extras' => "Uno de los productos seleccionados ya no existe (ID {$productoId}).",
                ]);
            }

            if ((int) $producto->stock < $cantidad) {
                throw ValidationException::withMessages([
                    'extras' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}, solicitado: {$cantidad}.",
                ]);
            }
        }

        foreach ($productosSolicitados as $productoId => $cantidad) {
            $producto = $productos->get((int) $productoId);
            if (!$producto || $cantidad <= 0) {
                continue;
            }

            $producto->decrement('stock', $cantidad);

            InventarioMovimiento::create([
                'inventario_producto_id' => $producto->id,
                'tipo' => 'salida',
                'cantidad' => $cantidad,
                'costo_unitario' => (float) $producto->precio,
                'referencia' => 'Matricula #' . $matriculaId,
                'usuario_id' => Auth::id(),
                'fecha_movimiento' => now()->toDateString(),
            ]);
        }
    }

    /**
     * Guarda el paso 3 cuando el periodo es VACACIONAL.
     * No genera deuda de matricula; solo pensiones de los meses vacacionales.
     */
    private function guardarPaso3Vacacional(Request $request, Matricula $matricula, PeriodoAcademico $periodo)
    {
        $anioId    = $matricula->anio_escolar_id;
        $periodoId = $matricula->periodo_academico_id;
        $nivel     = $matricula->grado?->nivel;

        $conceptoPension = $this->obtenerConceptoPension();
        if (!$conceptoPension) {
            return back()->withErrors(['_token' => 'No existe el concepto "Pensión". Configúralo primero en el Tarifario.']);
        }

        $tarifaPension = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where('activo', 1)
            ->first();

        if (!$tarifaPension) {
            return back()->withErrors(['_token' => "No hay tarifa de Pensión configurada para $nivel. Agrégala en el Tarifario."]);
        }

        [$mesInicioVacacional, $mesFinVacacional] = $periodo->rangoMesesPensiones();
        $mesesVacacionales = method_exists($periodo, 'mesesGenerablesPensiones')
            ? $periodo->mesesGenerablesPensiones($mesInicioVacacional, $mesFinVacacional)
            : range($mesInicioVacacional, $mesFinVacacional);

        if (empty($mesesVacacionales)) {
            return back()->withErrors(['_token' => 'El periodo vacacional no tiene meses configurados para cobrar pensión.']);
        }

        $montoMensualPension = (float) $tarifaPension->monto;
        $totalVacacional = round($montoMensualPension * count($mesesVacacionales), 2);
        $montoPagado     = (float) ($request->monto_pagado ?? 0);
        $registrarPago   = $montoPagado > 0;

        if ($montoPagado > $totalVacacional) {
            return back()->withInput()->withErrors(['monto_pagado' => 'El monto pagado no puede ser mayor al total.']);
        }

        if ($registrarPago && (!$request->filled('metodo') || !$request->filled('fecha_pago'))) {
            return back()->withInput()->withErrors(['metodo' => 'Indica método y fecha de pago para registrar el abono.']);
        }

        $deudaReferenciaId = null;
        $pagoId = null;

        DB::transaction(function () use (
            $request,
            $matricula,
            $anioId,
            $periodoId,
            $conceptoPension,
            $montoPagado,
            $registrarPago,
            $mesInicioVacacional,
            $mesFinVacacional,
            $mesesVacacionales,
            &$deudaReferenciaId,
            &$pagoId
        ) {
            $anioNombre = $matricula->anioEscolar?->nombre ?? date('Y');
            $anio = (int) $anioNombre;
            if ($anio < 2000) {
                $anio = (int) date('Y');
            }

            $meses = [
                1 => 'ENERO',
                2 => 'FEBRERO',
                3 => 'MARZO',
                4 => 'ABRIL',
                5 => 'MAYO',
                6 => 'JUNIO',
                7 => 'JULIO',
                8 => 'AGOSTO',
                9 => 'SEPTIEMBRE',
                10 => 'OCTUBRE',
                11 => 'NOVIEMBRE',
                12 => 'DICIEMBRE',
            ];

            $periodosPensionesVacacionales = collect($mesesVacacionales)
                ->map(fn (int $mes) => 'Pensión ' . ($meses[$mes] ?? 'MES') . " {$anio}")
                ->values()
                ->all();

            // Limpieza idempotente: solo pendientes/parciales del bloque vacacional.
            Deuda::where('matricula_id', $matricula->id)
                ->where('concepto_pago_id', $conceptoPension->id)
                ->whereIn('periodo', $periodosPensionesVacacionales)
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->delete();

            // Compatibilidad: limpia deudas legacy del concepto vacacional aun pendientes/parciales.
            $conceptoVacacionalLegacy = ConceptoPago::where('tipo', 'vacacional')->first();
            if ($conceptoVacacionalLegacy) {
                Deuda::where('matricula_id', $matricula->id)
                    ->where('concepto_pago_id', $conceptoVacacionalLegacy->id)
                    ->whereIn('estado', ['pendiente', 'parcial'])
                    ->delete();
            }

            // Genera pensiones de enero/febrero (o rango configurado en el periodo vacacional).
            $this->generarDeudasPensiones($matricula, $mesInicioVacacional, $mesFinVacacional);

            $deudaReferenciaId = (int) (Deuda::where('matricula_id', $matricula->id)
                ->where('concepto_pago_id', $conceptoPension->id)
                ->whereIn('periodo', $periodosPensionesVacacionales)
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->value('id') ?? 0);

            if ($registrarPago) {
                $pago = Pago::create([
                    'anio_escolar_id'     => $anioId,
                    'periodo_academico_id' => $periodoId,
                    'monto'               => $montoPagado,
                    'fecha_pago'          => $request->fecha_pago,
                    'metodo'              => $request->metodo,
                    'numero_operacion'    => $request->filled('numero_operacion') ? trim($request->numero_operacion) : null,
                    'estado'              => 'confirmado',
                    'usuario_registra_id' => Auth::id(),
                    'comentario'          => $request->comentario,
                ]);
                $pagoId = $pago->id;

                $montoRestante = (float) $montoPagado;
                $totalAplicado = 0.00;

                $deudasPendientes = Deuda::where('matricula_id', $matricula->id)
                    ->where('concepto_pago_id', $conceptoPension->id)
                    ->whereIn('periodo', $periodosPensionesVacacionales)
                    ->whereIn('estado', ['pendiente', 'parcial'])
                    ->where('saldo', '>', 0)
                    ->orderBy('fecha_vencimiento')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($deudasPendientes as $deudaPendiente) {
                    if ($montoRestante <= 0) {
                        break;
                    }

                    $saldoActual = (float) $deudaPendiente->saldo;
                    if ($saldoActual <= 0) {
                        continue;
                    }

                    $aplica = min($montoRestante, $saldoActual);
                    if ($aplica <= 0) {
                        continue;
                    }

                    $nuevoSaldo = max($saldoActual - $aplica, 0);
                    $deudaPendiente->saldo = $nuevoSaldo;
                    $deudaPendiente->estado = $nuevoSaldo <= 0 ? 'pagado' : 'parcial';
                    $deudaPendiente->save();

                    $pago->deudas()->attach($deudaPendiente->id, ['monto_aplicado' => $aplica]);

                    $montoRestante -= $aplica;
                    $totalAplicado += $aplica;
                }

                $pago->monto = round($totalAplicado, 2);
                $pago->save();
            }
        });

        session([
            'matricula.deuda_matricula_id' => $deudaReferenciaId > 0 ? $deudaReferenciaId : null,
            'matricula.pago_id' => $pagoId,
            'matricula.paso3_form' => $this->buildPaso3FormState(
                $request,
                'rango',
                $mesInicioVacacional,
                $mesFinVacacional
            ),
        ]);
        return redirect()->route($this->routePrefix() . 'matriculas.paso4');
    }

    private function aplicarPagoEnColeccionDeudas(Pago $pago, $deudas, float $montoDisponible): float
    {
        $montoRestante = max(round($montoDisponible, 2), 0.00);
        $totalAplicado = 0.00;

        foreach ($deudas as $deudaPendiente) {
            if ($montoRestante <= 0) {
                break;
            }

            $saldoActual = (float) $deudaPendiente->saldo;
            if ($saldoActual <= 0) {
                continue;
            }

            $aplica = min($montoRestante, $saldoActual);
            if ($aplica <= 0) {
                continue;
            }

            $nuevoSaldo = max($saldoActual - $aplica, 0);
            $deudaPendiente->saldo = $nuevoSaldo;
            $deudaPendiente->estado = $nuevoSaldo <= 0 ? 'pagado' : 'parcial';
            $deudaPendiente->save();

            $pago->deudas()->syncWithoutDetaching([
                $deudaPendiente->id => ['monto_aplicado' => $aplica],
            ]);

            $montoRestante = round($montoRestante - $aplica, 2);
            $totalAplicado = round($totalAplicado + $aplica, 2);
        }

        return $totalAplicado;
    }

    private function buildPaso3FormState(
        Request $request,
        ?string $genModo = null,
        ?int $mesGenInicio = null,
        ?int $mesGenFin = null
    ): array {
        $modo = (string) ($genModo ?? $request->input('gen_modo', 'todo'));

        if ($mesGenInicio === null && $request->filled('mes_gen_inicio')) {
            $mesGenInicio = (int) $request->input('mes_gen_inicio');
        }
        if ($mesGenFin === null && $request->filled('mes_gen_fin')) {
            $mesGenFin = (int) $request->input('mes_gen_fin');
        }

        if ($modo === 'solo_matricula') {
            $mesGenInicio = null;
            $mesGenFin = null;
        }
        if ($modo === 'todo') {
            $mesGenInicio = null;
            $mesGenFin = null;
        }

        if ($mesGenInicio !== null && $mesGenFin !== null && $mesGenFin < $mesGenInicio) {
            $mesGenFin = $mesGenInicio;
        }

        return [
            'matricula_id' => (int) session('matricula.id', 0),
            'gen_modo' => $modo,
            'solo_matricula_confirm' => $request->boolean('solo_matricula_confirm'),
            'mes_gen_inicio' => $mesGenInicio,
            'mes_gen_fin' => $mesGenFin,
            'descuento_monto'    => (float) $request->input('descuento_monto', 0),
            'descuento_concepto' => (string) $request->input('descuento_concepto', ''),
            'monto_pagado' => (string) $request->input('monto_pagado', '0.00'),
            'abono_matricula' => (string) $request->input('abono_matricula', ''),
            'abono_extras'    => (string) $request->input('abono_extras', ''),
            'abono_libros'    => (string) $request->input('abono_libros', ''),
            'abono_pensiones' => (string) $request->input('abono_pensiones', ''),
            'fecha_pago' => (string) $request->input('fecha_pago', ''),
            'comentario' => (string) $request->input('comentario', ''),
            'carrito' => $this->buildPaso3CarritoState($request),
        ];
    }

    private function buildPaso3CarritoState(Request $request): array
    {
        $extras = collect($request->input('extras', []))
            ->map(function ($extra) {
                return [
                    'producto_id' => (int) ($extra['producto_id'] ?? 0),
                    'cantidad' => max(1, (int) ($extra['cantidad'] ?? 1)),
                ];
            })
            ->filter(fn ($extra) => $extra['producto_id'] > 0)
            ->values();

        if ($extras->isEmpty()) {
            return [];
        }

        $productos = InventarioProducto::query()
            ->whereIn('id', $extras->pluck('producto_id')->all())
            ->get(['id', 'nombre', 'precio', 'stock'])
            ->keyBy('id');

        $carrito = [];
        foreach ($extras as $extra) {
            $producto = $productos->get($extra['producto_id']);
            if (!$producto) {
                continue;
            }

            $cantidad = $extra['cantidad'];
            $stock = (int) $producto->stock;
            if ($stock > 0 && $cantidad > $stock) {
                $cantidad = $stock;
            }

            $carrito[] = [
                'id' => (int) $producto->id,
                'nombre' => (string) $producto->nombre,
                'precio' => (float) $producto->precio,
                'cantidad' => max(1, $cantidad),
                'stock' => $stock > 0 ? $stock : max(1, $cantidad),
            ];
        }

        return $carrito;
    }

    protected function asegurarCodigosEstudiante(Estudiante $estudiante): void
    {
        $debeGuardar = false;

        if ($this->codigoVacio($estudiante->carnet_codigo)) {
            $estudiante->carnet_codigo = $this->generarCodigoCarnet();
            $debeGuardar = true;
        }

        if ($this->codigoVacio($estudiante->codigo)) {
            $estudiante->codigo = $this->generarCodigoEstudiante($estudiante->id);
            $debeGuardar = true;
        }

        if ($debeGuardar) {
            $estudiante->save();
        }
    }

    protected function codigoVacio(?string $codigo): bool
    {
        $normalizado = trim((string) $codigo);

        return $normalizado === '' || $normalizado === '-';
    }

    protected function generarCodigoCarnet(): string
    {
        do {
            $codigo = 'JP-' . strtoupper(Str::random(8));
        } while (Estudiante::where('carnet_codigo', $codigo)->exists());

        return $codigo;
    }

    protected function generarCodigoEstudiante(int $estudianteId): string
    {
        return 'ALU-' . str_pad((string) $estudianteId, 5, '0', STR_PAD_LEFT);
    }

    private function generarDeudasPensiones(
        Matricula $matricula,
        ?int $mesInicioOverride = null,
        ?int $mesFinOverride = null
    ): void {
        $matricula->loadMissing(['grado', 'anioEscolar']);

        $anioId     = $matricula->anio_escolar_id;
        $periodoId  = $matricula->periodo_academico_id;
        $nivel      = $matricula->grado?->nivel;

        if (!$nivel) return;

        $conceptoPension = $this->obtenerConceptoPension();
        if (!$conceptoPension) return;

        // Tarifa base: buscar la general (mes=0); si no existe con mes, intenta sin columna mes
        $tarifaPensionBase = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where('activo', 1)
            ->where('mes', 0)
            ->first()
            ?? Tarifa::where('anio_escolar_id', $anioId)
                ->where('nivel', $nivel)
                ->where('concepto_pago_id', $conceptoPension->id)
                ->where('activo', 1)
                ->first();

        if (!$tarifaPensionBase) return;

        // Pre-cargar tarifas especiales por mes para este nivel/año (mes > 0)
        $tarifasEspeciales = Tarifa::where('anio_escolar_id', $anioId)
            ->where('nivel', $nivel)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where('activo', 1)
            ->where('mes', '>', 0)
            ->get()
            ->keyBy('mes');

        // ✅ Borra la pensión "genérica" (periodo='Pensión' y/o fecha_vencimiento NULL)
        Deuda::where('matricula_id', $matricula->id)
            ->where('concepto_pago_id', $conceptoPension->id)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhere('periodo', 'Pensión');
            })
            ->delete();

        $anioNombre = $matricula->anioEscolar?->nombre ?? date('Y');
        $anio = (int) $anioNombre;
        if ($anio < 2000) $anio = (int) date('Y');

        $periodoModel = $periodoId ? PeriodoAcademico::find($periodoId) : null;

        if ($periodoModel) {
            if (method_exists($periodoModel, 'mesesGenerablesDesdePeriodo')) {
                $mesesGenerables = $periodoModel->mesesGenerablesDesdePeriodo($mesInicioOverride, $mesFinOverride);
            } else {
                $mesesGenerables = $periodoModel->mesesGenerablesPensiones($mesInicioOverride, $mesFinOverride);
            }
        } else {
            if ($mesInicioOverride !== null && $mesFinOverride !== null) {
                [$mesInicio, $mesFin] = [$mesInicioOverride, $mesFinOverride];
            } else {
                [$mesInicio, $mesFin] = [3, 12];
            }
            if ($mesFin < $mesInicio) {
                $mesFin = $mesInicio;
            }
            $mesesGenerables = range($mesInicio, $mesFin);
        }

        if (empty($mesesGenerables)) {
            return;
        }

        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        foreach ($mesesGenerables as $mes) {
            // Usar tarifa específica del mes si existe, si no la general
            $tarifaMes    = $tarifasEspeciales->get($mes) ?? $tarifaPensionBase;
            $pensionTarifa = (float) $tarifaMes->monto;
            $pensionFinal  = $this->calcularPensionMensualConDescuentos($matricula, $pensionTarifa);

            // Art. 61 del Reglamento Interno: cada cuota vence en la fecha del
            // cronograma oficial del año (último día hábil del mes; diciembre el 15).
            $fechaVenc = CronogramaPension::fechaVencimiento($anioId, $anio, $mes)->toDateString();

            $nombreMes = $meses[$mes] ?? 'MES';
            $periodoLabel = "Pensión {$nombreMes} {$anio}";

            $yaExisteMes = Deuda::where('matricula_id', $matricula->id)
                ->where('concepto_pago_id', $conceptoPension->id)
                ->where('periodo', $periodoLabel)
                ->exists();

            if ($yaExisteMes) {
                continue;
            }

            // No crear deudas de pensión con monto S/.0.00
            if ($pensionFinal <= 0) continue;

            $estado = 'pendiente';
            $saldo  = $pensionFinal;

            Deuda::create([
                'anio_escolar_id' => $anioId,
                'periodo_academico_id' => $periodoId,
                'matricula_id' => $matricula->id,
                'concepto_pago_id' => $conceptoPension->id,
                'periodo' => $periodoLabel,
                'monto' => $pensionFinal,
                'saldo' => $saldo,
                'fecha_vencimiento' => $fechaVenc,
                'estado' => $estado,
            ]);
        }
    }

    private function obtenerConceptoMatricula(): ?ConceptoPago
    {
        return ConceptoPago::where('tipo', 'matricula')->first()
            ?? ConceptoPago::whereIn('nombre', ['Matrícula', 'Matricula'])->first();
    }

    private function obtenerConceptoPension(): ?ConceptoPago
    {
        return ConceptoPago::whereIn('tipo', ['mensual', 'pension'])->first()
            ?? ConceptoPago::whereIn('nombre', ['Pensión', 'Pension'])->first();
    }

    private function obtenerConceptoLibros(): ?ConceptoPago
    {
        return ConceptoPago::where('tipo', 'libros')->first()
            ?? ConceptoPago::whereIn('nombre', ['Libros', 'Material Educativo'])->first();
    }

    private function asegurarTarifasBasePorNivel(?int $anioId, ?string $nivel): void
    {
        $anioId = (int) $anioId;
        $nivel = trim((string) $nivel);
        if ($anioId <= 0 || $nivel === '') {
            return;
        }

        $conceptoMatricula = $this->obtenerConceptoMatricula();
        $conceptoPension = $this->obtenerConceptoPension();

        if ($conceptoMatricula) {
            $montoDefaultMatricula = max((float) ($conceptoMatricula->monto_base ?? 0), 300.00);
            $this->asegurarTarifaActiva($anioId, $nivel, $conceptoMatricula, $montoDefaultMatricula);
        }

        if ($conceptoPension) {
            $montoDefaultPension = max((float) ($conceptoPension->monto_base ?? 0), 250.00);
            $this->asegurarTarifaActiva($anioId, $nivel, $conceptoPension, $montoDefaultPension);
        }
    }

    private function asegurarTarifaActiva(
        int $anioId,
        string $nivel,
        ConceptoPago $concepto,
        float $montoDefault
    ): void {
        $tarifa = Tarifa::firstOrCreate(
            [
                'anio_escolar_id' => $anioId,
                'concepto_pago_id' => $concepto->id,
                'nivel' => $nivel,
                'mes' => 0,
            ],
            [
                'monto' => $montoDefault,
                'activo' => true,
                'vigente_desde' => null,
                'vigente_hasta' => null,
            ]
        );

        $debeGuardar = false;
        if (!$tarifa->activo) {
            $tarifa->activo = true;
            $debeGuardar = true;
        }

        if ((float) $tarifa->monto <= 0 && $montoDefault > 0) {
            $tarifa->monto = $montoDefault;
            $debeGuardar = true;
        }

        if ($debeGuardar) {
            $tarifa->save();
        }
    }

    private function calcularPensionMensualConDescuentos(Matricula $matricula, float $pensionTarifa): float
    {
        $descuentos = MatriculaDescuento::where('matricula_id', $matricula->id)->get();

        // Descuento personalizado (monto fijo)
        $personalizado = $descuentos->firstWhere('tipo', 'personalizado');
        if ($personalizado) {
            $monto = max(0, $pensionTarifa - (float) $personalizado->monto);
            return round($monto, 2);
        }

        return round(max($pensionTarifa, 0), 2);
    }

    private function actualizarObservacionesHermanos(
        Matricula $matricula,
        \Illuminate\Support\Collection $hermanos,
        bool $esPrimerHijo,
        bool $esMenor
    ): void {
        // Respetar NH: si fue marcado manualmente como "no hermano", no tocar
        if (Str::upper(trim((string) $matricula->observacion)) === 'NH') {
            return;
        }

        if ($esPrimerHijo) {
            // Sin hermanos matriculados este año: sin marca
            $matricula->observacion = null;
            $matricula->save();
            return;
        }

        // Lista completa: matrícula actual + hermanos existentes
        $todos = $hermanos
            ->map(fn ($h) => $h['matricula'])
            ->push($matricula);

        $todos->each(fn ($m) => $m->loadMissing('estudiante'));

        $count = $todos->count();

        // Ordenar por fecha_nacimiento: más viejo primero (H+), más joven al final (H-)
        // Sin fecha_nacimiento → se trata como el de mayor edad (va primero)
        $sorted = $todos->sortBy(function (Matricula $m) {
            $fecha = $m->estudiante?->fecha_nacimiento;
            if (!$fecha) return '1900-01-01';
            return $fecha instanceof \Carbon\Carbon
                ? $fecha->format('Y-m-d')
                : (string) $fecha;
        })->values();

        foreach ($sorted as $i => $mat) {
            // No tocar los que tienen NH manual
            if (Str::upper(trim((string) $mat->observacion)) === 'NH') {
                continue;
            }

            $obs = match (true) {
                $count === 1       => null,
                $i === 0           => 'H+',  // el mayor
                $i === $count - 1  => 'H-',  // el menor
                default            => 'H',   // del medio
            };

            if ($mat->observacion !== $obs) {
                $mat->observacion = $obs;
                $mat->save();
            }
        }
    }

    private function detectarHermanos(int $estudianteId, int $anioId): \Illuminate\Support\Collection
    {
        $apoderadoId = session('matricula.apoderado_id');
        if (!$apoderadoId) return collect();

        $apoderado = Apoderado::with(['estudiantes' => function ($q) use ($anioId, $estudianteId) {
            $q->where('estudiantes.id', '!=', $estudianteId)
              ->with(['matriculas' => fn ($mq) => $mq
                  ->where('anio_escolar_id', $anioId)
                  ->where('estado', '!=', 'retirada')]);
        }])->find($apoderadoId);

        if (!$apoderado) return collect();

        return $apoderado->estudiantes
            ->filter(fn ($est) => $est->matriculas->isNotEmpty())
            ->map(fn ($est) => ['estudiante' => $est, 'matricula' => $est->matriculas->first()]);
    }

    private function esHijoMenor(?\App\Models\Estudiante $estudiante, \Illuminate\Support\Collection $hermanos): bool
    {
        if ($hermanos->isEmpty() || !$estudiante?->fecha_nacimiento) return false;

        $fnStr = fn($f) => $f instanceof \Carbon\Carbon ? $f->format('Y-m-d') : (string) $f;
        $miStr = $fnStr($estudiante->fecha_nacimiento);

        return $hermanos->every(function ($h) use ($miStr, $fnStr) {
            $fechaH = $h['estudiante']->fecha_nacimiento;
            return !$fechaH || $miStr > $fnStr($fechaH);
        });
    }

    private function detectarHermanoMenorPrevio(\Illuminate\Support\Collection $hermanos, ?ConceptoPago $conceptoGastos): ?array
    {
        if (!$conceptoGastos || $hermanos->isEmpty()) return null;

        return $hermanos->first(function ($h) use ($conceptoGastos) {
            $deuda = Deuda::where('matricula_id', $h['matricula']->id)
                ->where('concepto_pago_id', $conceptoGastos->id)
                ->where('estado', 'pendiente')
                ->first();
            return $deuda && abs((float) $deuda->monto - 95.00) < 0.01;
        });
    }
}
