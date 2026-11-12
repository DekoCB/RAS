@extends('layouts.mantis')

@section('title', 'Asistencias | Secretaria')
@section('page-title', 'Asistencias')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    <div class="jp-attendance-wrap">
        <div class="card jp-docente-filter mt-2">
            <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h5 class="jp-docente-filter-title">Asistencias</h5>
                    <p class="jp-docente-filter-sub">
                        Consulta y filtra los registros de asistencia por alumno, fecha y secci&oacute;n.
                    </p>
                    <div class="jp-docente-filter-meta">
                        <span class="jp-docente-filter-pill">
                            <i data-feather="calendar"></i>
                            {{ request('fecha') ? 'Fecha ' . (\Illuminate\Support\Carbon::make(request('fecha'))?->format('d/m/Y') ?? request('fecha')) : 'Hoy ' . now()->format('d/m/Y') }}
                        </span>
                        <span class="jp-docente-filter-pill is-accent">
                            <i data-feather="sun"></i>
                            Turno Ma&ntilde;ana
                        </span>
                        <span class="jp-docente-filter-pill is-muted">
                            <i data-feather="lock"></i>
                            Solo lectura
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="jp-attendance-metrics">
            <div class="jp-attendance-metric is-total">
                <span>Inicial</span>
                <strong>07:50 - 12:50</strong>
            </div>
            <div class="jp-attendance-metric is-presente">
                <span>Primaria</span>
                <strong>07:50 - 13:10</strong>
            </div>
            <div class="jp-attendance-metric is-tarde">
                <span>Secundaria</span>
                <strong>07:40 - 14:30</strong>
            </div>
            <div class="jp-attendance-metric is-justificado">
                <span>Tolerancia</span>
                <strong>10 minutos</strong>
            </div>
        </div>

        <div class="card jp-search-card">
            <div class="card-body">
                <form method="GET" action="{{ route('secretaria.asistencias.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label">Alumno (DNI o nombre)</label>
                            <div class="input-group jp-search-input">
                                <span class="input-group-text"><i data-feather="search"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Buscar alumno..."
                                    value="{{ request('q') }}">
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Secci&oacute;n</label>
                            <select name="seccion_id" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($secciones as $seccion)
                                    <option value="{{ $seccion->id }}" @selected((string) request('seccion_id') === (string) $seccion->id)>
                                        {{ $seccion->grado?->nombre ?? '-' }} - {{ $seccion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="presente" @selected(request('estado') === 'presente')>Presente</option>
                                <option value="tarde" @selected(request('estado') === 'tarde')>Tarde</option>
                                <option value="ausente" @selected(request('estado') === 'ausente')>Ausente</option>
                                <option value="justificado" @selected(request('estado') === 'justificado')>Justificado</option>
                            </select>
                        </div>
                        <div class="col-lg-1 d-grid">
                            <button class="btn jp-btn-search" type="submit">Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card jp-attendance-card">
            <div class="table-responsive">
                <table class="table jp-attendance-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Alumno</th>
                            <th>DNI</th>
                            <th>Nivel/Grado</th>
                            <th>Secci&oacute;n</th>
                            <th>Estado</th>
                            <th>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($asistencias as $asistencia)
                            @php
                                $estado = strtolower($asistencia->estado ?? '');
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
                                <td>{{ \Illuminate\Support\Carbon::make($asistencia->fecha)?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $asistencia->estudiante?->apellidos }} {{ $asistencia->estudiante?->nombres }}</td>
                                <td>{{ $asistencia->estudiante?->dni ?? '-' }}</td>
                                <td>{{ $asistencia->seccion?->grado?->nombre ?? '-' }}</td>
                                <td>{{ $asistencia->seccion?->nombre ?? '-' }}</td>
                                <td>
                                    <span class="jp-attendance-pill {{ $pill }}">
                                        {{ ucfirst($estadoNormalizado ?: '-') }}
                                    </span>
                                </td>
                                <td>{{ $asistencia->registradoPor?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay registros de asistencia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0">
                {{ $asistencias->links() }}
            </div>
        </div>
    </div>
@endsection
