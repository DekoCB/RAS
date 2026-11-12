@extends('layouts.mantis')

@section('title', 'Expediente de admision | R.A.S')
@section('page-title', 'Expediente de admision')
@section('body-class', 'role-superadmin')

@include('admision._layout_head')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $iniciales    = mb_strtoupper(mb_substr($postulante->nombres, 0, 1) . mb_substr($postulante->apellidos, 0, 1));
    $nivelCls     = strtolower($postulante->nivel ?? '');
    $obligatorios = \App\Models\PostulanteDocumento::OBLIGATORIOS;
    $cargados     = count($obligatorios) - count($faltantes);
    $completadas  = $postulante->etapas->where('estado', 'completada')->count();
    $pagado       = $postulante->pagoValidado();
    $pillEstado   = match ($postulante->estado) {
        'admitido'    => 'ad-pill--ok',
        'no_admitido' => 'ad-pill--danger',
        'desistido'   => 'ad-pill--mute',
        'borrador', 'preinscrito' => 'ad-pill--gold',
        default       => 'ad-pill--navy',
    };
    $apoderado = trim($postulante->apoderado_apellidos . ' ' . $postulante->apoderado_nombres);
    $puedeMatricular = $postulante->puedeMatricularse() && auth()->user()?->hasAnyRole(['Secretaria', 'SuperAdmin']);
    $prefijoMat = auth()->user()?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.';
    $ficha = [
        ['Origen', \App\Models\Postulante::ORIGENES[$postulante->origen] ?? $postulante->origen],
        ['DNI', $postulante->dni],
        ['Fecha de nacimiento', $postulante->fecha_nacimiento?->format('d/m/Y')],
        ['Sexo', ['M' => 'Masculino', 'F' => 'Femenino'][$postulante->sexo] ?? null],
        ['Direccion', $postulante->direccion],
        ['Colegio de procedencia', $postulante->colegio_procedencia],
        ['Apoderado', $apoderado],
        ['Parentesco', $postulante->apoderado_parentesco],
        ['DNI del apoderado', $postulante->apoderado_dni],
        ['Nacimiento del apoderado', $postulante->apoderado_fecha_nacimiento?->format('d/m/Y')],
        ['Sexo del apoderado', ['M' => 'Masculino', 'F' => 'Femenino'][$postulante->apoderado_sexo] ?? null],
        ['Estado civil', $postulante->apoderado_estado_civil],
        ['Ciudad', $postulante->apoderado_ciudad],
        ['Direccion del apoderado', $postulante->apoderado_direccion],
        ['Telefono', $postulante->apoderado_telefono],
        ['Correo', $postulante->apoderado_email],
    ];
    if ($postulante->apoderado2_nombres) {
        $ficha = array_merge($ficha, [
            ['2do apoderado', trim($postulante->apoderado2_apellidos . ' ' . $postulante->apoderado2_nombres)],
            ['Parentesco (2do)', $postulante->apoderado2_parentesco],
            ['DNI (2do)', $postulante->apoderado2_dni],
            ['Telefono (2do)', $postulante->apoderado2_telefono],
            ['Correo (2do)', $postulante->apoderado2_email],
        ]);
    }
@endphp
<div class="ad-page">
    <div class="ad-toolbar">
        <div class="ad-toolbar__lead">
            <div>
                <div class="ad-toolbar__kicker">Expediente {{ $postulante->codigo }}</div>
                <div class="ad-toolbar__text">
                    @if ($postulante->esWeb())
                        Pre-inscrito por la web el
                        {{ $postulante->preinscrito_at?->format('d/m/Y H:i') ?? $postulante->created_at?->format('d/m/Y') }}
                    @else
                        Registrado el {{ $postulante->created_at?->format('d/m/Y') }}
                    @endif
                    @if ($postulante->anioEscolar)
                        &middot; periodo {{ $postulante->anioEscolar->nombre }}
                    @endif
                </div>
            </div>
        </div>
        <div class="ad-toolbar__actions">
            <a href="{{ route('admision.index') }}" class="ad-btn ad-btn--ghost">
                <i data-feather="arrow-left"></i> Volver al listado
            </a>
            @if ($postulante->matricula_id)
                <a href="{{ route($prefijoMat . 'matriculas.resumen', $postulante->matricula_id) }}" class="ad-btn ad-btn--ghost">
                    <i data-feather="book-open"></i> Ver matricula
                </a>
            @elseif ($puedeMatricular)
                <form method="POST" action="{{ route('admision.matricular', $postulante) }}" class="ad-inline">
                    @csrf
                    <button type="submit" class="ad-btn ad-btn--gold">
                        <i data-feather="user-check"></i> Matricular
                    </button>
                </form>
            @endif
            @can('admision.registrar')
                <a href="{{ route('admision.paso2', $postulante) }}" class="ad-btn ad-btn--navy">
                    <i data-feather="edit-2"></i> Editar expediente
                </a>
                <form method="POST" action="{{ route('admision.eliminar', $postulante) }}" class="ad-inline"
                      onsubmit="return confirm('Eliminar este expediente y todos sus archivos? Esta accion no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button class="ad-btn ad-btn--danger"><i data-feather="trash-2"></i></button>
                </form>
            @endcan
        </div>
    </div>

    @include('admision._alertas')

    @if ($postulante->matricula_id)
        <div class="ad-alert ad-alert--ok">
            <i data-feather="check-circle"></i>
            <div>
                <strong>Matriculado.</strong>
                Este expediente ya genero la matricula
                @if ($postulante->matricula?->estudiante)
                    de {{ $postulante->matricula->estudiante->apellidos }}, {{ $postulante->matricula->estudiante->nombres }}
                    ({{ $postulante->matricula->grado?->nombre }} &middot; seccion {{ $postulante->matricula->seccion?->nombre }})
                @endif
                &mdash; estado: {{ $postulante->matricula?->estado }}.
            </div>
        </div>
    @elseif ($puedeMatricular)
        <div class="ad-alert ad-alert--info">
            <i data-feather="info"></i>
            <div>
                <strong>Admitido y pendiente de matricula.</strong>
                Con <em>Matricular</em> se abre el asistente con los datos del alumno y del apoderado ya llenados;
                solo falta elegir la seccion.
            </div>
        </div>
    @endif

    {{-- Identidad y medidores --}}
    <div class="ad-identity">
        <div class="ad-identity__main">
            <span class="ad-avatar ad-avatar--lg ad-avatar--{{ $nivelCls }}">{{ $iniciales }}</span>
            <div>
                <div class="ad-identity__name">{{ $postulante->nombreCompleto() }}</div>
                <div class="ad-identity__chips">
                    <span class="ad-pill {{ $pillEstado }}">{{ \App\Models\Postulante::ESTADOS[$postulante->estado] ?? $postulante->estado }}</span>
                    <span class="ad-chip"><i data-feather="book-open"></i>{{ $postulante->grado?->nombre ?? $postulante->nivel }}</span>
                    @if ($postulante->sede)
                        <span class="ad-chip"><i data-feather="map-pin"></i>{{ $postulante->sede->nombre }}</span>
                    @endif
                    @if ($postulante->apoderado_telefono)
                        <span class="ad-chip"><i data-feather="phone"></i>{{ $postulante->apoderado_telefono }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="ad-identity__side">
            <div class="ad-meter {{ count($faltantes) === 0 ? 'ad-meter--ok' : '' }}">
                <div class="ad-meter__lbl">Documentos</div>
                <div class="ad-meter__val">{{ $cargados }}/{{ count($obligatorios) }}</div>
                <div class="ad-meter__bar"><i style="width: {{ round($cargados / max(count($obligatorios), 1) * 100) }}%"></i></div>
            </div>
            <div class="ad-meter {{ $pagado ? 'ad-meter--ok' : 'ad-meter--warn' }}">
                <div class="ad-meter__lbl">Derecho</div>
                <div class="ad-meter__val">{{ $pagado ? 'Pagado' : 'Pendiente' }}</div>
                <div class="ad-meter__bar"><i style="width: {{ $pagado ? 100 : ($postulante->pagos->isNotEmpty() ? 50 : 0) }}%"></i></div>
            </div>
            <div class="ad-meter {{ $completadas === count($etapas) ? 'ad-meter--ok' : '' }}">
                <div class="ad-meter__lbl">Etapas</div>
                <div class="ad-meter__val">{{ $completadas }}/{{ count($etapas) }}</div>
                <div class="ad-meter__bar"><i style="width: {{ round($completadas / max(count($etapas), 1) * 100) }}%"></i></div>
            </div>
        </div>
    </div>

    {{-- Ficha --}}
    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon"><i data-feather="user"></i></span>
                <div>
                    <h2>Ficha del postulante</h2>
                    <p>Datos declarados en la postulacion.</p>
                </div>
            </div>
        </div>
        <div class="ad-facts">
            @foreach ($ficha as [$etiqueta, $valor])
                <div class="ad-fact">
                    <div class="ad-fact__lbl">{{ $etiqueta }}</div>
                    <div class="ad-fact__val {{ $valor ? '' : 'is-empty' }}">{{ $valor ?: 'Sin registrar' }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Documentos --}}
    <section class="ad-card">
        <div class="ad-card__head">
            <div class="ad-card__title">
                <span class="ad-card__icon ad-card__icon--gold"><i data-feather="clipboard"></i></span>
                <div>
                    <h2>Documentos</h2>
                    <p>Requisitos del reglamento de admision.</p>
                </div>
            </div>
            <div class="ad-card__aside">
                @if (count($faltantes) > 0)
                    <span class="ad-pill ad-pill--warn">Faltan {{ count($faltantes) }}</span>
                @else
                    <span class="ad-pill ad-pill--ok">Completos</span>
                @endif
            </div>
        </div>
        @include('admision._checklist', ['puedeSubir' => auth()->user()?->can('admision.registrar') ?? false, 'soloExistentesOpcionales' => true])
    </section>

    {{-- Etapas --}}
    @can('admision.registrar')
        <section class="ad-card">
            <div class="ad-card__head">
                <div class="ad-card__title">
                    <span class="ad-card__icon"><i data-feather="calendar"></i></span>
                    <div>
                        <h2>Etapas del proceso</h2>
                        <p>Reglamento, entrevista pastoral, socializacion y examen.</p>
                    </div>
                </div>
                <div class="ad-card__aside">
                    <span class="ad-pill {{ $completadas === count($etapas) ? 'ad-pill--ok' : 'ad-pill--navy' }}">
                        {{ $completadas }} de {{ count($etapas) }} completadas
                    </span>
                </div>
            </div>
            <div class="ad-card__body">
                @include('admision._etapas')
            </div>
        </section>
    @endcan

    {{-- Examen (RF-17) --}}
    @can('admision.evaluar')
        <section class="ad-card">
            <div class="ad-card__head">
                <div class="ad-card__title">
                    <span class="ad-card__icon"><i data-feather="award"></i></span>
                    <div>
                        <h2>Examen de admision</h2>
                        <p>Solo se ofrecen los examenes activos del nivel {{ $postulante->nivel }}.</p>
                    </div>
                </div>
            </div>
            <div class="ad-card__body">
                @if ($examenes->isEmpty())
                    <div class="ad-alert ad-alert--warn">
                        <i data-feather="alert-triangle"></i>
                        <div>
                            No hay examenes activos para {{ $postulante->nivel }}.
                            <a href="{{ route('admision.examenes') }}">Crear uno</a>.
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('admision.examen.registrar', $postulante) }}"
                          class="ad-grid" style="margin-bottom:20px">
                        @csrf
                        <div class="ad-field c-4">
                            <label for="examen_id">Examen</label>
                            <select id="examen_id" name="examen_id" class="ad-select" required>
                                @foreach ($examenes as $ex)
                                    <option value="{{ $ex->id }}">
                                        {{ $ex->titulo }} (max {{ $ex->puntaje_maximo }}, min {{ $ex->puntaje_minimo }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ad-field c-3">
                            <label for="fecha_examen">Fecha</label>
                            <input id="fecha_examen" type="datetime-local" name="fecha" class="ad-input">
                        </div>
                        <div class="ad-field c-2">
                            <label for="puntaje">Puntaje</label>
                            <input id="puntaje" type="number" step="0.01" min="0" name="puntaje" class="ad-input">
                        </div>
                        <div class="ad-field ad-field--end c-3">
                            <button type="submit" class="ad-btn ad-btn--navy ad-btn--block">
                                <i data-feather="plus"></i> Registrar
                            </button>
                        </div>
                        <div class="ad-field c-12">
                            <label for="observacion_examen">Observacion</label>
                            <input id="observacion_examen" type="text" name="observacion" class="ad-input" maxlength="500"
                                   placeholder="Opcional">
                        </div>
                    </form>
                @endif

                <div class="ad-table-wrap" style="border:1px solid var(--ad-line);border-radius:14px">
                    <table class="ad-table">
                        <thead>
                            <tr><th>Examen</th><th>Fecha</th><th>Puntaje</th><th>Resultado</th><th>Observacion</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($postulante->examenes as $ap)
                                @php $aprobado = $ap->aprobado(); @endphp
                                <tr>
                                    <td class="ad-strong">{{ $ap->examen?->titulo }}</td>
                                    <td>{{ $ap->fecha?->format('d/m/Y H:i') ?: '--' }}</td>
                                    <td class="ad-strong">{{ $ap->puntaje !== null ? $ap->puntaje : '--' }}</td>
                                    <td>
                                        @if ($aprobado === null)
                                            <span class="ad-pill ad-pill--mute">Sin calificar</span>
                                        @elseif ($aprobado)
                                            <span class="ad-pill ad-pill--ok">Alcanza el minimo</span>
                                        @else
                                            <span class="ad-pill ad-pill--warn">Bajo el minimo</span>
                                        @endif
                                    </td>
                                    <td class="ad-muted">{{ $ap->observacion ?: '--' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="ad-empty">Sin resultados de examen registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endcan

    {{-- Resultado (RF-18) --}}
    @can('admision.resultado')
        <section class="ad-card">
            <div class="ad-card__head">
                <div class="ad-card__title">
                    <span class="ad-card__icon ad-card__icon--gold"><i data-feather="send"></i></span>
                    <div>
                        <h2>Resultado de admision</h2>
                        <p>Se comunica al apoderado y cierra el expediente.</p>
                    </div>
                </div>
                <div class="ad-card__aside">
                    @if ($postulante->resultado_publicado_at)
                        <span class="ad-pill ad-pill--ok">Publicado</span>
                    @else
                        <span class="ad-pill ad-pill--warn">Sin publicar</span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admision.resultado', $postulante) }}">
                @csrf
                <div class="ad-card__body">
                    @if ($postulante->resultado_publicado_at)
                        <div class="ad-verdict">
                            <span class="ad-verdict__icon"><i data-feather="check-circle"></i></span>
                            <div>
                                <strong>{{ \App\Models\Postulante::ESTADOS[$postulante->estado] ?? $postulante->estado }}</strong>
                                <span>
                                    Publicado el {{ $postulante->resultado_publicado_at->format('d/m/Y H:i') }}@if ($postulante->resultado_canal), comunicado por {{ $postulante->resultado_canal }}@endif.
                                </span>
                            </div>
                        </div>
                    @endif
                    <div class="ad-grid">
                        <div class="ad-field c-3">
                            <label for="estado_resultado">Resultado</label>
                            <select id="estado_resultado" name="estado" class="ad-select" required>
                                <option value="admitido" @selected($postulante->estado === 'admitido')>Admitido</option>
                                <option value="no_admitido" @selected($postulante->estado === 'no_admitido')>No admitido</option>
                                <option value="desistido" @selected($postulante->estado === 'desistido')>Desistio</option>
                            </select>
                        </div>
                        <div class="ad-field c-4">
                            <label for="canal">Canal de comunicacion</label>
                            <input id="canal" type="text" name="canal" class="ad-input" maxlength="120"
                                   placeholder="Opcional: WhatsApp, correo, llamada..." value="{{ $postulante->resultado_canal }}">
                        </div>
                        <div class="ad-field c-5">
                            <label for="observacion_resultado">Observacion</label>
                            <input id="observacion_resultado" type="text" name="observacion" class="ad-input" maxlength="1000"
                                   placeholder="Opcional">
                        </div>
                    </div>
                </div>
                <div class="ad-card__foot">
                    <button type="submit" class="ad-btn ad-btn--gold">
                        <i data-feather="send"></i> Publicar resultado
                    </button>
                </div>
            </form>
        </section>
    @endcan
</div>
@endsection
