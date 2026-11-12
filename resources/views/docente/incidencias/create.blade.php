@extends('layouts.mantis')

@section('title', 'Registrar incidencia | Docente')
@section('page-title', 'Registrar incidencia')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-docente-filter mt-2">
        <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="jp-docente-filter-title">Secciones asignadas</h5>
                <p class="jp-docente-filter-sub">
                    Estas secciones corresponden al periodo acad&eacute;mico vigente.
                </p>
                <div class="jp-docente-filter-meta">
                    <span class="jp-docente-filter-pill">
                        <i data-feather="calendar"></i>
                        A&ntilde;o {{ $anioId ?? date('Y') }}
                    </span>
                    <span class="jp-docente-filter-pill is-accent">
                        <i data-feather="bookmark"></i>
                        {{ $periodoActivo?->nombre ?? 'Periodo actual' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('docente.incidencias.index') }}" class="jp-docente-history-btn">
                <i data-feather="clock"></i>
                Ver historial
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <strong>Revisa la informaci&oacute;n:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($seccionId)
        <div class="d-flex justify-content-end mt-2">
            <a href="{{ route('docente.incidencias.create') }}" class="btn btn-outline-secondary">
                Cambiar secci&oacute;n
            </a>
        </div>
    @endif

    @if (!$seccionId)
        <div class="row mt-4 g-4">
            @forelse ($secciones as $seccion)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('docente.incidencias.create', ['seccion_id' => $seccion->id]) }}"
                        class="text-decoration-none">
                        <div class="card jp-course-card h-100 border-0 shadow-sm hover-lift">
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
        @if ($estudiantes->isEmpty())
            <div class="alert alert-warning mt-3">
                No hay estudiantes matriculados en la secci&oacute;n seleccionada.
            </div>
        @endif

        <div class="card jp-search-card mt-3">
            <div class="card-body">
                <form method="POST" action="{{ route('docente.incidencias.store') }}">
                    @csrf
                    <input type="hidden" name="anio_escolar_id" value="{{ $anioId }}">
                    <input type="hidden" name="seccion_id" value="{{ $seccionId }}">

                    @php
                        $tipos = ['Disciplinaria', 'Conducta', 'Academica', 'Convivencia', 'Otro'];
                        $gravedades = ['Leve', 'Moderada', 'Grave', 'Critica'];
                    @endphp

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Curso</label>
                            <select name="curso_id" class="form-select" required>
                                <option value="">Selecciona un curso</option>
                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso->id }}" @selected((string) $cursoId === (string) $curso->id)>
                                        {{ $curso->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Alumno</label>
                            <select name="estudiante_id" class="form-select" required>
                                <option value="">Selecciona un alumno</option>
                                @foreach ($estudiantes as $estudiante)
                                    <option value="{{ $estudiante->id }}" @selected((string) old('estudiante_id') === (string) $estudiante->id)>
                                        {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Fecha reporte</label>
                            <input type="date" name="fecha_reporte" value="{{ old('fecha_reporte', date('Y-m-d')) }}"
                                class="form-control" required />
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Tipo de incidencia</label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Seleccione tipo</option>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>
                                        {{ $tipo === 'Academica' ? 'Acad&eacute;mica' : $tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Gravedad</label>
                            <select name="gravedad" class="form-select" required>
                                <option value="">Seleccione gravedad</option>
                                @foreach ($gravedades as $gravedad)
                                    <option value="{{ $gravedad }}" @selected(old('gravedad') === $gravedad)>
                                        {{ $gravedad === 'Critica' ? 'Cr&iacute;tica' : $gravedad }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripci&oacute;n</label>
                            <textarea name="descripcion" class="form-control" rows="4" required>{{ old('descripcion') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn jp-btn-incidencia" type="submit">
                            <i data-feather="save"></i>
                            Registrar incidencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
