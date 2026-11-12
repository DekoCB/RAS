@extends('layouts.mantis')

@php
$rolLabel = match(true) {
    auth()->user()?->hasRole('SuperAdmin') => 'SuperAdmin',
    auth()->user()?->hasRole('Secretaria') => 'Secretaria',
    default => 'Director',
};
@endphp
@section('title', 'Gestionar Secciones | ' . $rolLabel)
@section('page-title', 'Gestionar Secciones')
@section('body-class', 'role-' . strtolower($rolLabel))

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @elseif(auth()->user()->hasRole('Secretaria'))
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    @endif
    <style>
        .sec-soft-card {
            border: 1px solid #d7e0ea;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(18, 27, 82, 0.10);
        }

        .sec-inst-bar {
            background: linear-gradient(120deg, #162061 0%, #1c297e 50%, #1f2e8e 100%);
            border-radius: 14px;
            padding: .8rem 1.2rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1.4rem;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            box-shadow: 0 8px 20px rgba(28, 41, 126, .28);
        }

        .sec-inst-bar i {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }

        .sec-inst-bar span {
            opacity: .88;
            font-weight: 400;
            font-size: .78rem;
        }

        .sec-btn-add {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .5rem 1.2rem;
            background: linear-gradient(135deg, #1f2e8e, #1b287b);
            color: #fff;
            border: 0;
            border-radius: 12px;
            font-weight: 700;
            font-size: .85rem;
            box-shadow: 0 8px 16px rgba(31, 46, 142, .30);
        }

        .sec-btn-add:hover {
            background: linear-gradient(135deg, #1b287b, #17226a);
            color: #fff;
        }

        .sec-tabs {
            border-bottom: 0;
            margin-bottom: 1.35rem;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .sec-tabs .nav-link {
            border-radius: 999px;
            padding: .45rem 1.2rem;
            font-weight: 700;
            font-size: .83rem;
            color: #17226a;
            border: 1.5px solid #b8c1fd;
            background: #ebfaff;
        }

        .sec-tabs .nav-link.active {
            background: linear-gradient(135deg, #1c297e 0%, #1f2e8e 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 6px 14px rgba(28, 41, 126, .30);
        }

        .sec-table {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #b8c1fd;
        }

        .sec-table thead th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            color: #fff;
            padding: .75rem 1rem;
            background: linear-gradient(120deg, #162061 0%, #1c297e 55%, #1f2e8e 100%);
            border-bottom: 0;
            white-space: nowrap;
        }

        .sec-table tbody td {
            padding: .65rem 1rem;
            vertical-align: middle;
            font-size: .88rem;
            border-bottom: 1px solid #eef9fe;
        }

        .sec-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 28px;
            background: linear-gradient(135deg, #1c297e, #1f2e8e);
            color: #fff;
            border-radius: 8px;
            font-size: .75rem;
            font-weight: 800;
        }

        .sec-vac-nd {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .28rem .75rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, .25);
        }

        .sec-vac-nd i {
            width: 12px;
            height: 12px;
        }

        .sec-btn-edit {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .85rem;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            font-size: .78rem;
            font-weight: 700;
        }

        .sec-btn-edit:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .sec-btn-del {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .85rem;
            border-radius: 10px;
            border: 1.5px solid #cae0fe;
            background: #f5f9ff;
            color: #1c297e;
            font-size: .78rem;
            font-weight: 700;
        }

        .sec-btn-del:hover {
            background: #e2eefe;
            color: #18246e;
        }

        .sec-btn-course {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .85rem;
            border-radius: 10px;
            border: 1.5px solid #c7d2fe;
            background: #eef2ff;
            color: #253286;
            font-size: .78rem;
            font-weight: 700;
        }

        .sec-btn-course:hover {
            background: #e0e7ff;
            color: #222d73;
        }

        .sec-course-list {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: .75rem .85rem;
        }

        .sec-course-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding: .4rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .sec-course-item:last-child {
            border-bottom: 0;
        }

        .sec-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: #94a3b8;
        }

        /* ── Modales institucionales ──────────────────────── */
        .sec-modal .modal-content {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1.5rem 3.5rem rgba(21, 31, 94, .35);
        }
        .sec-modal .modal-header {
            background: linear-gradient(120deg, #162061 0%, #1c297e 55%, #1f2e8e 100%);
            border-bottom: 0;
            padding: 1.2rem 1.5rem;
            color: #fff;
        }
        .sec-modal .modal-title {
            font-weight: 800;
            font-size: .98rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .sec-modal .modal-title i { width: 18px; height: 18px; }
        .sec-modal .btn-close { filter: invert(1) grayscale(100%); opacity: .9; }
        .sec-modal .modal-body { padding: 1.6rem 1.75rem; background: #fff; }
        .sec-modal .modal-footer {
            background: #fafafa;
            border-top: 1px solid #f1f5f9;
            padding: 1rem 1.75rem;
        }

        /* ── Labels en modales ───────────────────────────── */
        .sec-modal-field label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: .35rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }
        .sec-modal-field label i { width: 13px; height: 13px; color: #94a3b8; }
        .sec-modal-field .form-control,
        .sec-modal-field .form-select {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            min-height: 44px;
            font-size: .88rem;
            background: #fafafa;
            transition: border-color .18s, box-shadow .18s;
        }
        .sec-modal-field .form-control:focus,
        .sec-modal-field .form-select:focus {
            border-color: #1f2e8e;
            box-shadow: 0 0 0 3px rgba(31, 46, 142,.15);
            background: #fff;
        }

        /* ── Botones en modales ──────────────────────────── */
        .sec-btn-modal-cancel {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .55rem 1.1rem;
            background: #f1f5f9; color: #475569;
            border: 1.5px solid #e2e8f0; border-radius: 12px;
            font-weight: 700; font-size: .88rem;
            transition: all .18s ease;
        }
        .sec-btn-modal-cancel:hover { background: #e2e8f0; color: #334155; }
        .sec-btn-modal-cancel i { width: 15px; height: 15px; }

        .sec-btn-modal-save {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .55rem 1.35rem;
            background: linear-gradient(135deg, #1c297e, #1f2e8e);
            color: #fff; border: 0; border-radius: 12px;
            font-weight: 700; font-size: .88rem;
            box-shadow: 0 8px 16px rgba(28, 41, 126,.22);
            transition: all .18s ease;
        }
        .sec-btn-modal-save:hover { background: linear-gradient(135deg,#18246e,#1b287b); color:#fff; transform:translateY(-1px); }
        .sec-btn-modal-save i { width: 15px; height: 15px; }

        /* ── Info chips en modal cursos ──────────────────── */
        .sec-info-chip {
            background: #ebfaff;
            border: 1.5px solid #b8c1fd;
            border-radius: 12px;
            padding: .65rem 1rem;
        }
        .sec-info-chip .label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #17226a; margin-bottom: .15rem; }
        .sec-info-chip .value { font-weight: 700; color: #1e293b; font-size: .92rem; }

        /* ── Lista de cursos ─────────────────────────────── */
        .sec-course-list-styled {
            border: 1.5px solid #b8c1fd;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }
        .sec-course-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .7rem 1rem;
            border-bottom: 1px solid #eef9fe;
            transition: background .15s;
            cursor: pointer;
        }
        .sec-course-row:last-child { border-bottom: 0; }
        .sec-course-row:hover { background: #ebfaff; }
        .sec-course-row .name { font-weight: 600; font-size: .88rem; color: #1e293b; }
        .sec-course-row .form-check-input {
            width: 18px; height: 18px;
            border-radius: 6px; border: 1.5px solid #1f2e8e;
            cursor: pointer;
        }
        .sec-course-row .form-check-input:checked {
            background-color: #1f2e8e; border-color: #1f2e8e;
        }
        .sec-course-empty {
            padding: 2rem; text-align: center; color: #94a3b8; font-size: .85rem;
        }
    </style>
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @elseif(auth()->user()->hasRole('Secretaria'))
        @include('secretaria.partials.sidebar')
    @else
        @include('director.partials.sidebar')
    @endif
@endsection

@section('content')
    @php
        $nivelesAcademicos = collect(['Inicial', 'Primaria', 'Secundaria'])
            ->merge(($seccionesPorNivel ?? collect())->keys())
            ->unique()
            ->values();
    @endphp

    @if (session('status'))
        <div class="alert alert-success mt-2">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mt-2">
            {{ session('error') }}
        </div>
    @endif

    <div class="card sec-soft-card mt-2">
        <div class="card-body p-4">
            <div class="sec-inst-bar">
                <i data-feather="award"></i>
                <strong>R.A.S</strong>
                <span>&nbsp;|&nbsp; Valores: Estudio | Trabajo | Disciplina</span>
            </div>

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h5 class="fw-800 mb-0" style="color:#1e293b;">Grados y Secciones</h5>
                    <p class="text-muted small mb-0 mt-1">
                        Administra los grados, secciones y vacantes disponibles por nivel académico.
                    </p>
                </div>
                <button type="button" class="sec-btn-add" data-bs-target="#agregarGradoSeccionModal" data-bs-toggle="modal">
                    <i data-feather="plus"></i>
                    Agregar Grado/Sección
                </button>
            </div>

            @if (($seccionesPorNivel ?? collect())->isNotEmpty())
                <ul class="nav sec-tabs" id="tabNiveles" role="tablist">
                    @foreach ($seccionesPorNivel as $nivel => $secciones)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                id="nivel-{{ $loop->index }}-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#nivel-{{ $loop->index }}"
                                type="button"
                                role="tab">
                                {{ $nivel }}
                                <span style="margin-left:.35rem;font-size:.7rem;opacity:.75;">({{ $secciones->count() }})</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="tabNivelesContent">
                    @foreach ($seccionesPorNivel as $nivel => $secciones)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="nivel-{{ $loop->index }}"
                            role="tabpanel">
                            <div class="table-responsive">
                                <table class="table sec-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:70px;">ID</th>
                                            <th>Grado y Sección</th>
                                            <th>Matriculados</th>
                                            <th>Vacantes</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($secciones as $seccion)
                                            @php
                                                $capacidad = $seccion->aula?->capacidad;
                                                $matriculados = $seccion->matriculas_count ?? 0;
                                                $vacantes = is_null($capacidad) ? null : max($capacidad - $matriculados, 0);
                                                $periodoSeccion = $periodosPorSeccion[$seccion->id] ?? null;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="sec-id">#{{ $seccion->id }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-700">{{ $seccion->grado?->nombre ?? '-' }}</span>
                                                    <span class="text-muted"> - {{ $seccion->nombre }}</span>
                                                </td>
                                                <td>{{ $matriculados }}</td>
                                                <td>
                                                    @if (is_null($vacantes))
                                                        <span class="sec-vac-nd">
                                                            <i data-feather="minus-circle"></i>
                                                            Sin definir
                                                        </span>
                                                    @else
                                                        <span class="sec-vac-nd">
                                                            <i data-feather="users"></i>
                                                            {{ $vacantes }} libres
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @if ($periodoSeccion)
                                                            <button
                                                                type="button"
                                                                class="sec-btn-course js-sec-cursos"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#asignarCursosModal"
                                                                data-id="{{ $seccion->id }}"
                                                                data-cursos-url="{{ route($seccionesRoutePrefix . '.cursos.guardar', $seccion) }}"
                                                                data-seccion-label="{{ ($seccion->grado?->nombre ?? '-') . ' - ' . ($seccion->nombre ?? '-') }}"
                                                                data-periodo-id="{{ $periodoSeccion['id'] }}"
                                                                data-periodo-label="{{ $periodoSeccion['nombre'] }}"
                                                                data-cursos='@json($cursosAsignadosPorSeccion[$seccion->id] ?? [])'>
                                                                <i data-feather="book-open"></i> Cursos
                                                            </button>
                                                        @else
                                                            <button
                                                                type="button"
                                                                class="sec-btn-course"
                                                                title="No hay periodo acad&eacute;mico activo para esta seccion."
                                                                disabled>
                                                                <i data-feather="book-open"></i> Sin periodo
                                                            </button>
                                                        @endif

                                                        <button
                                                            type="button"
                                                            class="sec-btn-edit js-sec-edit"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editarGradoSeccionModal"
                                                            data-id="{{ $seccion->id }}"
                                                            data-update-url="{{ route($seccionesRoutePrefix . '.update', $seccion) }}"
                                                            data-nivel="{{ $seccion->grado?->nivel ?? '' }}"
                                                            data-grado="{{ $seccion->grado?->nombre ?? '' }}"
                                                            data-seccion="{{ $seccion->nombre ?? '' }}"
                                                            data-vacantes="{{ $seccion->aula?->capacidad ?? '' }}">
                                                            <i data-feather="edit-2"></i> Editar
                                                        </button>

                                                        <form method="POST"
                                                            action="{{ route($seccionesRoutePrefix . '.destroy', $seccion) }}"
                                                            onsubmit="return confirm('Deseas eliminar esta seccion?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="sec-btn-del">
                                                                <i data-feather="trash-2"></i> Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="sec-empty">
                                                        No hay secciones registradas en {{ $nivel }}.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sec-empty">
                    No hay niveles o secciones registradas todavía.
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade sec-modal" id="editarGradoSeccionModal" tabindex="-1" aria-labelledby="editarGradoSeccionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarGradoSeccionModalLabel">
                        <i data-feather="edit-2"></i>
                        Editar Grado / Sección
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editarSeccionForm" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="editing_seccion_id" id="editingSeccionId" value="{{ old('editing_seccion_id') }}">

                        <div class="col-md-4 sec-modal-field">
                            <label>
                                <i data-feather="layers"></i> Nivel Académico
                            </label>
                            <select name="edit_nivel_academico" id="editNivelAcademico"
                                    class="form-select @error('edit_nivel_academico') is-invalid @enderror" required>
                                <option value="">Seleccionar nivel...</option>
                                @foreach ($nivelesAcademicos as $nivel)
                                    <option value="{{ $nivel }}" {{ old('edit_nivel_academico') === $nivel ? 'selected' : '' }}>
                                        {{ $nivel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('edit_nivel_academico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-5 sec-modal-field">
                            <label>
                                <i data-feather="book"></i> Nombre del Grado
                            </label>
                            <input type="text" name="edit_grado_nombre" id="editGradoNombre"
                                   class="form-control @error('edit_grado_nombre') is-invalid @enderror"
                                   placeholder="Ej: Primero" value="{{ old('edit_grado_nombre') }}" required>
                            @error('edit_grado_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 sec-modal-field">
                            <label>
                                <i data-feather="tag"></i> Sección
                            </label>
                            <input type="text" name="edit_seccion_nombre" id="editSeccionNombre"
                                   class="form-control @error('edit_seccion_nombre') is-invalid @enderror"
                                   placeholder="Ej: A" value="{{ old('edit_seccion_nombre') }}" required>
                            @error('edit_seccion_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 sec-modal-field">
                            <label>
                                <i data-feather="users"></i> Vacantes
                            </label>
                            <input type="number" name="edit_vacantes" id="editVacantes" min="1"
                                   class="form-control @error('edit_vacantes') is-invalid @enderror"
                                   value="{{ old('edit_vacantes') }}">
                            @error('edit_vacantes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="sec-btn-modal-cancel" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="submit" form="editarSeccionForm" class="sec-btn-modal-save">
                        <i data-feather="save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade sec-modal" id="agregarGradoSeccionModal" tabindex="-1" aria-labelledby="agregarGradoSeccionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarGradoSeccionModalLabel">
                        <i data-feather="plus-circle"></i>
                        Agregar Grado / Sección
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route($seccionesRoutePrefix . '.guardar') }}" id="agregarSeccionForm" class="row g-3">
                        @csrf
                        {{-- Campo oculto que el backend espera --}}
                        <input type="hidden" name="nivel_academico" id="addNivelHidden">
                        <input type="hidden" name="nombre_grado_seccion" id="addNombreGradoSeccionHidden">

                        <div class="col-md-4 sec-modal-field">
                            <label><i data-feather="layers"></i> Nivel</label>
                            <select id="addNivelSelect" class="form-select @error('nivel_academico') is-invalid @enderror" required>
                                <option value="">Seleccionar nivel...</option>
                                @foreach ($nivelesAcademicos as $nivel)
                                    <option value="{{ $nivel }}">{{ $nivel }}</option>
                                @endforeach
                            </select>
                            @error('nivel_academico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 sec-modal-field">
                            <label><i data-feather="book"></i> Grado</label>
                            <select id="addGradoSelect" class="form-select" required disabled>
                                <option value="">— primero elige nivel —</option>
                            </select>
                            <input type="text" id="addGradoNuevo" class="form-control mt-1 d-none"
                                   placeholder="Nombre del nuevo grado">
                        </div>

                        <div class="col-md-2 sec-modal-field">
                            <label><i data-feather="tag"></i> Sección</label>
                            <input type="text" id="addSeccionNombre"
                                   class="form-control @error('nombre_grado_seccion') is-invalid @enderror"
                                   placeholder="A" maxlength="10" required
                                   style="text-transform:uppercase"
                                   oninput="this.value=this.value.toUpperCase()">
                            @error('nombre_grado_seccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-2 sec-modal-field">
                            <label><i data-feather="users"></i> Vacantes</label>
                            <input type="number" name="vacantes" min="1"
                                   class="form-control @error('vacantes') is-invalid @enderror"
                                   value="{{ old('vacantes', 30) }}" required>
                            @error('vacantes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="sec-btn-modal-cancel" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="submit" form="agregarSeccionForm" class="sec-btn-modal-save">
                        <i data-feather="check"></i> Guardar Sección
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade sec-modal" id="asignarCursosModal" tabindex="-1" aria-labelledby="asignarCursosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="asignarCursosModalLabel">
                        <i data-feather="book-open"></i>
                        Asignar Cursos a Sección
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="asignarCursosForm">
                        @csrf
                        <input type="hidden" name="assigning_seccion_id" id="assigningSeccionId" value="{{ old('assigning_seccion_id') }}">
                        <input type="hidden" name="periodo_academico_id" id="assigningPeriodoId" value="{{ old('periodo_academico_id') }}">

                        {{-- Info chips: sección y periodo --}}
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="sec-info-chip">
                                    <div class="label">
                                        <i data-feather="grid" style="width:12px;height:12px;"></i>
                                        Sección
                                    </div>
                                    <div class="value" id="assigningSeccionLabel">—</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="sec-info-chip">
                                    <div class="label">
                                        <i data-feather="calendar" style="width:12px;height:12px;"></i>
                                        Periodo Académico
                                    </div>
                                    <div class="value" id="assigningPeriodoLabel">—</div>
                                </div>
                            </div>
                        </div>

                        {{-- Instrucción --}}
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i data-feather="check-square" style="width:15px;height:15px;color:#1f2e8e;"></i>
                            <span style="font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">
                                Selecciona los cursos para esta sección
                            </span>
                        </div>

                        {{-- Lista de cursos estilizada --}}
                        <div class="sec-course-list-styled" style="max-height:340px;overflow-y:auto;">
                            @forelse ($cursos ?? collect() as $curso)
                                <label class="sec-course-row" for="curso-check-{{ $curso->id }}">
                                    <span class="name">{{ $curso->nombre }}</span>
                                    <input
                                        class="form-check-input js-curso-check"
                                        type="checkbox"
                                        id="curso-check-{{ $curso->id }}"
                                        name="cursos[]"
                                        value="{{ $curso->id }}"
                                        {{ in_array($curso->id, old('cursos', [])) ? 'checked' : '' }}>
                                </label>
                            @empty
                                <div class="sec-course-empty">
                                    <i data-feather="inbox" style="width:32px;height:32px;opacity:.35;margin-bottom:.5rem;"></i>
                                    <div>No hay cursos registrados.</div>
                                </div>
                            @endforelse
                        </div>

                        @error('periodo_academico_id')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        @if ($errors->has('cursos') || $errors->has('cursos.*'))
                            <div class="text-danger small mt-2">
                                {{ $errors->first('cursos') ?: $errors->first('cursos.*') }}
                            </div>
                        @endif

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="sec-btn-modal-cancel" data-bs-dismiss="modal">
                        <i data-feather="x"></i> Cancelar
                    </button>
                    <button type="submit" form="asignarCursosForm" class="sec-btn-modal-save">
                        <i data-feather="save"></i> Guardar Cursos
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editModalElement = document.getElementById('editarGradoSeccionModal');
            const editForm = document.getElementById('editarSeccionForm');
            const editSeccionId = document.getElementById('editingSeccionId');
            const editNivel = document.getElementById('editNivelAcademico');
            const editGrado = document.getElementById('editGradoNombre');
            const editSeccion = document.getElementById('editSeccionNombre');
            const editVacantes = document.getElementById('editVacantes');
            const cursosModalElement = document.getElementById('asignarCursosModal');
            const cursosForm = document.getElementById('asignarCursosForm');
            const cursosSeccionId = document.getElementById('assigningSeccionId');
            const cursosPeriodoId = document.getElementById('assigningPeriodoId');
            const cursosSeccionLabel = document.getElementById('assigningSeccionLabel');
            const cursosPeriodoLabel = document.getElementById('assigningPeriodoLabel');

            const hydrateEditForm = (button, keepCurrentValues = false) => {
                if (!button || !editForm) {
                    return;
                }

                const updateUrl = button.getAttribute('data-update-url') ?? '';
                const id = button.getAttribute('data-id') ?? '';
                const nivel = button.getAttribute('data-nivel') ?? '';
                const grado = button.getAttribute('data-grado') ?? '';
                const seccion = button.getAttribute('data-seccion') ?? '';
                const vacantes = button.getAttribute('data-vacantes') ?? '';

                if (updateUrl !== '') {
                    editForm.setAttribute('action', updateUrl);
                }

                if (editSeccionId) {
                    editSeccionId.value = id;
                }

                if (!keepCurrentValues) {
                    if (editNivel) editNivel.value = nivel;
                    if (editGrado) editGrado.value = grado;
                    if (editSeccion) editSeccion.value = seccion;
                    if (editVacantes) editVacantes.value = vacantes;
                }
            };

            const parseCursoIds = (value) => {
                if (!value) {
                    return [];
                }

                try {
                    const parsed = JSON.parse(value);
                    if (!Array.isArray(parsed)) {
                        return [];
                    }

                    return parsed.map((id) => Number(id)).filter((id) => Number.isInteger(id));
                } catch (_error) {
                    return [];
                }
            };

            const hydrateCursosForm = (button, keepCurrentValues = false) => {
                if (!button || !cursosForm) {
                    return;
                }

                const cursosUrl = button.getAttribute('data-cursos-url') ?? '';
                const seccionId = button.getAttribute('data-id') ?? '';
                const periodoId = button.getAttribute('data-periodo-id') ?? '';
                const seccionLabel = button.getAttribute('data-seccion-label') ?? '-';
                const periodoLabel = button.getAttribute('data-periodo-label') ?? '-';
                const cursoIds = parseCursoIds(button.getAttribute('data-cursos') ?? '');

                if (cursosUrl !== '') {
                    cursosForm.setAttribute('action', cursosUrl);
                }

                if (cursosSeccionId) cursosSeccionId.value = seccionId;
                if (cursosPeriodoId) cursosPeriodoId.value = periodoId;
                if (cursosSeccionLabel) cursosSeccionLabel.textContent = seccionLabel;
                if (cursosPeriodoLabel) cursosPeriodoLabel.textContent = periodoLabel;

                if (!keepCurrentValues) {
                    const selectedSet = new Set(cursoIds);
                    document.querySelectorAll('.js-curso-check').forEach((checkbox) => {
                        const checkboxValue = Number(checkbox.value);
                        checkbox.checked = selectedSet.has(checkboxValue);
                    });
                }
            };

            document.querySelectorAll('.js-sec-edit').forEach((button) => {
                button.addEventListener('click', () => {
                    hydrateEditForm(button);
                });
            });

            document.querySelectorAll('.js-sec-cursos').forEach((button) => {
                button.addEventListener('click', () => {
                    hydrateCursosForm(button);
                });
            });

            // --- Lógica del modal Agregar Grado/Sección ---
            const gradosPorNivel = @json($gradosPorNivel ?? []);
            const addNivelSelect  = document.getElementById('addNivelSelect');
            const addGradoSelect  = document.getElementById('addGradoSelect');
            const addGradoNuevo   = document.getElementById('addGradoNuevo');
            const addSeccion      = document.getElementById('addSeccionNombre');
            const addNivelHidden  = document.getElementById('addNivelHidden');
            const addNombreHidden = document.getElementById('addNombreGradoSeccionHidden');
            const agregarForm     = document.getElementById('agregarSeccionForm');

            if (addNivelSelect) {
                addNivelSelect.addEventListener('change', () => {
                    const nivel = addNivelSelect.value;
                    addNivelHidden.value = nivel;
                    addGradoSelect.innerHTML = '';
                    addGradoNuevo.classList.add('d-none');

                    if (!nivel) {
                        addGradoSelect.innerHTML = '<option value="">— primero elige nivel —</option>';
                        addGradoSelect.disabled = true;
                        return;
                    }

                    const grados = gradosPorNivel[nivel] ?? [];
                    addGradoSelect.innerHTML = '<option value="">Seleccionar grado...</option>';
                    grados.forEach(nombre => {
                        const opt = document.createElement('option');
                        opt.value = nombre;
                        opt.textContent = nombre;
                        addGradoSelect.appendChild(opt);
                    });
                    const optNuevo = document.createElement('option');
                    optNuevo.value = '__nuevo__';
                    optNuevo.textContent = '+ Nuevo grado...';
                    addGradoSelect.appendChild(optNuevo);
                    addGradoSelect.disabled = false;
                });

                addGradoSelect.addEventListener('change', () => {
                    if (addGradoSelect.value === '__nuevo__') {
                        addGradoNuevo.classList.remove('d-none');
                        addGradoNuevo.required = true;
                    } else {
                        addGradoNuevo.classList.add('d-none');
                        addGradoNuevo.required = false;
                    }
                });
            }

            if (agregarForm) {
                agregarForm.addEventListener('submit', (e) => {
                    const nivel  = addNivelSelect?.value ?? '';
                    const grado  = addGradoSelect?.value === '__nuevo__'
                        ? (addGradoNuevo?.value ?? '').trim()
                        : (addGradoSelect?.value ?? '').trim();
                    const seccion = (addSeccion?.value ?? '').trim();

                    if (!nivel || !grado || !seccion) return; // deja que la validación HTML native actúe

                    addNivelHidden.value  = nivel;
                    addNombreHidden.value = `${grado} - ${seccion}`;
                });
            }
            // --- fin lógica modal agregar ---

            const openAddModal = @json(
                $errors->has('nivel_academico')
                || $errors->has('nombre_grado_seccion')
                || $errors->has('vacantes')
            );

            const openEditModal = @json(
                $errors->has('edit_nivel_academico')
                || $errors->has('edit_grado_nombre')
                || $errors->has('edit_seccion_nombre')
                || $errors->has('edit_vacantes')
                || $errors->has('editing_seccion_id')
            );

            const openCursosModal = @json(
                $errors->has('assigning_seccion_id')
                || $errors->has('periodo_academico_id')
                || $errors->has('cursos')
                || $errors->has('cursos.*')
            );

            const openEditModalId = @json(old('editing_seccion_id'));
            const openCursosModalId = @json(old('assigning_seccion_id'));

            if (openEditModal && openEditModalId && editModalElement && window.bootstrap?.Modal) {
                const button = document.querySelector(`.js-sec-edit[data-id="${openEditModalId}"]`);
                if (button) {
                    hydrateEditForm(button, true);
                    window.bootstrap.Modal.getOrCreateInstance(editModalElement).show();
                    return;
                }
            }

            if (openCursosModal && openCursosModalId && cursosModalElement && window.bootstrap?.Modal) {
                const button = document.querySelector(`.js-sec-cursos[data-id="${openCursosModalId}"]`);
                if (button) {
                    hydrateCursosForm(button, true);
                    window.bootstrap.Modal.getOrCreateInstance(cursosModalElement).show();
                    return;
                }
            }

            if (openAddModal) {
                const addModalElement = document.getElementById('agregarGradoSeccionModal');
                if (addModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(addModalElement).show();
                }
            }
        });
    </script>
@endpush
