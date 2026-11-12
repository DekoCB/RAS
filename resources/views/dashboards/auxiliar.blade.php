@extends('layouts.mantis')

@section('title', 'Auxiliar | R.A.S')
@section('page-title', 'Dashboard Auxiliar')
@section('body-class', 'role-auxiliar')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/auxiliar-dashboard.css') }}?v={{ time() }}">
@endpush

@section('sidebar')
    @include('auxiliar.partials.sidebar')
@endsection

@section('content')

{{-- ══════════ HERO BANNER ══════════ --}}
<div class="aux-hero mb-4">
    <div class="aux-hero__left">
        <div>
            <p class="aux-hero__school">R.A.S</p>
            <h2 class="aux-hero__welcome">
                Hola, {{ auth()->user()->name ?? 'Auxiliar' }}
            </h2>
            <p class="aux-hero__sub">
                Registra asistencias de todos los estudiantes del colegio.
            </p>
        </div>
    </div>
    <div class="aux-hero__right">
        <div class="aux-hero__pills">
            <span class="aux-hero__pill is-light">
                <i data-feather="calendar"></i>
                Hoy: {{ \Illuminate\Support\Carbon::now()->isoFormat('dddd, D MMM') }}
            </span>
            <span class="aux-hero__pill is-light">
                <i data-feather="layers"></i>
                {{ $totalSecciones }} secci&oacute;n{{ $totalSecciones != 1 ? 'es' : '' }}
            </span>
        </div>
        <span class="aux-hero__pill" style="align-self:flex-end;">
            <i data-feather="check-square"></i>
            {{ $asistenciasHoy }} asistencias hoy
        </span>
    </div>
</div>

{{-- ══════════ KPI CARDS ══════════ --}}
<div class="aux-kpi-grid mb-4">

    <div class="aux-kpi">
        <div class="aux-kpi__icon">
            <i data-feather="layers"></i>
        </div>
        <div>
            <div class="aux-kpi__label">Secciones</div>
            <div class="aux-kpi__num">{{ $totalSecciones }}</div>
            <span class="aux-kpi__chip">
                <i data-feather="map-pin"></i> Total
            </span>
        </div>
    </div>

    <div class="aux-kpi">
        <div class="aux-kpi__icon is-cyan">
            <i data-feather="check-square"></i>
        </div>
        <div>
            <div class="aux-kpi__label">Asistencias hoy</div>
            <div class="aux-kpi__num">{{ $asistenciasHoy }}</div>
            <span class="aux-kpi__chip">
                <i data-feather="clock"></i> Del d&iacute;a
            </span>
        </div>
    </div>

    <div class="aux-kpi">
        <div class="aux-kpi__icon is-green">
            <i data-feather="calendar"></i>
        </div>
        <div>
            <div class="aux-kpi__label">A&ntilde;o Escolar</div>
            <div class="aux-kpi__num" style="font-size:1.3rem;">{{ $anio?->nombre ?? date('Y') }}</div>
            <span class="aux-kpi__chip">
                <i data-feather="bookmark"></i> Activo
            </span>
        </div>
    </div>

</div>

{{-- ══════════ ACCIONES RÁPIDAS ══════════ --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="aux-card-icon"><i data-feather="zap"></i></span>
            <div>
                <p class="aux-card-title">Acciones r&aacute;pidas</p>
                <p class="aux-card-sub">Registra asistencia por secci&oacute;n o mediante QR.</p>
            </div>
        </div>
        <a href="{{ route('auxiliar.asistencias.historial') }}" class="aux-card-link">
            <i data-feather="clock"></i> Historial
        </a>
    </div>
    <div class="card-body">
        <div class="aux-actions-grid">

            <a href="{{ route('auxiliar.asistencias.create') }}" class="aux-action">
                <div class="aux-action__top">
                    <div class="aux-action__icon">
                        <i data-feather="calendar"></i>
                    </div>
                    <span class="aux-action__cta">
                        <i data-feather="arrow-right"></i> Abrir
                    </span>
                </div>
                <div>
                    <p class="aux-action__title">Registrar asistencia</p>
                    <p class="aux-action__desc">Marca presente, tarde o falta por secci&oacute;n.</p>
                </div>
            </a>

            <a href="{{ route('auxiliar.asistencias.qr') }}" class="aux-action">
                <div class="aux-action__top">
                    <div class="aux-action__icon" style="background:linear-gradient(135deg,#1d2a82,#213196);box-shadow:0 6px 14px rgba(29, 42, 130,.28);">
                        <i data-feather="camera"></i>
                    </div>
                    <span class="aux-action__cta" style="background:linear-gradient(135deg,#1d2a82,#213196);box-shadow:0 6px 14px rgba(29, 42, 130,.25);">
                        <i data-feather="arrow-right"></i> Abrir
                    </span>
                </div>
                <div>
                    <p class="aux-action__title">Asistencia QR</p>
                    <p class="aux-action__desc">Escanea el carnet del estudiante con la c&aacute;mara.</p>
                </div>
            </a>

            <a href="{{ route('auxiliar.asistencias.index') }}" class="aux-action">
                <div class="aux-action__top">
                    <div class="aux-action__icon" style="background:linear-gradient(135deg,#15803d,#22c55e);box-shadow:0 6px 14px rgba(34,197,94,.28);">
                        <i data-feather="list"></i>
                    </div>
                    <span class="aux-action__cta" style="background:linear-gradient(135deg,#15803d,#22c55e);box-shadow:0 6px 14px rgba(34,197,94,.25);">
                        <i data-feather="arrow-right"></i> Ver
                    </span>
                </div>
                <div>
                    <p class="aux-action__title">Ver secciones</p>
                    <p class="aux-action__desc">Lista de todas las secciones disponibles.</p>
                </div>
            </a>

            <a href="{{ route('auxiliar.carnet.show') }}" class="aux-action">
                <div class="aux-action__top">
                    <div class="aux-action__icon">
                        <i data-feather="credit-card"></i>
                    </div>
                    <span class="aux-action__cta">
                        <i data-feather="arrow-right"></i> Ver
                    </span>
                </div>
                <div>
                    <p class="aux-action__title">Mi Carnet</p>
                    <p class="aux-action__desc">Ver e imprimir tu carnet con c&oacute;digo QR.</p>
                </div>
            </a>

        </div>
    </div>
</div>

{{-- ══════════ ÚLTIMAS ASISTENCIAS ══════════ --}}
<div class="card">
    <div class="card-header d-flex align-items-center gap-3">
        <span class="aux-card-icon"><i data-feather="check-square"></i></span>
        <div>
            <p class="aux-card-title">&Uacute;ltimas asistencias registradas</p>
            <p class="aux-card-sub">Los registros m&aacute;s recientes del d&iacute;a.</p>
        </div>
    </div>
    <div class="card-body">
        <div class="aux-list">
            @forelse ($ultimasAsistencias as $asistencia)
                @php
                    $estado = strtolower($asistencia->estado ?? '');
                    $pill = match($estado) {
                        'presente' => 'aux-pill--ok',
                        'tarde'    => 'aux-pill--warn',
                        'falta', 'ausente' => 'aux-pill--danger',
                        default    => 'aux-pill--neutral',
                    };
                    $fecha = $asistencia->fecha
                        ? \Illuminate\Support\Carbon::parse($asistencia->fecha)->format('d M')
                        : '--';
                @endphp
                <div class="aux-list-item">
                    <div class="aux-list-meta">
                        <span class="aux-list-date">
                            <i data-feather="calendar"></i>{{ $fecha }}
                        </span>
                        <span class="aux-pill {{ $pill }}">
                            {{ $estado ? ucfirst($estado) : 'Sin estado' }}
                        </span>
                        <span class="aux-tag">
                            {{ $asistencia->seccion?->grado?->nombre }} {{ $asistencia->seccion?->nombre }}
                        </span>
                    </div>
                    <span class="aux-tag">{{ $asistencia->estudiante?->apellidos ?? 'N/A' }}</span>
                </div>
            @empty
                <div class="aux-empty">
                    <i data-feather="inbox"></i>
                    Sin asistencias registradas hoy.
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
