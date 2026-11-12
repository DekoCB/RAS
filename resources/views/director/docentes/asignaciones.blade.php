@extends('layouts.mantis')

@section('title', 'Asignación de Docentes')
@section('page-title', 'Asignación de Docentes')
@section('body-class', 'role-director')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    <style>
        .jp-assign-card {
            border-radius: 20px;
            border: 1px solid #e0eaef;
            box-shadow: 0 16px 28px rgba(15, 19, 42, 0.08);
            background: #fff;
        }
        .jp-assign-filter-card {
            background: linear-gradient(180deg, #f3faff 0%, #ffffff 100%);
        }
        .jp-assign-filter {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            align-items: end;
        }
        .jp-assign-filter .form-label {
            font-weight: 700;
            color: #2a3f5b;
        }
        .jp-assign-filter .form-select {
            border-radius: 12px;
            border: 1px solid #cee0ea;
        }
        .jp-assign-filter .btn {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            box-shadow: 0 10px 18px rgba(24, 36, 111, 0.18);
        }
        .jp-assign-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .jp-assign-title {
            margin-bottom: 6px;
            font-weight: 800;
            color: #11194a;
        }
        .jp-assign-sub {
            margin: 0;
            color: #6b7280;
        }
        .jp-assign-search {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .jp-assign-search .form-control {
            border-radius: 999px;
            border: 1px solid #cee0ea;
            min-width: 220px;
        }
        .jp-assign-search .btn {
            border-radius: 999px;
            font-weight: 700;
        }
        .jp-assign-context {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .jp-assign-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #d3e2eb;
            background: #eff9ff;
            color: #1d265e;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .jp-assign-chip.is-warning {
            color: #18246e;
            border-color: #c6e1f4;
            background: #edf8ff;
        }
        .jp-assign-chip.is-primary {
            color: #1f2e8d;
            border-color: #dbeafe;
            background: #eff6ff;
        }
        .jp-assign-table thead th {
            background: #eff6fd;
            color: #1d265e;
            border-bottom: none;
            font-weight: 700;
        }
        .jp-assign-table tbody td {
            border-top: 1px solid #dce7f1;
        }
        .jp-assign-modal .modal-content {
            border-radius: 20px;
            border: 1px solid #d6e5ef;
            box-shadow: 0 20px 40px rgba(15, 19, 42, 0.16);
            max-height: calc(100vh - 2rem);
            background: #f9fdff;
        }
        .jp-assign-modal .modal-dialog {
            max-height: calc(100vh - 2rem);
        }
        .jp-assign-modal .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 240px);
            background: #fff;
        }
        .jp-assign-modal .modal-header {
            border-bottom: 1px solid #dce7f1;
            background: #f0f9ff;
        }
        .jp-assign-modal .modal-footer {
            border-top: 1px solid #dce7f1;
            background: #f0f9ff;
        }
        .jp-assign-modal .modal-title {
            font-weight: 800;
            color: #11194a;
        }
        .jp-assign-modal .modal-title small {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
        }
        .jp-assign-modal .info-banner {
            background: #eef5ff;
            border: 1px solid #cfe1ff;
            border-radius: 18px;
            padding: 14px 16px;
        }
        .jp-assign-modal .info-banner__icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #25359c;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 18px rgba(37, 53, 156, 0.2);
        }
        .jp-assign-modal .info-banner__text strong {
            color: #192158;
        }
        .jp-assign-modal .info-banner__meta {
            color: #2c3366;
            font-size: 0.9rem;
        }
        .jp-assign-modal .jp-table thead th {
            background: #eff7ff;
            color: #1d265e;
            border-bottom: none;
            font-weight: 700;
        }
        .jp-assign-modal .jp-table tbody td {
            border-top: 1px solid #dce7f1;
            vertical-align: middle;
        }
        .jp-assign-modal .jp-table tbody tr:hover {
            background: rgba(24, 36, 111, 0.04);
        }
        .jp-assign-modal .form-select {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
        }
        .jp-assign-modal .btn-primary {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            background: linear-gradient(90deg, #25359c, #213197);
            border: none;
            box-shadow: 0 12px 18px rgba(33, 49, 151, 0.25);
        }
        .jp-assign-modal .btn-outline-secondary {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            border-color: #e5e7eb;
            color: #6b7280;
            background: #fff;
        }
        .jp-assign-modal .btn-outline-secondary:hover {
            background: #f9fafb;
            color: #4b5563;
        }
        .jp-assign-modal__stats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }
        .jp-assign-stat {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 12px;
            background: #f5faff;
            border: 1px solid #dce7f1;
            font-size: 0.82rem;
            color: #1d265e;
            font-weight: 600;
        }
        .jp-assign-stat strong {
            font-size: 1rem;
            color: #0f153f;
            line-height: 1;
        }
        .jp-assign-stat.is-warning {
            color: #18246e;
            border-color: #bddff5;
            background: #edf8ff;
        }
        .jp-assign-stat.is-info {
            color: #1f2e8d;
            border-color: #dbeafe;
            background: #eff6ff;
        }
        .jp-assign-modal__tools {
            margin-top: 14px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
        }
        .jp-assign-modal__search {
            min-width: 0;
        }
        .jp-assign-modal__search .form-control {
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            padding-inline: 14px;
        }
        .jp-assign-modal__bulk {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .jp-assign-modal__bulk .form-select {
            min-width: 220px;
        }
        .jp-assign-modal__bulk .btn {
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 7px 12px;
            box-shadow: none;
        }
        .jp-course-row.is-pending td:first-child {
            border-left: 3px solid #22329c;
        }
        .jp-course-row.is-dirty {
            background: rgba(37, 53, 156, 0.06);
        }
        .jp-row-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .jp-row-status.is-assigned {
            color: #166534;
            background: #dcfce7;
        }
        .jp-row-status.is-pending {
            color: #18246e;
            background: #d5f0ff;
        }
        .jp-row-status.is-dirty {
            color: #1f2e8d;
            background: #dbeafe;
        }
        .jp-course-name {
            font-weight: 600;
            color: #2f3a45;
        }
        .jp-empty-results {
            margin-top: 10px;
            border-radius: 12px;
            border: 1px dashed #d1d5db;
            background: #f9fafb;
            color: #6b7280;
            padding: 10px 12px;
            font-size: 0.86rem;
        }
        .jp-modal-change-indicator {
            margin-right: auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 0.86rem;
            font-weight: 600;
        }
        .jp-modal-change-indicator strong {
            color: #213197;
        }
        .jp-modal-change-indicator.is-dirty {
            color: #1f2e8d;
        }
        @media (max-width: 992px) {
            .jp-assign-filter {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .jp-assign-search {
                width: 100%;
            }
            .jp-assign-search .form-control {
                flex: 1;
            }
            .jp-assign-modal__tools {
                grid-template-columns: 1fr;
            }
            .jp-assign-modal__bulk {
                justify-content: flex-start;
            }
        }
        @media (max-width: 576px) {
            .jp-assign-filter {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('sidebar')
    @include('director.partials.sidebar')
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card jp-search-card jp-assign-card jp-assign-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('director.docentes.asignaciones') }}" class="jp-assign-filter">
                <input type="hidden" name="open_modal" value="1">
                <div>
                    <label class="form-label">A&ntilde;o escolar</label>
                    <select name="anio_escolar_id" class="form-select">
                        @foreach ($anios as $anioItem)
                            <option value="{{ $anioItem->id }}" @selected($anio?->id === $anioItem->id)>
                                {{ $anioItem->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Periodo ac</label>
                    <select name="periodo_academico_id" class="form-select">
                        @forelse ($periodos as $periodoItem)
                            <option value="{{ $periodoItem->id }}" @selected($periodo?->id === $periodoItem->id)>
                                {{ $periodoItem->codigo }} - {{ $periodoItem->nombre }}
                            </option>
                        @empty
                            <option value="">Sin periodos</option>
                        @endforelse        
                    </select>
                </div> 
                <div>
                    <label class="form-label">Secci&oacute;n</label>
                    <select name="seccion_id" class="form-select">
                        <option value="">Seleccione una secci&oacute;n</option>
                        @foreach ($secciones as $seccionItem)
                            <option value="{{ $seccionItem->id }}" @selected($seccion?->id === $seccionItem->id)>
                                {{ $seccionItem->grado?->nivel ?? '-' }} - {{ $seccionItem->grado?->nombre ?? '-' }} -
                                {{ $seccionItem->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex align-items-end">
                    <button type="submit" class="btn jp-btn-search">Cargar cursos</button>
                </div>
            </form>
        </div>
    </div>

    @if ($anio)
        <div class="card jp-soft-card jp-assign-card mt-3">
            <div class="card-header">
                <div class="jp-assign-header">
                    <div>
                        <h5 class="jp-assign-title">Resumen general</h5>
                        <p class="jp-assign-sub">A&ntilde;o {{ $anio->nombre }}</p>
                    </div>
                    <form method="GET" action="{{ route('director.docentes.asignaciones') }}" class="jp-assign-search">
                    <input type="hidden" name="anio_escolar_id" value="{{ $anio->id }}">
                    @if ($periodo)
                        <input type="hidden" name="periodo_academico_id" value="{{ $periodo->id }}">
                    @endif
                    @if ($seccion)
                        <input type="hidden" name="seccion_id" value="{{ $seccion->id }}">
                    @endif
                    <input type="text" name="docente" class="form-control form-control-sm"
                        placeholder="Buscar docente..." value="{{ $docenteFiltro }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Buscar</button>
                        @if ($seccion)
                            <button type="submit" name="open_modal" value="1" class="btn btn-sm btn-primary">
                                Editar cursos
                            </button>
                        @endif
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="jp-assign-context">
                    @php
                        $periodoTexto = $periodo
                            ? trim(($periodo->codigo ?? '') . ' - ' . ($periodo->nombre ?? ''))
                            : 'Sin periodo';
                    @endphp
                    <span class="jp-assign-chip">
                        Periodo: {{ $periodoTexto !== '-' ? $periodoTexto : 'Sin periodo' }}
                    </span>
                    <span class="jp-assign-chip">Cursos asignados: {{ $totalAsignadas }}</span>
                    <span class="jp-assign-chip is-warning">Pendientes: {{ $pendientes }}</span>
                    @if ($seccion)
                        <span class="jp-assign-chip is-primary">
                            Secci&oacute;n activa: {{ $seccion->grado?->nivel ?? '-' }} - {{ $seccion->grado?->nombre ?? '-' }} -
                            {{ $seccion->nombre }}
                        </span>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover jp-table jp-assign-table">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Total cursos</th>
                                <th>Cursos</th>
                                <th>Secciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($resumenAsignaciones as $resumen)
                                <tr>
                                    <td>{{ $resumen['docente'] }}</td>
                                    <td>{{ $resumen['total_cursos'] }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($resumen['cursos'] as $curso)
                                                <span class="badge bg-light text-dark">{{ $curso }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($resumen['secciones'] as $seccionNombre)
                                                <span class="badge bg-secondary">{{ $seccionNombre }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No hay asignaciones registradas para este a&ntilde;o.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($seccion)
        <div class="modal fade jp-assign-modal" id="assignModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Asignaci&oacute;n de docentes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    @if ($cursos->isEmpty())
                        <div class="modal-body">
                            <div class="info-banner">
                                <div class="info-banner__icon"><i data-feather="info"></i></div>
                                <div>
                                    <div class="info-banner__text">
                                        <strong>Secci&oacute;n seleccionada</strong>
                                        {{ $seccion->grado?->nivel ?? '-' }} - {{ $seccion->grado?->nombre ?? '-' }} -
                                        {{ $seccion->nombre }}
                                    </div>
                                    <div class="info-banner__meta">
                                        Periodo acad&eacute;mico:
                                        {{ $periodo?->codigo ?? $anio?->nombre ?? '-' }}
                                        @if ($periodo?->nombre)
                                            - {{ $periodo->nombre }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning mb-0">
                                Esta secci&oacute;n no tiene cursos asignados. As&iacute;gnelos desde matr&iacute;cula antes de continuar.
                            </div>
                        </div>
                    @else
                        @php
                            $totalCursosSeccion = $cursos->count();
                            $cursosAsignadosSeccion = $cursos->filter(fn ($curso) => !empty($curso->pivot?->docente_id))->count();
                            $cursosPendientesSeccion = $totalCursosSeccion - $cursosAsignadosSeccion;
                        @endphp
                        <form method="POST" action="{{ route('director.docentes.asignaciones.guardar') }}">
                            @csrf
                            <input type="hidden" name="anio_escolar_id" value="{{ $anio->id }}">
                            @if ($periodo)
                                <input type="hidden" name="periodo_academico_id" value="{{ $periodo->id }}">
                            @endif
                            <input type="hidden" name="seccion_id" value="{{ $seccion->id }}">

                            <div class="modal-body">
                                <div class="info-banner">
                                    <div class="info-banner__icon"><i data-feather="info"></i></div>
                                    <div>
                                        <div class="info-banner__text">
                                            <strong>Secci&oacute;n seleccionada</strong>
                                            {{ $seccion->grado?->nivel ?? '-' }} - {{ $seccion->grado?->nombre ?? '-' }} -
                                            {{ $seccion->nombre }}
                                        </div>
                                        <div class="info-banner__meta">
                                            Periodo acad&eacute;mico:
                                            {{ $periodo?->codigo ?? $anio?->nombre ?? '-' }}
                                            @if ($periodo?->nombre)
                                                - {{ $periodo->nombre }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="jp-assign-modal__stats" id="assignModalStats">
                                    <div class="jp-assign-stat">
                                        <strong id="statTotalCursos">{{ $totalCursosSeccion }}</strong>
                                        Cursos totales
                                    </div>
                                    <div class="jp-assign-stat">
                                        <strong id="statAsignados">{{ $cursosAsignadosSeccion }}</strong>
                                        Con docente
                                    </div>
                                    <div class="jp-assign-stat is-warning">
                                        <strong id="statPendientes">{{ $cursosPendientesSeccion }}</strong>
                                        Pendientes
                                    </div>
                                    <div class="jp-assign-stat is-info">
                                        <strong id="statCambios">0</strong>
                                        Cambios sin guardar
                                    </div>
                                </div>

                                <div class="jp-assign-modal__tools">
                                    <div class="jp-assign-modal__search">
                                        <input type="text" class="form-control" id="courseSearchInput"
                                            placeholder="Buscar curso o docente asignado...">
                                    </div>
                                    <div class="jp-assign-modal__bulk">
                                        <select id="bulkDocenteSelect" class="form-select form-select-sm">
                                            <option value="">Selecciona docente para acciones masivas</option>
                                            @foreach ($docentes as $docente)
                                                <option value="{{ $docente->id }}">{{ $docente->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="assignPendingBtn">
                                            Asignar pendientes
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="assignAllBtn">
                                            Asignar todos
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearAllBtn">
                                            Limpiar todo
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive mt-3">
                                    <table class="table table-hover jp-table" id="assignCoursesTable">
                                        <thead>
                                            <tr>
                                                <th>Curso</th>
                                                <th>Estado</th>
                                                <th>Docente asignado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cursos as $curso)
                                                @php
                                                    $docenteActual = (string) ($curso->pivot?->docente_id ?? '');
                                                    $cursoAsignado = $docenteActual !== '';
                                                @endphp
                                                <tr class="jp-course-row {{ $cursoAsignado ? '' : 'is-pending' }}" data-course-row>
                                                    <td>
                                                        <span class="jp-course-name">{{ $curso->nombre }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="jp-row-status {{ $cursoAsignado ? 'is-assigned' : 'is-pending' }}"
                                                            data-status-badge>
                                                            {{ $cursoAsignado ? 'Asignado' : 'Pendiente' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <select name="docentes[{{ $curso->id }}]" class="form-select"
                                                            data-course-select data-initial-value="{{ $docenteActual }}">
                                                            <option value="">Sin asignar</option>
                                                            @foreach ($docentes as $docente)
                                                                <option value="{{ $docente->id }}"
                                                                    @selected($curso->pivot?->docente_id == $docente->id)>
                                                                    {{ $docente->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="jp-empty-results" id="assignNoResults" hidden>
                                    No hay cursos que coincidan con la b&uacute;squeda.
                                </div>
                            </div>

                            <div class="modal-footer">
                                <div class="jp-modal-change-indicator" id="changesIndicator">
                                    Cambios sin guardar: <strong id="changesCount">0</strong>
                                </div>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary" id="saveAssignmentsBtn" disabled>
                                    Guardar asignaciones
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Selecciona el a&ntilde;o escolar y la secci&oacute;n para ver los cursos.
        </div>
    @endif
@endsection

@push('scripts')
    @php
        $debeAbrirModal = $seccion && (request()->boolean('open_modal') || $errors->any());
    @endphp
    @if ($debeAbrirModal)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalEl = document.getElementById('assignModal');
                if (!modalEl || typeof bootstrap === 'undefined') {
                    return;
                }
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                const form = modalEl.querySelector('form');
                if (!form) {
                    return;
                }

                const courseRows = Array.from(modalEl.querySelectorAll('[data-course-row]'));
                const courseSelects = Array.from(modalEl.querySelectorAll('[data-course-select]'));
                const searchInput = document.getElementById('courseSearchInput');
                const bulkDocenteSelect = document.getElementById('bulkDocenteSelect');
                const assignPendingBtn = document.getElementById('assignPendingBtn');
                const assignAllBtn = document.getElementById('assignAllBtn');
                const clearAllBtn = document.getElementById('clearAllBtn');
                const emptyResults = document.getElementById('assignNoResults');
                const statAsignados = document.getElementById('statAsignados');
                const statPendientes = document.getElementById('statPendientes');
                const statCambios = document.getElementById('statCambios');
                const changesCount = document.getElementById('changesCount');
                const changesIndicator = document.getElementById('changesIndicator');
                const saveAssignmentsBtn = document.getElementById('saveAssignmentsBtn');

                const normalizeValue = function (value) {
                    return value ? String(value) : '';
                };

                const countChanges = function () {
                    return courseSelects.filter(function (select) {
                        return normalizeValue(select.value) !== normalizeValue(select.dataset.initialValue);
                    }).length;
                };

                const countAssigned = function () {
                    return courseSelects.filter(function (select) {
                        return normalizeValue(select.value) !== '';
                    }).length;
                };

                const setRowStatus = function (select) {
                    const row = select.closest('[data-course-row]');
                    if (!row) {
                        return;
                    }

                    const hasDocente = normalizeValue(select.value) !== '';
                    const isDirty = normalizeValue(select.value) !== normalizeValue(select.dataset.initialValue);
                    const badge = row.querySelector('[data-status-badge]');

                    row.classList.toggle('is-pending', !hasDocente);
                    row.classList.toggle('is-dirty', isDirty);

                    if (!badge) {
                        return;
                    }

                    badge.classList.remove('is-assigned', 'is-pending', 'is-dirty');

                    if (isDirty) {
                        badge.classList.add('is-dirty');
                        badge.textContent = hasDocente ? 'Editado' : 'Pendiente';
                        return;
                    }

                    if (hasDocente) {
                        badge.classList.add('is-assigned');
                        badge.textContent = 'Asignado';
                    } else {
                        badge.classList.add('is-pending');
                        badge.textContent = 'Pendiente';
                    }
                };

                const updateStats = function () {
                    const assigned = countAssigned();
                    const pending = courseSelects.length - assigned;
                    const changes = countChanges();

                    if (statAsignados) {
                        statAsignados.textContent = String(assigned);
                    }
                    if (statPendientes) {
                        statPendientes.textContent = String(pending);
                    }
                    if (statCambios) {
                        statCambios.textContent = String(changes);
                    }
                    if (changesCount) {
                        changesCount.textContent = String(changes);
                    }
                    if (changesIndicator) {
                        changesIndicator.classList.toggle('is-dirty', changes > 0);
                    }
                    if (saveAssignmentsBtn) {
                        saveAssignmentsBtn.disabled = changes === 0;
                    }
                };

                const applySearchFilter = function () {
                    const rawTerm = searchInput ? searchInput.value : '';
                    const term = (rawTerm || '').trim().toLowerCase();
                    let visibleRows = 0;

                    courseRows.forEach(function (row) {
                        const courseNameEl = row.querySelector('.jp-course-name');
                        const courseName = (courseNameEl ? courseNameEl.textContent : '').toLowerCase();
                        const select = row.querySelector('[data-course-select]');
                        const selectedOption = select && select.selectedIndex >= 0
                            ? select.options[select.selectedIndex]
                            : null;
                        const docenteName = (selectedOption ? selectedOption.textContent : '').toLowerCase();
                        const matches = term === '' || courseName.includes(term) || docenteName.includes(term);

                        row.classList.toggle('d-none', !matches);
                        if (matches) {
                            visibleRows += 1;
                        }
                    });

                    if (emptyResults) {
                        emptyResults.hidden = visibleRows > 0;
                    }
                };

                const assignMassive = function (onlyPending) {
                    const docenteId = normalizeValue(bulkDocenteSelect ? bulkDocenteSelect.value : '');
                    if (!docenteId) {
                        return;
                    }

                    courseSelects.forEach(function (select) {
                        if (onlyPending && normalizeValue(select.value) !== '') {
                            return;
                        }
                        select.value = docenteId;
                        setRowStatus(select);
                    });

                    updateStats();
                    applySearchFilter();
                };

                const clearAllAssignments = function () {
                    courseSelects.forEach(function (select) {
                        select.value = '';
                        setRowStatus(select);
                    });

                    updateStats();
                    applySearchFilter();
                };

                courseSelects.forEach(function (select) {
                    setRowStatus(select);
                    select.addEventListener('change', function () {
                        setRowStatus(select);
                        updateStats();
                        applySearchFilter();
                    });
                });

                if (searchInput) {
                    searchInput.addEventListener('input', applySearchFilter);
                }

                if (assignPendingBtn) {
                    assignPendingBtn.addEventListener('click', function () {
                        assignMassive(true);
                    });
                }

                if (assignAllBtn) {
                    assignAllBtn.addEventListener('click', function () {
                        assignMassive(false);
                    });
                }

                if (clearAllBtn) {
                    clearAllBtn.addEventListener('click', function () {
                        clearAllAssignments();
                    });
                }

                modalEl.addEventListener('hide.bs.modal', function (event) {
                    const hasPendingChanges = countChanges() > 0;
                    if (hasPendingChanges && !form.dataset.submitting) {
                        const allowClose = window.confirm('Tienes cambios sin guardar. Deseas cerrar de todos modos?');
                        if (!allowClose) {
                            event.preventDefault();
                        }
                    }
                });

                form.addEventListener('submit', function () {
                    form.dataset.submitting = '1';
                    if (saveAssignmentsBtn) {
                        saveAssignmentsBtn.disabled = true;
                        saveAssignmentsBtn.textContent = 'Guardando...';
                    }
                });

                updateStats();
                applySearchFilter();
            });
        </script>
    @endif
@endpush
