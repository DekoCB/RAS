<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Seccion;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EstudianteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:estudiante.ver')->only(['index', 'show', 'info', 'pagos']);
        $this->middleware('permission:estudiante.crear')->only(['create', 'store']);
        $this->middleware('permission:estudiante.editar')->only(['edit', 'update']);
        $this->middleware('permission:estudiante.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q         = trim((string) $request->input('q', ''));
        $nivel     = $request->input('nivel', '');
        $gradoId   = (int) $request->input('grado_id', 0) ?: null;
        $seccionId = (int) $request->input('seccion_id', 0) ?: null;

        $nivelesValidos = ['Inicial', 'Primaria', 'Secundaria'];
        if (!in_array($nivel, $nivelesValidos, true)) {
            $nivel = '';
        }

        $estadosMatriculaVisibles = [
            Matricula::ESTADO_REGISTRADA,
            Matricula::ESTADO_ACTIVA,
            Matricula::ESTADO_RETIRADA,
        ];

        $ultimaMatriculaEstado = Matricula::query()
            ->select('estado')
            ->whereColumn('estudiante_id', 'estudiantes.id')
            ->whereIn('estado', $estadosMatriculaVisibles)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(1);

        $baseQuery = Estudiante::with('apoderados')
            ->addSelect(['ultima_matricula_estado' => $ultimaMatriculaEstado])
            ->withMax([
                'matriculas as matriculas_max_created_at' => function ($query) use ($estadosMatriculaVisibles) {
                    $query->whereIn('estado', $estadosMatriculaVisibles);
                }
            ], 'created_at')
            ->where(function ($query) use ($estadosMatriculaVisibles) {
                $query->whereDoesntHave('matriculas')
                    ->orWhereHas('matriculas', function ($matriculaQuery) use ($estadosMatriculaVisibles) {
                        $matriculaQuery->whereIn('estado', $estadosMatriculaVisibles);
                    });
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('dni', 'like', "%{$q}%")
                        ->orWhere('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%");
                });
            });

        // Conteos por nivel para los tabs (sin filtro de nivel aplicado, pero sí con búsqueda)
        $conteosPorNivel = [];
        foreach ($nivelesValidos as $n) {
            $conteosPorNivel[$n] = (clone $baseQuery)
                ->whereHas('matriculas', function ($mq) use ($estadosMatriculaVisibles, $n) {
                    $mq->whereIn('estado', $estadosMatriculaVisibles)
                       ->whereHas('grado', fn ($g) => $g->where('nivel', $n));
                })
                ->count();
        }

        $estudiantes = (clone $baseQuery)
            ->when($nivel !== '', function ($query) use ($nivel, $estadosMatriculaVisibles) {
                $query->whereHas('matriculas', function ($mq) use ($nivel, $estadosMatriculaVisibles) {
                    $mq->whereIn('estado', $estadosMatriculaVisibles)
                       ->whereHas('grado', fn ($g) => $g->where('nivel', $nivel));
                });
            })
            ->when($gradoId, function ($query) use ($gradoId, $estadosMatriculaVisibles) {
                $query->whereHas('matriculas', function ($mq) use ($gradoId, $estadosMatriculaVisibles) {
                    $mq->whereIn('estado', $estadosMatriculaVisibles)->where('grado_id', $gradoId);
                });
            })
            ->when($seccionId, function ($query) use ($seccionId, $estadosMatriculaVisibles) {
                $query->whereHas('matriculas', function ($mq) use ($seccionId, $estadosMatriculaVisibles) {
                    $mq->whereIn('estado', $estadosMatriculaVisibles)->where('seccion_id', $seccionId);
                });
            })
            ->orderByDesc('matriculas_max_created_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Grados para el filtro (todos agrupados por nivel, o solo los del nivel activo)
        $grados = Grado::when($nivel !== '', fn ($q) => $q->where('nivel', $nivel))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nivel']);

        // Secciones: solo cuando hay grado o nivel seleccionado
        $secciones = ($gradoId || $nivel !== '')
            ? Seccion::when($gradoId, fn ($q) => $q->where('grado_id', $gradoId))
                ->when(!$gradoId && $nivel !== '', fn ($q) =>
                    $q->whereHas('grado', fn ($g) => $g->where('nivel', $nivel))
                )
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'grado_id'])
            : collect();

        return view('secretaria.estudiantes.index', compact(
            'estudiantes', 'nivel', 'conteosPorNivel',
            'grados', 'secciones', 'gradoId', 'seccionId'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apoderados = Apoderado::orderBy('apellidos')->orderBy('nombres')->get();

        return view('secretaria.estudiantes.create', compact('apoderados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['nullable', 'string', 'max:50'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'string', 'max:10'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:30'],
            'apoderado_ids' => ['nullable', 'array'],
            'apoderado_ids.*' => ['integer', 'exists:apoderados,id'],
        ]);

        $apoderadoIds = $data['apoderado_ids'] ?? [];
        unset($data['apoderado_ids']);

        $estudiante = Estudiante::create($data);
        $estudiante->apoderados()->sync($apoderadoIds);

        AuditoriaService::log($request->user()?->id, 'crear', 'estudiantes', $estudiante->id, $data, $request);

        return redirect()->route('secretaria.estudiantes.index')->with('status', 'Estudiante creado.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Estudiante $estudiante)
    {
        return view('secretaria.estudiantes.show', compact('estudiante'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Estudiante $estudiante)
    {
        $estudiante->load([
            'apoderados' => function ($query) {
                $query->withPivot(['relacion', 'principal'])
                    ->orderByDesc('apoderado_estudiante.principal')
                    ->orderBy('apellidos')
                    ->orderBy('nombres');
            },
        ]);

        $apoderadoPrincipal = $estudiante->apoderados->firstWhere('pivot.principal', true)
            ?? $estudiante->apoderados->first();

        return view('secretaria.estudiantes.edit', compact('estudiante', 'apoderadoPrincipal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        $estudiante->load([
            'apoderados' => function ($query) {
                $query->withPivot(['relacion', 'principal'])
                    ->orderByDesc('apoderado_estudiante.principal')
                    ->orderBy('apellidos')
                    ->orderBy('nombres');
            },
        ]);

        $apoderadoPrincipal = $estudiante->apoderados->firstWhere('pivot.principal', true)
            ?? $estudiante->apoderados->first();

        $data = $request->validate([
            'codigo' => ['nullable', 'string', 'max:50'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'string', 'max:10'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:30'],
            'apoderado_nombres' => [
                'nullable',
                'string',
                'max:255',
                'required_with:apoderado_apellidos,apoderado_dni,apoderado_fecha_nacimiento,apoderado_sexo,apoderado_estado_civil,apoderado_ciudad,apoderado_telefono,apoderado_email,apoderado_direccion,apoderado_relacion',
            ],
            'apoderado_apellidos' => [
                'nullable',
                'string',
                'max:255',
                'required_with:apoderado_nombres,apoderado_dni,apoderado_fecha_nacimiento,apoderado_sexo,apoderado_estado_civil,apoderado_ciudad,apoderado_telefono,apoderado_email,apoderado_direccion,apoderado_relacion',
            ],
            'apoderado_dni' => ['nullable', 'string', 'max:20'],
            'apoderado_fecha_nacimiento' => ['nullable', 'date'],
            'apoderado_sexo' => ['nullable', 'string', 'max:10'],
            'apoderado_estado_civil' => ['nullable', 'string', 'max:255'],
            'apoderado_ciudad' => ['nullable', 'string', 'max:255'],
            'apoderado_telefono' => ['nullable', 'string', 'max:30'],
            'apoderado_email' => ['nullable', 'email', 'max:255'],
            'apoderado_direccion' => ['nullable', 'string', 'max:255'],
            'apoderado_relacion' => ['nullable', 'string', 'max:100'],
        ]);

        $estudianteData = [
            'codigo' => $data['codigo'] ?? null,
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'dni' => $data['dni'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'sexo' => $data['sexo'] ?? null,
            'ciudad' => $data['ciudad'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'estado' => $data['estado'] ?? null,
        ];

        $estudiante->update($estudianteData);

        $camposApoderado = [
            'apoderado_nombres',
            'apoderado_apellidos',
            'apoderado_dni',
            'apoderado_fecha_nacimiento',
            'apoderado_sexo',
            'apoderado_estado_civil',
            'apoderado_ciudad',
            'apoderado_telefono',
            'apoderado_email',
            'apoderado_direccion',
            'apoderado_relacion',
        ];

        $actualizarApoderado = collect($camposApoderado)->contains(function ($campo) use ($data) {
            $valor = $data[$campo] ?? null;

            if (is_string($valor)) {
                return trim($valor) !== '';
            }

            return !is_null($valor);
        });

        if ($actualizarApoderado) {
            $apoderadoData = [
                'nombres' => $data['apoderado_nombres'] ?? null,
                'apellidos' => $data['apoderado_apellidos'] ?? null,
                'dni' => $data['apoderado_dni'] ?? null,
                'fecha_nacimiento' => $data['apoderado_fecha_nacimiento'] ?? null,
                'sexo' => $data['apoderado_sexo'] ?? null,
                'estado_civil' => $data['apoderado_estado_civil'] ?? null,
                'ciudad' => $data['apoderado_ciudad'] ?? null,
                'telefono' => $data['apoderado_telefono'] ?? null,
                'email' => $data['apoderado_email'] ?? null,
                'direccion' => $data['apoderado_direccion'] ?? null,
            ];

            if ($apoderadoPrincipal) {
                $apoderadoPrincipal->update($apoderadoData);
                $estudiante->apoderados()->updateExistingPivot($apoderadoPrincipal->id, [
                    'relacion' => $data['apoderado_relacion'] ?? null,
                    'principal' => true,
                ]);
            } else {
                $apoderadoPrincipal = Apoderado::create($apoderadoData);
                $estudiante->apoderados()->attach($apoderadoPrincipal->id, [
                    'relacion' => $data['apoderado_relacion'] ?? 'Padre',
                    'principal' => true,
                ]);
            }
        }

        AuditoriaService::log($request->user()?->id, 'editar', 'estudiantes', $estudiante->id, [
            'estudiante' => $estudianteData,
            'apoderado_principal' => $actualizarApoderado ? ($apoderadoData ?? null) : null,
        ], $request);

        return redirect()->route('secretaria.estudiantes.index')->with('status', 'Estudiante actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Estudiante $estudiante)
    {
        $estudiante->apoderados()->detach();
        $estudiante->delete();

        AuditoriaService::log($request->user()?->id, 'eliminar', 'estudiantes', $estudiante->id, null, $request);

        return redirect()->route('secretaria.estudiantes.index')->with('status', 'Estudiante eliminado.');
    }

    public function info(Estudiante $estudiante)
    {
        $estudiante->load([
            'apoderados' => function ($query) {
                $query->withPivot(['relacion', 'principal'])
                      ->orderByDesc('apoderado_estudiante.principal');
            },
        ]);

        $matricula = $estudiante->matriculas()
            ->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA])
            ->with(['grado', 'seccion', 'anioEscolar'])
            ->latest()
            ->first();

        $apoderados = $estudiante->apoderados;

        // ── Detect siblings via shared apoderado ──
        $hermanos = collect();
        $miTag    = null;

        $apodIds = $apoderados->pluck('id');
        if ($apodIds->isNotEmpty()) {
            $hermanoIds = DB::table('apoderado_estudiante')
                ->whereIn('apoderado_id', $apodIds)
                ->where('estudiante_id', '!=', $estudiante->id)
                ->pluck('estudiante_id')
                ->unique()
                ->values()
                ->toArray();

            if (!empty($hermanoIds)) {
                $todosIds = array_merge([$estudiante->id], $hermanoIds);

                $matriculasMap = Matricula::whereIn('estudiante_id', $todosIds)
                    ->whereIn('estado', [Matricula::ESTADO_REGISTRADA, Matricula::ESTADO_ACTIVA])
                    ->with(['grado', 'anioEscolar'])
                    ->orderByDesc('id')
                    ->get()
                    ->unique('estudiante_id')
                    ->keyBy('estudiante_id');

                // Build orden lookup
                $ordenMap = collect($todosIds)->mapWithKeys(fn($id) => [
                    $id => $matriculasMap->get($id)?->grado?->orden,
                ])->filter(fn($o) => $o !== null);

                $computeTag = function (int $eid) use ($ordenMap) {
                    $miOrden = $ordenMap->get($eid);
                    if ($miOrden === null) {
                        return $ordenMap->count() >= 1 ? 'H' : null;
                    }
                    $otros = $ordenMap->filter(fn($o, $id) => $id !== $eid);
                    if ($otros->isEmpty()) return null;
                    if ($miOrden < $otros->min()) return 'H-';
                    if ($miOrden > $otros->max()) return 'H+';
                    return 'H';
                };

                $miTag = $computeTag($estudiante->id);

                $hermanos = Estudiante::whereIn('id', $hermanoIds)
                    ->orderBy('apellidos')
                    ->orderBy('nombres')
                    ->get()
                    ->map(function ($h) use ($matriculasMap, $computeTag) {
                        $h->hermano_matricula = $matriculasMap->get($h->id);
                        $h->hermano_tag       = $computeTag($h->id);
                        return $h;
                    });
            }
        }

        return view('secretaria.estudiantes.info', compact('estudiante', 'matricula', 'apoderados', 'hermanos', 'miTag'));
    }

    public function carnet(Estudiante $estudiante)
    {
        if (!$estudiante->carnet_codigo) {
            do {
                $codigo = 'JP-' . strtoupper(Str::random(8));
            } while (Estudiante::where('carnet_codigo', $codigo)->exists());

            $estudiante->carnet_codigo = $codigo;
            $estudiante->save();
        }

        $matricula = $estudiante->matriculas()
            ->where('estado', Matricula::ESTADO_REGISTRADA)
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

        return view('secretaria.estudiantes.carnet', compact('estudiante', 'matricula', 'qr'));
    }

    public function pagos(Estudiante $estudiante)
    {
        // 1) Matrículas del estudiante
        $estadosMatriculaPagos = [
            Matricula::ESTADO_REGISTRADA,
            Matricula::ESTADO_ACTIVA,
            Matricula::ESTADO_RETIRADA,
        ];

        $matriculaIds = $estudiante->matriculas()
            ->whereIn('estado', $estadosMatriculaPagos)
            ->pluck('id');

        // 2) DEUDAS (aquí salen Matrícula + Pensiones MARZO..DIC)
        $deudas = \App\Models\Deuda::with(['concepto'])
            ->whereIn('matricula_id', $matriculaIds)
            ->orderByRaw("CASE WHEN fecha_vencimiento IS NULL THEN 0 ELSE 1 END") // matrícula primero (null)
            ->orderBy('fecha_vencimiento')
            ->get();

        // Resumen de deudas
        $deudaTotal = (float) $deudas->sum('monto');
        $saldoPendiente = (float) $deudas
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->sum('saldo');

        $hoy = now()->toDateString();
        $vencido = (float) $deudas
            ->filter(fn($d) => in_array($d->estado, ['pendiente', 'parcial'], true)
                && (float) $d->saldo > 0
                && $d->fecha_vencimiento
                && $d->fecha_vencimiento < $hoy)
            ->sum('saldo');

        $cantidadDeudas = (int) $deudas->whereIn('estado', ['pendiente', 'parcial'])->count();

        // 3) PAGOS (solo los pagos que aplicaron a deudas de este estudiante)
        $pagos = \App\Models\Pago::with([
            'deudas' => function ($query) use ($matriculaIds) {
                $query->whereIn('matricula_id', $matriculaIds)
                    ->with(['concepto', 'matricula']);
            },
            'registradoPor',
        ])
            ->whereHas('deudas', function ($q) use ($matriculaIds) {
                $q->whereIn('matricula_id', $matriculaIds);
            })
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->get();

        $totalPagado = (float) $pagos->sum(function ($pago) {
            return (float) $pago->deudas->sum(function ($deuda) {
                return (float) ($deuda->pivot->monto_aplicado ?? 0);
            });
        });
        $ultimoPago = $pagos->first();
        $metodos = $pagos->pluck('metodo')->filter()->unique()->values();

        return view('secretaria.estudiantes.pagos', compact(
            'estudiante',
            'deudas',
            'deudaTotal',
            'saldoPendiente',
            'vencido',
            'cantidadDeudas',
            'pagos',
            'totalPagado',
            'ultimoPago',
            'metodos'
        ));
    }
}
