@extends('layouts.mantis')

@section('title', 'Historial de asistencias | Docente')
@section('page-title', 'Historial de asistencias')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-count-card mt-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="jp-pill">Historial</div>
                    <h5 class="mb-1">Asistencias registradas</h5>
                    @if ($seccionId)
                        <p class="mb-0 opacity-75">
                            Selecciona un d&iacute;a para revisar las asistencias guardadas.
                        </p>
                    @else
                        <p class="mb-0 opacity-75">
                            Selecciona una secci&oacute;n para ver los d&iacute;as registrados.
                        </p>
                    @endif
                </div>
                <a href="{{ $seccionId ? route('docente.asistencias.historial') : route('docente.asistencias.index') }}"
                    class="btn btn-outline-light">
                    Volver
                </a>
            </div>
        </div>
    </div>

    @if (!$seccionId)
        <div class="row mt-4 g-4">
            @forelse ($seccionesAsignadas as $seccion)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('docente.asistencias.historial', ['seccion_id' => $seccion->id]) }}"
                        class="text-decoration-none">
                        <div
                            class="card jp-course-card h-100 border-0 shadow-sm hover-lift {{ (string) $seccionId === (string) $seccion->id ? 'border border-2 border-danger' : '' }}">
                            <div class="card-body p-4 text-center">
                                <div class="jp-check-icon-lg mb-3 mx-auto bg-soft-primary text-primary">
                                    <i data-feather="users" style="width: 32px; height: 32px;"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $seccion->grado->nombre }} - {{ $seccion->nombre }}</h5>
                                <p class="text-muted mb-0 small">Nivel: {{ $seccion->grado->nivel ?? 'General' }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i data-feather="info" class="me-2"></i>
                        No tienes secciones asignadas.
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    @if ($seccionId)
        <div class="card jp-search-card mt-3">
            <div class="card-body">
                <form method="GET" action="{{ route('docente.asistencias.historial') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-6">
                            <label class="form-label">Secci&oacute;n seleccionada</label>
                            <select name="seccion_id" class="form-select">
                                <option value="">Seleccione una secci&oacute;n</option>
                                @foreach ($seccionesAsignadas as $seccion)
                                    <option value="{{ $seccion->id }}" @selected((string) $seccionId === (string) $seccion->id)>
                                        {{ $seccion->grado?->nombre ?? '-' }} - {{ $seccion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">D&iacute;a registrado</label>
                            <select name="fecha" class="form-select">
                                <option value="">Seleccione un d&iacute;a</option>
                                @foreach ($fechasDisponibles as $fechaDisponible)
                                    @php
                                        $fechaMostrar = \Carbon\Carbon::parse($fechaDisponible)->format('d-m-Y');
                                    @endphp
                                    <option value="{{ $fechaDisponible }}" @selected($fecha === $fechaDisponible)>
                                        {{ $fechaMostrar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 d-grid">
                            <button class="btn jp-btn-search" type="submit">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            Selecciona una secci&oacute;n para elegir el d&iacute;a registrado.
        </div>
    @endif

    @if ($seccionId && $fecha)
        <div class="card jp-soft-card mt-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Listado de asistencias</h5>
                <span class="badge jp-badge-outline">
                    A&ntilde;o {{ $anio?->nombre ?? date('Y') }}
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover jp-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Secci&oacute;n</th>
                                <th>Estudiante</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($asistencias as $asistencia)
                                @php
                                    $estado = strtolower($asistencia->estado ?? 'presente');
                                    $estadoNormalizado = match ($estado) {
                                        'tardanza' => 'tarde',
                                        'falta' => 'ausente',
                                        default => $estado,
                                    };
                                    $pill = match ($estadoNormalizado) {
                                        'presente' => 'jp-attendance-pill--presente',
                                        'tarde' => 'jp-attendance-pill--tarde',
                                        'ausente' => 'jp-attendance-pill--ausente',
                                        'justificado' => 'jp-attendance-pill--justificado',
                                        default => 'jp-attendance-pill--neutral',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d-m-Y') }}</td>
                                    <td>
                                        {{ $asistencia->seccion?->grado?->nombre ?? '-' }} - {{ $asistencia->seccion?->nombre ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $asistencia->estudiante?->apellidos ?? '-' }}, {{ $asistencia->estudiante?->nombres ?? '' }}
                                    </td>
                                    <td>
                                        <span class="jp-attendance-pill {{ $pill }}">
                                            {{ ucfirst($estadoNormalizado) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No hay asistencias registradas para ese d&iacute;a.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 jp-table-pagination">
                    {{ $asistencias->links() }}
                </div>
            </div>
        </div>
    @elseif ($seccionId)
        <div class="alert alert-info mt-3">
            Selecciona un d&iacute;a registrado para ver el listado de asistencias.
        </div>
    @endif
@endsection
