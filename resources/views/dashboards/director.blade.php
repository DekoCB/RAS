@extends('layouts.mantis')

@section('title', 'Director | R.A.S')
@section('page-title', 'Dashboard Director')
@section('body-class', 'role-director')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('director.partials.sidebar')
@endsection

@section('content')
@php
    $anioActivo = \App\Models\AnioEscolar::where('activo', true)->first()
        ?? \App\Models\AnioEscolar::orderBy('nombre', 'desc')->first();
    $periodoActual = $anioActivo
        ? \App\Models\PeriodoAcademico::actualParaAnio($anioActivo->id)
        : null;

    $docentesCount = \App\Models\User::role('Docente')->count();
    $seccionesCount = $anioActivo
        ? \App\Models\Seccion::where('anio_escolar_id', $anioActivo->id)->count()
        : 0;
    $estudiantesCount = $anioActivo
        ? \App\Models\Matricula::where('anio_escolar_id', $anioActivo->id)
            ->when($periodoActual, fn($q) => $q->where('periodo_academico_id', $periodoActual->id))
            ->count()
        : 0;
    $cursosPlanificados = $anioActivo
        ? \Illuminate\Support\Facades\DB::table('seccion_curso')
            ->where('anio_escolar_id', $anioActivo->id)
            ->when($periodoActual, fn($q) => $q->where('periodo_academico_id', $periodoActual->id))
            ->distinct('curso_id')->count('curso_id')
        : 0;
    $cierresRegistrados = $anioActivo
        ? \App\Models\NotaCierre::where('anio_escolar_id', $anioActivo->id)
            ->when($periodoActual, fn($q) => $q->where('periodo_academico_id', $periodoActual->id))
            ->count()
        : 0;
    $asistenciasHoy = $anioActivo
        ? \App\Models\Asistencia::where('anio_escolar_id', $anioActivo->id)
            ->whereDate('fecha', \Illuminate\Support\Carbon::today())->count()
        : 0;
    $asistenciaDocenteEntradasHoy = \App\Models\AsistenciaDocente::query()
        ->when($anioActivo, fn($q) => $q->where('anio_escolar_id', $anioActivo->id))
        ->whereDate('fecha', \Illuminate\Support\Carbon::today())
        ->where('tipo', 'ENTRADA')
        ->count();
    $asistenciaDocenteSalidasHoy = \App\Models\AsistenciaDocente::query()
        ->when($anioActivo, fn($q) => $q->where('anio_escolar_id', $anioActivo->id))
        ->whereDate('fecha', \Illuminate\Support\Carbon::today())
        ->where('tipo', 'SALIDA')
        ->count();
    $incidenciasAbiertas = $anioActivo
        ? \App\Models\Incidencia::where('anio_escolar_id', $anioActivo->id)
            ->where('estado', 'abierta')->count()
        : 0;
    $cursosSinDocente = $anioActivo
        ? \Illuminate\Support\Facades\DB::table('seccion_curso')
            ->where('anio_escolar_id', $anioActivo->id)
            ->when($periodoActual, fn($q) => $q->where('periodo_academico_id', $periodoActual->id))
            ->whereNull('docente_id')->count()
        : 0;
    $ultimosCierres = \App\Models\NotaCierre::with('curso')
        ->when($anioActivo, fn($q) => $q->where('anio_escolar_id', $anioActivo->id))
        ->orderByDesc('fecha_cierre')->orderByDesc('updated_at')
        ->limit(5)->get();
@endphp

{{-- ══════════ HERO BANNER ══════════ --}}
<div class="dir-hero mb-4">
    <div class="dir-hero__inner">
        <div class="dir-hero__left">
            <div>
                <p class="dir-hero__school">R.A.S</p>
                <h2 class="dir-hero__welcome">
                    Bienvenido, {{ auth()->user()->name ?? 'Director' }}
                </h2>
                <p class="dir-hero__sub">
                    Vista ejecutiva del a&ntilde;o escolar y seguimiento acad&eacute;mico.
                </p>
            </div>
        </div>
        <div class="dir-hero__right">
            <div class="dir-hero__pills">
                <span class="dir-hero__pill is-light">
                    <i data-feather="calendar"></i>
                    A&ntilde;o {{ $anioActivo?->nombre ?? date('Y') }}
                </span>
                <span class="dir-hero__pill is-light">
                    <i data-feather="bookmark"></i>
                    {{ $periodoActual?->codigo ?? 'Periodo' }}
                    {{ $periodoActual?->nombre ? '· ' . $periodoActual->nombre : '' }}
                </span>
            </div>
            <span class="dir-hero__pill" style="align-self: flex-end;">
                <i data-feather="activity"></i>
                Asistencias hoy:&nbsp;<strong>{{ $asistenciasHoy }}</strong>
            </span>
        </div>
    </div>
</div>

{{-- ══════════ KPI CARDS ══════════ --}}
<div class="dir-kpi-grid mb-4">

    <div class="dir-kpi">
        <div class="dir-kpi__icon">
            <i data-feather="layers"></i>
        </div>
        <div>
            <div class="dir-kpi__label">Secciones</div>
            <div class="dir-kpi__num">{{ $seccionesCount }}</div>
            <span class="dir-kpi__chip">
                <i data-feather="calendar"></i> A&ntilde;o activo
            </span>
        </div>
    </div>

    <div class="dir-kpi">
        <div class="dir-kpi__icon is-orange">
            <i data-feather="user-check"></i>
        </div>
        <div>
            <div class="dir-kpi__label">Estudiantes</div>
            <div class="dir-kpi__num">{{ $estudiantesCount }}</div>
            <span class="dir-kpi__chip">
                <i data-feather="users"></i> Matriculados
            </span>
        </div>
    </div>

    <div class="dir-kpi">
        <div class="dir-kpi__icon is-amber">
            <i data-feather="book-open"></i>
        </div>
        <div>
            <div class="dir-kpi__label">Cursos</div>
            <div class="dir-kpi__num">{{ $cursosPlanificados }}</div>
            <span class="dir-kpi__chip">
                <i data-feather="book"></i> Planificados
            </span>
        </div>
    </div>

    <div class="dir-kpi">
        <div class="dir-kpi__icon is-blue">
            <i data-feather="clipboard"></i>
        </div>
        <div>
            <div class="dir-kpi__label">Cierres de notas</div>
            <div class="dir-kpi__num">{{ $cierresRegistrados }}</div>
            <span class="dir-kpi__chip">
                <i data-feather="check-square"></i> Registrados
            </span>
        </div>
    </div>

</div>

{{-- ══════════ ACCIONES RÁPIDAS ══════════ --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="dir-card-icon"><i data-feather="zap"></i></span>
            <div>
                <p class="dir-card-title">Acciones r&aacute;pidas</p>
                <p class="dir-card-sub">Accesos directos para gesti&oacute;n acad&eacute;mica.</p>
            </div>
        </div>
        <a href="{{ route('director.horarios.lista') }}" class="dir-card-link">
            <i data-feather="clock"></i> Horarios
        </a>
    </div>
    <div class="card-body">
        <div class="dir-actions-grid">

            <div class="dir-action">
                <div class="dir-action__top">
                    <div class="dir-action__icon is-green">
                        <i data-feather="user-plus"></i>
                    </div>
                    <a href="{{ route('director.personal.index') }}" class="dir-action__cta" style="background: linear-gradient(135deg,#0f766e,#14b8a6); box-shadow: 0 6px 14px rgba(20,184,166,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </a>
                </div>
                <div>
                    <p class="dir-action__title">Personal</p>
                    <p class="dir-action__desc">Registra usuarios para Docente y Secretaria.</p>
                </div>
            </div>

            <div class="dir-action">
                <div class="dir-action__top">
                    <div class="dir-action__icon is-blue">
                        <i data-feather="clock"></i>
                    </div>
                    <a href="{{ route('director.asistencias-docentes.index') }}" class="dir-action__cta" style="background: linear-gradient(135deg,#162166,#213197); box-shadow: 0 6px 14px rgba(33,49,151,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </a>
                </div>
                <div>
                    <p class="dir-action__title">Asistencias docentes</p>
                    <p class="dir-action__desc">
                        Entradas hoy: {{ $asistenciaDocenteEntradasHoy }} | Salidas hoy: {{ $asistenciaDocenteSalidasHoy }}.
                    </p>
                </div>
            </div>

            <div class="dir-action">
                <div class="dir-action__top">
                    <div class="dir-action__icon">
                        <i data-feather="check-square"></i>
                    </div>
                    <a href="{{ route('director.notas.cierre') }}" class="dir-action__cta">
                        <i data-feather="arrow-right"></i> Abrir
                    </a>
                </div>
                <div>
                    <p class="dir-action__title">Cierre de notas</p>
                    <p class="dir-action__desc">Controla y gestiona los cierres por periodo y curso.</p>
                </div>
            </div>

            <div class="dir-action">
                <div class="dir-action__top">
                    <div class="dir-action__icon is-green">
                        <i data-feather="users"></i>
                    </div>
                    <a href="{{ route('director.docentes.asignaciones') }}" class="dir-action__cta" style="background: linear-gradient(135deg,#15803d,#22c55e); box-shadow: 0 6px 14px rgba(34,197,94,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </a>
                </div>
                <div>
                    <p class="dir-action__title">Asignar docentes</p>
                    <p class="dir-action__desc">Define responsables por curso y secci&oacute;n activa.</p>
                </div>
            </div>

            <div class="dir-action">
                <div class="dir-action__top">
                    <div class="dir-action__icon is-amber">
                        <i data-feather="calendar"></i>
                    </div>
                    <a href="{{ route('director.horarios.index') }}" class="dir-action__cta" style="background: linear-gradient(135deg,#1b287b,#22329c); box-shadow: 0 6px 14px rgba(34, 50, 156,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </a>
                </div>
                <div>
                    <p class="dir-action__title">Horario escolar</p>
                    <p class="dir-action__desc">Gestiona bloques horarios y programaci&oacute;n semanal.</p>
                </div>
            </div>

            <div class="dir-action">
                <div class="dir-action__top">
                    <div class="dir-action__icon is-blue">
                        <i data-feather="sliders"></i>
                    </div>
                    <a href="{{ route('gestionar-secciones.index') }}" class="dir-action__cta" style="background: linear-gradient(135deg,#1f2e8d,#213197); box-shadow: 0 6px 14px rgba(33,49,151,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </a>
                </div>
                <div>
                    <p class="dir-action__title">Gestionar secciones</p>
                    <p class="dir-action__desc">Administra niveles, grados, secciones y vacantes.</p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════ CIERRES + ALERTAS ══════════ --}}
<div class="row g-4">

    {{-- Últimos cierres --}}
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="dir-card-icon"><i data-feather="file-text"></i></span>
                    <div>
                        <p class="dir-card-title">&Uacute;ltimos cierres de notas</p>
                        <p class="dir-card-sub">Historial reciente del periodo activo.</p>
                    </div>
                </div>
                <a href="{{ route('director.notas.cierre') }}" class="dir-card-link">
                    <i data-feather="external-link"></i> Ver todos
                </a>
            </div>
            <div class="card-body">
                <div class="dir-closures">
                    @forelse ($ultimosCierres as $cierre)
                        <div class="dir-closure-item">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="dir-closure-date">
                                    <i data-feather="calendar"></i>
                                    {{ optional($cierre->fecha_cierre)->format('d M Y')
                                        ?? $cierre->updated_at?->format('d M Y') }}
                                </span>
                                <span class="dir-closure-badge">
                                    <i data-feather="check-circle"></i> Cerrado
                                </span>
                            </div>
                            <span class="dir-closure-course">
                                {{ $cierre->curso?->nombre ?? 'Todos los cursos' }}
                            </span>
                        </div>
                    @empty
                        <div class="dir-closure-empty">
                            <i data-feather="inbox"></i>
                            No hay cierres registrados a&uacute;n.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas académicas --}}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-3">
                <span class="dir-card-icon"><i data-feather="alert-triangle"></i></span>
                <div>
                    <p class="dir-card-title">Alertas acad&eacute;micas</p>
                    <p class="dir-card-sub">Estado operativo del periodo.</p>
                </div>
            </div>
            <div class="card-body d-flex flex-column gap-3">

                <div class="dir-alert-chip is-danger w-100 justify-content-between">
                    <span class="d-flex align-items-center gap-2">
                        <i data-feather="alert-circle"></i>
                        Cursos sin docente
                    </span>
                    <strong class="fs-5">{{ $cursosSinDocente }}</strong>
                </div>

                <div class="dir-alert-chip is-warning w-100 justify-content-between">
                    <span class="d-flex align-items-center gap-2">
                        <i data-feather="alert-triangle"></i>
                        Incidencias abiertas
                    </span>
                    <strong class="fs-5">{{ $incidenciasAbiertas }}</strong>
                </div>

                <div class="dir-alert-chip is-ok w-100 justify-content-between">
                    <span class="d-flex align-items-center gap-2">
                        <i data-feather="user-check"></i>
                        Docentes activos
                    </span>
                    <strong class="fs-5">{{ $docentesCount }}</strong>
                </div>

                @if ($cursosSinDocente > 0)
                <a href="{{ route('director.docentes.asignaciones') }}"
                   class="dir-action__cta mt-auto text-center justify-content-center"
                   style="text-decoration:none;">
                    <i data-feather="users"></i>
                    Asignar docentes pendientes
                </a>
                @endif

            </div>
        </div>
    </div>

</div>
@endsection
