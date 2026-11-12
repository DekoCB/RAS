@extends('layouts.mantis')

@section('title', 'Docente | R.A.S')
@section('page-title', 'Dashboard Docente')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')

{{-- ══════════ HERO BANNER ══════════ --}}
<div class="doc-hero mb-4">
    <div class="doc-hero__inner">
        <div class="doc-hero__left">
            <div>
                <p class="doc-hero__school">R.A.S</p>
                <h2 class="doc-hero__welcome">
                    Hola, {{ auth()->user()->name ?? 'Docente' }}
                </h2>
                <p class="doc-hero__sub">
                    Gestiona tus secciones, asistencias, notas e incidencias.
                </p>
            </div>
        </div>
        <div class="doc-hero__right">
            <div class="doc-hero__pills">
                <span class="doc-hero__pill is-light">
                    <i data-feather="calendar"></i>
                    Hoy: {{ \Illuminate\Support\Carbon::now()->isoFormat('dddd, D MMM') }}
                </span>
                <span class="doc-hero__pill is-light">
                    <i data-feather="layers"></i>
                    {{ $resumen['secciones'] }} secci&oacute;n{{ $resumen['secciones'] != 1 ? 'es' : '' }}
                </span>
            </div>
            <span class="doc-hero__pill" style="align-self:flex-end;">
                <i data-feather="users"></i>
                {{ $resumen['estudiantes'] }} estudiantes a cargo
            </span>
        </div>
    </div>
</div>

{{-- ══════════ KPI CARDS ══════════ --}}
<div class="doc-kpi-grid mb-4">

    <div class="doc-kpi">
        <div class="doc-kpi__icon">
            <i data-feather="layers"></i>
        </div>
        <div>
            <div class="doc-kpi__label">Secciones</div>
            <div class="doc-kpi__num">{{ $resumen['secciones'] }}</div>
            <span class="doc-kpi__chip">
                <i data-feather="map-pin"></i> Asignadas
            </span>
        </div>
    </div>

    <div class="doc-kpi">
        <div class="doc-kpi__icon is-green">
            <i data-feather="users"></i>
        </div>
        <div>
            <div class="doc-kpi__label">Estudiantes</div>
            <div class="doc-kpi__num">{{ $resumen['estudiantes'] }}</div>
            <span class="doc-kpi__chip">
                <i data-feather="user-check"></i> A tu cargo
            </span>
        </div>
    </div>

    <div class="doc-kpi">
        <div class="doc-kpi__icon is-amber">
            <i data-feather="calendar"></i>
        </div>
        <div>
            <div class="doc-kpi__label">Asistencias hoy</div>
            <div class="doc-kpi__num">{{ $resumen['asistencias_hoy'] }}</div>
            <span class="doc-kpi__chip">
                <i data-feather="clock"></i> Del d&iacute;a
            </span>
        </div>
    </div>

    <div class="doc-kpi">
        <div class="doc-kpi__icon is-red">
            <i data-feather="edit-3"></i>
        </div>
        <div>
            <div class="doc-kpi__label">Notas registradas</div>
            <div class="doc-kpi__num">{{ $resumen['notas'] }}</div>
            <span class="doc-kpi__chip">
                <i data-feather="book"></i> Bimestre activo
            </span>
        </div>
    </div>

</div>

{{-- ══════════ ACCIONES RÁPIDAS ══════════ --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="doc-card-icon"><i data-feather="zap"></i></span>
            <div>
                <p class="doc-card-title">Acciones r&aacute;pidas</p>
                <p class="doc-card-sub">Accesos directos para tu jornada docente.</p>
            </div>
        </div>
        <a href="{{ route('docente.asistencias.historial') }}" class="doc-card-link">
            <i data-feather="clock"></i> Historial
        </a>
    </div>
    <div class="card-body">
        <div class="doc-actions-grid">

            <a href="{{ route('docente.asistencias.create') }}" class="doc-action">
                <div class="doc-action__top">
                    <div class="doc-action__icon">
                        <i data-feather="calendar"></i>
                    </div>
                    <span class="doc-action__cta">
                        <i data-feather="arrow-right"></i> Abrir
                    </span>
                </div>
                <div>
                    <p class="doc-action__title">Registrar asistencia</p>
                    <p class="doc-action__desc">Marca presente, tarde o falta por secci&oacute;n.</p>
                </div>
            </a>

            <a href="{{ route('docente.notas.create') }}" class="doc-action">
                <div class="doc-action__top">
                    <div class="doc-action__icon is-green">
                        <i data-feather="edit-3"></i>
                    </div>
                    <span class="doc-action__cta" style="background:linear-gradient(135deg,#15803d,#22c55e);box-shadow:0 6px 14px rgba(34,197,94,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </span>
                </div>
                <div>
                    <p class="doc-action__title">Registrar nota</p>
                    <p class="doc-action__desc">Carga evaluaciones y promedio bimestral.</p>
                </div>
            </a>

            <a href="{{ route('docente.incidencias.create') }}" class="doc-action">
                <div class="doc-action__top">
                    <div class="doc-action__icon is-amber">
                        <i data-feather="alert-triangle"></i>
                    </div>
                    <span class="doc-action__cta" style="background:linear-gradient(135deg,#1b287b,#22329c);box-shadow:0 6px 14px rgba(34, 50, 156,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </span>
                </div>
                <div>
                    <p class="doc-action__title">Reportar incidencia</p>
                    <p class="doc-action__desc">Conducta, disciplina o evento relevante.</p>
                </div>
            </a>

        </div>
    </div>
</div>

{{-- ══════════ TABLA NOTAS + LISTAS ══════════ --}}
<div class="row g-4">

    {{-- Últimas notas --}}
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="doc-card-icon"><i data-feather="edit-3"></i></span>
                    <div>
                        <p class="doc-card-title">&Uacute;ltimas notas registradas</p>
                        <p class="doc-card-sub">Resumen de los &uacute;ltimos registros.</p>
                    </div>
                </div>
                <a href="{{ route('docente.notas.index') }}" class="doc-card-link">
                    <i data-feather="external-link"></i> Ver todas
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover jp-docente-table mb-0">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Estudiante</th>
                                <th class="text-center">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notasRegistradas as $nota)
                                <tr>
                                    <td>{{ $nota->curso->nombre ?? 'Curso' }}</td>
                                    <td>{{ $nota->estudiante->apellidos ?? 'Sin estudiante' }}</td>
                                    <td class="text-center">
                                        <span class="doc-pill doc-pill--ok">{{ $nota->valor ?? '--' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Sin notas registradas por ahora.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Asistencias + Incidencias --}}
    <div class="col-xl-5 d-flex flex-column gap-4">

        {{-- Últimas asistencias --}}
        <div class="card flex-fill">
            <div class="card-header d-flex align-items-center gap-3">
                <span class="doc-card-icon"><i data-feather="calendar"></i></span>
                <div>
                    <p class="doc-card-title">&Uacute;ltimas asistencias</p>
                    <p class="doc-card-sub">Registros recientes.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="doc-list">
                    @forelse ($asistenciasRegistradas as $asistencia)
                        @php
                            $estado = strtolower($asistencia->estado ?? '');
                            $pill = match($estado) {
                                'presente' => 'doc-pill--ok',
                                'tarde'    => 'doc-pill--warn',
                                'falta', 'ausente' => 'doc-pill--danger',
                                default    => 'doc-pill--neutral',
                            };
                            $fecha = $asistencia->fecha
                                ? \Illuminate\Support\Carbon::parse($asistencia->fecha)->format('d M')
                                : '--';
                        @endphp
                        <div class="doc-list-item">
                            <div class="doc-list-meta">
                                <span class="doc-list-date">
                                    <i data-feather="calendar"></i>{{ $fecha }}
                                </span>
                                <span class="doc-pill {{ $pill }}">
                                    {{ $estado ? ucfirst($estado) : 'Sin estado' }}
                                </span>
                            </div>
                            <span class="doc-tag">{{ $asistencia->estudiante->apellidos ?? 'N/A' }}</span>
                        </div>
                    @empty
                        <div class="doc-empty">
                            <i data-feather="inbox"></i>
                            Sin asistencias registradas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Últimas incidencias --}}
        <div class="card flex-fill">
            <div class="card-header d-flex align-items-center gap-3">
                <span class="doc-card-icon" style="background:linear-gradient(135deg,#1b287b,#22329c);"><i data-feather="alert-triangle"></i></span>
                <div>
                    <p class="doc-card-title">&Uacute;ltimas incidencias</p>
                    <p class="doc-card-sub">Reportadas recientemente.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="doc-list">
                    @forelse ($incidenciasReportadas as $incidencia)
                        @php
                            $fechaI = $incidencia->fecha_reporte
                                ? \Illuminate\Support\Carbon::parse($incidencia->fecha_reporte)->format('d M')
                                : '--';
                            $tipo = $incidencia->tipo ? ucfirst(strtolower($incidencia->tipo)) : 'Incidencia';
                        @endphp
                        <div class="doc-list-item">
                            <div class="doc-list-meta">
                                <span class="doc-list-date">
                                    <i data-feather="calendar"></i>{{ $fechaI }}
                                </span>
                                <span class="doc-pill doc-pill--warn">{{ $tipo }}</span>
                            </div>
                            <span class="doc-tag doc-tag--warning">{{ $incidencia->estudiante->apellidos ?? 'N/A' }}</span>
                        </div>
                    @empty
                        <div class="doc-empty">
                            <i data-feather="check-circle"></i>
                            Sin incidencias reportadas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
