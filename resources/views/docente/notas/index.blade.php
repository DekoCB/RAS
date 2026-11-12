@extends('layouts.mantis')

@section('title', 'Historial de notas | Docente')
@section('page-title', 'Historial de notas')
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
                    <h5 class="mb-1">Notas registradas</h5>
                    <p class="mb-0 opacity-75">
                        Filtra por secci&oacute;n, curso y evaluaci&oacute;n para revisar las notas.
                    </p>
                </div>
                <a href="{{ route('docente.notas.create') }}" class="btn jp-btn-search">
                    Registrar notas
                </a>
            </div>
        </div>
    </div>

    <div class="card jp-search-card mt-3">
        <div class="card-body">
            <form method="GET" action="{{ route('docente.notas.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3">
                        <label class="form-label">A&ntilde;o escolar</label>
                        <select name="anio_escolar_id" class="form-select">
                            @foreach ($anios as $anio)
                                <option value="{{ $anio->id }}" @selected((string) $anioId === (string) $anio->id)>
                                    {{ $anio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Periodo acad&eacute;mico</label>
                        <select name="periodo_academico_id" class="form-select">
                            @foreach ($periodosAcademicos as $periodoAcademico)
                                <option value="{{ $periodoAcademico->id }}"
                                    @selected((string) $periodoAcademicoId === (string) $periodoAcademico->id)>
                                    {{ $periodoAcademico->codigo }} - {{ $periodoAcademico->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Secci&oacute;n</label>
                        <select name="seccion_id" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($secciones as $seccion)
                                <option value="{{ $seccion->id }}" @selected((string) $seccionId === (string) $seccion->id)>
                                    {{ $seccion->grado?->nombre ?? '-' }} - {{ $seccion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Curso</label>
                        <select name="curso_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id }}" @selected((string) $cursoId === (string) $curso->id)>
                                    {{ $curso->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Evaluaci&oacute;n</label>
                        <select name="periodo" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($periodos as $item)
                                <option value="{{ $item }}" @selected($periodo === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Buscar alumno</label>
                        <input type="text" name="buscar" class="form-control" placeholder="Nombre, apellido o DNI"
                            value="{{ $buscar }}">
                    </div>
                    <div class="col-lg-1 d-grid">
                        <button class="btn jp-btn-search" type="submit">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card jp-soft-card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table jp-table jp-notas-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Secci&oacute;n</th>
                            <th>Curso</th>
                            <th>Evaluaci&oacute;n</th>
                            <th>Estudiante</th>
                            <th>Nota</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notas as $nota)
                            <tr>
                                <td>
                                    {{ $nota->seccion?->grado?->nombre ?? '-' }} - {{ $nota->seccion?->nombre ?? '-' }}
                                </td>
                                <td>{{ $nota->curso?->nombre ?? '-' }}</td>
                                <td>{{ $nota->periodo }}</td>
                                <td>
                                    {{ $nota->estudiante?->apellidos ?? '-' }},
                                    {{ $nota->estudiante?->nombres ?? '' }}
                                </td>
                                <td>{{ $nota->valor ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $nota->estado === 'cerrada' ? 'bg-danger' : 'bg-success' }}">
                                        {{ ucfirst($nota->estado ?? 'abierta') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No hay notas registradas con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 jp-table-pagination">
                {{ $notas->links() }}
            </div>
        </div>
    </div>
@endsection
