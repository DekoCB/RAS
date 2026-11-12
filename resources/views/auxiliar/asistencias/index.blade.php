@extends('layouts.mantis')

@section('title', 'Asistencias | Auxiliar')
@section('page-title', 'Asistencias')
@section('body-class', 'role-auxiliar')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/auxiliar-dashboard.css') }}?v={{ time() }}">
@endpush

@section('sidebar')
    @include('auxiliar.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-docente-filter mt-2">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="jp-docente-filter-title">Todas las secciones</h5>
                <p class="jp-docente-filter-sub">
                    Selecciona una secci&oacute;n para registrar asistencia.
                </p>
                <div class="jp-docente-filter-meta">
                    <span class="jp-docente-filter-pill">
                        <i data-feather="calendar"></i>
                        A&ntilde;o {{ $anio?->nombre ?? date('Y') }}
                    </span>
                    <span class="jp-docente-filter-pill is-accent">
                        <i data-feather="bookmark"></i>
                        {{ $periodoActual?->nombre ?? 'Periodo actual' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('auxiliar.asistencias.historial') }}" class="jp-docente-history-btn">
                <i data-feather="clock"></i>
                Ver historial
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success jp-incidencia-alert mt-3">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <div class="row mt-4 g-4">
        @forelse ($seccionesAsignadas as $seccion)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('auxiliar.asistencias.create', ['seccion_id' => $seccion->id]) }}"
                    class="text-decoration-none">
                    <div class="card jp-course-card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="jp-check-icon-lg mb-3 mx-auto bg-soft-primary text-primary">
                                <i data-feather="users" style="width:32px;height:32px;"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">
                                {{ $seccion->grado->nombre }} - {{ $seccion->nombre }}
                            </h5>
                            <p class="text-muted mb-0 small">Nivel: {{ $seccion->grado->nivel ?? 'General' }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i data-feather="info" class="me-2"></i>
                    No hay secciones registradas.
                </div>
            </div>
        @endforelse
    </div>
@endsection
