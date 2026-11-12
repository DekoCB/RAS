@extends('layouts.mantis')

@section('title', request()->routeIs('director.*') ? 'Gestionar Secciones' : 'Matrículas')
@section('page-title', request()->routeIs('director.*') ? 'Gestionar Secciones' : 'Matrículas')
@section('body-class',
    auth()->user()?->hasRole('SuperAdmin')
        ? 'role-superadmin'
        : (auth()->user()?->hasRole('Director') ? 'role-director' : 'role-secretaria')
)

@push('styles')
<link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@if(auth()->user()?->hasRole('Director'))
<link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
@endif
@if(auth()->user()?->hasRole('SuperAdmin'))
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endif
<style>
    .jp-soft-card {
        border: 1px solid #d7e0ea;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 26px rgba(18, 27, 82, 0.10);
    }

    .jp-soft-card .card-header {
        background: linear-gradient(125deg, #162061 0%, #18246e 58%, #1c297e 100%);
        color: #fff;
        padding: 1.2rem 1.4rem;
        border-bottom: 0;
    }

    .jp-soft-card .card-body {
        background: #fff;
    }

    .jp-table thead th {
        background: #f5f9ff;
        color: #162061;
        border-bottom: 1px solid #d2e0f2;
        font-weight: 700;
    }

    .jp-table tbody tr:hover {
        background: #f7faff;
    }

    .jp-table-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 42px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #151f5f;
        transition: all .2s ease;
    }

    .jp-table-action i {
        width: 20px;
        height: 20px;
    }

    .jp-table-action:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #161f59;
        transform: translateY(-1px);
    }

    .jp-btn-outline {
        border-color: #c7cdf6;
        color: #cfe8f3;
        background: rgba(255, 255, 255, 0.08);
    }

    .jp-btn-outline:hover {
        border-color: #445099;
        color: #e5f7ff;
        background: rgba(0, 0, 0, 0.10);
    }

    .jp-btn-primary {
        background: linear-gradient(135deg, #1c297e, #213196);
        border-color: transparent;
        color: #fff;
        font-weight: 700;
    }

    .jp-btn-primary:hover {
        background: linear-gradient(135deg, #18246e, #1d2a82);
        border-color: transparent;
        color: #fff;
    }

    .jp-chip-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 999px;
        padding: .45rem 1rem;
        font-weight: 700;
        border: 1px solid transparent !important;
        background: #fff !important;
        color: #334155 !important;
        transition: all .2s ease;
    }

    .jp-chip-btn i {
        width: 16px;
        height: 16px;
    }

    .jp-chip-btn--primary {
        background: #e2eefe !important;
        border-color: rgba(43, 58, 153, 0.45) !important;
        color: #1c297e !important;
        box-shadow: 0 10px 18px rgba(28, 41, 126, 0.2);
    }

    .jp-chip-btn--neutral {
        background: #f1f5f9 !important;
        border-color: rgba(100, 116, 139, 0.35) !important;
        color: #334155 !important;
    }

    .jp-chip-btn--danger {
        background: #ffe4e6 !important;
        border-color: rgba(239, 68, 68, 0.4) !important;
        color: #b91c1c !important;
    }

    .jp-chip-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(15, 19, 42, 0.12);
    }

    .jp-chip-btn:disabled {
        opacity: 1;
        cursor: not-allowed;
    }

    .jp-chip-btn--neutral:disabled {
        background: #f1f5f9 !important;
        border-color: rgba(100, 116, 139, 0.35) !important;
        color: #334155 !important;
    }

    .jp-chip-btn--danger:disabled {
        background: #ffe4e6 !important;
        border-color: rgba(239, 68, 68, 0.4) !important;
        color: #b91c1c !important;
    }

    .jp-btn-modal-cancel {
        background: #e5e7eb;
        border-color: #d1d5db;
        color: #475569;
        border-radius: 12px;
        font-weight: 700;
        padding: .55rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .jp-btn-modal-cancel:hover {
        background: #dbe0e7;
        color: #334155;
    }

    .jp-btn-modal-add {
        background: linear-gradient(135deg, #16a34a, #22c55e);
        border: 0;
        color: #fff;
        border-radius: 12px;
        font-weight: 700;
        padding: .55rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        box-shadow: 0 12px 20px rgba(34, 197, 94, 0.25);
    }

    .jp-btn-modal-add:hover {
        background: linear-gradient(135deg, #15803d, #22c55e);
        color: #fff;
    }

    .jp-status-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .85rem;
        border-radius: 999px;
        font-weight: 800;
        font-size: .72rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        background: #ecfdf3;
        color: #15803d;
        border: 1px solid rgba(34, 197, 94, 0.35);
    }

    .jp-status-badge i {
        width: 14px;
        height: 14px;
    }

    .jp-manage-modal .modal-content,
    .jp-add-modal .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1rem 3rem rgba(21, 31, 94, 0.35);
    }

    .jp-manage-modal .modal-header,
    .jp-add-modal .modal-header {
        border-bottom: 0;
        padding: 1rem 1.25rem;
        background: linear-gradient(120deg, #172268 0%, #192471 100%);
        color: #fff;
    }

    .jp-manage-modal .modal-title {
        color: #ffffff !important;
    }

    .jp-manage-modal .modal-title i {
        color: #ffffff !important;
    }

    .jp-vacantes-pill {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .85rem;
        border-radius: 999px;
        font-weight: 800;
        font-size: .72rem;
        background: #ecfdf3;
        color: #15803d;
        border: 1px solid rgba(34, 197, 94, 0.35);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .jp-vacantes-pill i {
        width: 14px;
        height: 14px;
    }

    .jp-manage-modal .btn-close,
    .jp-add-modal .btn-close {
        filter: invert(1) grayscale(100%);
        opacity: .9;
    }

    .jp-values-pill {
        display: inline-block;
        background: linear-gradient(135deg, #1c297e, #213196);
        color: #fff;
        border-radius: 999px;
        padding: .55rem 1rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .jp-metrics-card {
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 14px;
        padding: .9rem 1rem;
        margin-bottom: 1rem;
        color: #fff;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(2px);
    }

    .jp-metrics-wrap {
        background: linear-gradient(120deg, #172268 0%, #1b287b 100%);
        border-radius: 14px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .jp-add-form {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1rem;
        background: #fff;
    }

    .jp-add-form .form-label {
        font-weight: 600;
        color: #374151;
    }

    .jp-add-form .form-control,
    .jp-add-form .form-select {
        border-radius: 10px;
        border-color: #cbd5e1;
        min-height: 48px;
    }

    .jp-add-form .btn-cancel {
        background: #6b7280;
        border-color: #6b7280;
        color: #fff;
        min-width: 140px;
    }

    .jp-add-form .btn-add {
        background: #17b26a;
        border-color: #17b26a;
        color: #fff;
        min-width: 140px;
    }

    .jp-manage-modal .nav-tabs .nav-link.active {
        color: #172268;
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: 700;
    }

    .jp-manage-modal .nav-tabs .nav-link {
        color: #172268;
        font-weight: 600;
    }

    .jp-manage-modal .nav-tabs .nav-link:not(.active):hover {
        color: #161f59;
    }

    .jp-manage-modal .table thead th {
        white-space: nowrap;
        background: linear-gradient(120deg, #172268 0%, #192471 100%);
        color: #fff;
        border-bottom: 0;
    }

    .jp-manage-modal .badge {
        font-size: .8rem;
    }

    /* ── Tabs de nivel académico ──────────────────────── */
    .jp-nivel-tabs { border-bottom: 0; margin-bottom: 1.35rem; flex-wrap: wrap; gap: .35rem; }
    .jp-nivel-tabs .nav-link {
        border-radius: 999px;
        padding: .45rem 1.2rem;
        font-weight: 700;
        font-size: .83rem;
        color: #17226a;
        border: 1.5px solid #b8c1fd;
        background: #ebfaff;
        transition: all .18s ease;
    }
    .jp-nivel-tabs .nav-link.active {
        background: linear-gradient(135deg, #1c297e 0%, #1f2e8e 100%);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 6px 14px rgba(28, 41, 126,.30);
    }
    .jp-nivel-tabs .nav-link:not(.active):hover {
        background: #c7effe;
        border-color: #23339f;
        color: #1b287b;
    }

    /* ── Tabla de secciones ───────────────────────────── */
    .jp-seccion-table { border-radius: 14px; overflow: hidden; border: 1px solid #b8c1fd; }
    .jp-seccion-table thead th {
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
    .jp-seccion-table tbody td {
        padding: .65rem 1rem;
        vertical-align: middle;
        font-size: .88rem;
        border-bottom: 1px solid #eef9fe;
    }
    .jp-seccion-table tbody tr:last-child td { border-bottom: 0; }
    .jp-seccion-table tbody tr:hover td { background: #ebfaff; }
    .jp-seccion-id {
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
        letter-spacing: .02em;
    }

    /* ── Vacantes pills ───────────────────────────────── */
    .jp-vac-ok {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .28rem .75rem; border-radius: 999px;
        font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
        background: #ecfdf5; color: #15803d; border: 1px solid rgba(34,197,94,.3);
    }
    .jp-vac-ok i { width: 12px; height: 12px; }
    .jp-vac-nd {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .28rem .75rem; border-radius: 999px;
        font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
        background: #f1f5f9; color: #64748b; border: 1px solid rgba(100,116,139,.25);
    }
    .jp-vac-nd i { width: 12px; height: 12px; }

    /* ── Botones de acción en tabla ───────────────────── */
    .jp-sec-btn-edit {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .35rem .85rem; border-radius: 10px;
        border: 1.5px solid #e2e8f0; background: #f8fafc; color: #475569;
        font-size: .78rem; font-weight: 700; transition: all .18s ease; cursor: pointer;
    }
    .jp-sec-btn-edit:hover:not(:disabled) { background: #f1f5f9; border-color: #cbd5e1; }
    .jp-sec-btn-edit:disabled { opacity: .5; cursor: not-allowed; }
    .jp-sec-btn-del {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .35rem .85rem; border-radius: 10px;
        border: 1.5px solid #cae0fe; background: #f5f9ff; color: #1c297e;
        font-size: .78rem; font-weight: 700; transition: all .18s ease; cursor: pointer;
    }
    .jp-sec-btn-del:hover:not(:disabled) { background: #e2eefe; border-color: #c8cffc; }
    .jp-sec-btn-del:disabled { opacity: .5; cursor: not-allowed; }
    .jp-sec-btn-edit i, .jp-sec-btn-del i { width: 13px; height: 13px; }

    /* ── Barra de valores institucionales ─────────────── */
    .jp-inst-bar {
        background: linear-gradient(120deg, #162061 0%, #1c297e 50%, #1f2e8e 100%);
        border-radius: 14px;
        padding: .8rem 1.2rem;
        display: flex; align-items: center; gap: .6rem;
        margin-bottom: 1.4rem;
        color: #fff; font-weight: 700; font-size: .85rem;
        box-shadow: 0 8px 20px rgba(28, 41, 126,.28);
        position: relative; overflow: hidden;
    }
    .jp-inst-bar::after {
        content: '';
        position: absolute; right: -20px; top: -20px;
        width: 90px; height: 90px;
        background: rgba(255,255,255,.08); border-radius: 50%;
        pointer-events: none;
    }
    .jp-inst-bar i { width: 15px; height: 15px; flex-shrink: 0; }
    .jp-inst-bar span { opacity: .88; font-weight: 400; font-size: .78rem; }

    /* ── Botón agregar sección ───────────────────────── */
    .jp-btn-add-seccion {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .5rem 1.2rem;
        background: linear-gradient(135deg, #1f2e8e, #1b287b);
        color: #fff; border: 0; border-radius: 12px;
        font-weight: 700; font-size: .85rem;
        box-shadow: 0 8px 16px rgba(31, 46, 142,.30);
        transition: all .18s ease;
    }
    .jp-btn-add-seccion:hover { background: linear-gradient(135deg,#1b287b,#17226a); color:#fff; transform:translateY(-1px); box-shadow: 0 10px 20px rgba(31, 46, 142,.35); }
    .jp-btn-add-seccion i { width: 15px; height: 15px; }

    /* ── Formulario agregar ─────────────────────────── */
    .jp-agregar-field label {
        font-size: .73rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #64748b; margin-bottom: .35rem;
        display: flex; align-items: center; gap: .35rem;
    }
    .jp-agregar-field label i { width: 13px; height: 13px; color: #94a3b8; }
    .jp-agregar-field .form-control,
    .jp-agregar-field .form-select {
        border-radius: 12px; border: 1.5px solid #e2e8f0;
        min-height: 44px; font-size: .88rem; background: #fafafa;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .jp-agregar-field .form-control:focus,
    .jp-agregar-field .form-select:focus {
        border-color: #1f2e8e; box-shadow: 0 0 0 3px rgba(31, 46, 142,.15); background: #fff;
    }
    .jp-add-modal .modal-header h5 { font-size: 1rem; }
    .jp-btn-agregar-guardar {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .55rem 1.3rem;
        background: linear-gradient(135deg, #1c297e, #1f2e8e);
        color: #fff; border: 0; border-radius: 12px; font-weight: 700;
        box-shadow: 0 8px 16px rgba(28, 41, 126,.25); transition: all .18s ease;
    }
    .jp-btn-agregar-guardar:hover { background: linear-gradient(135deg,#18246e,#1b287b); color:#fff; transform: translateY(-1px); }
    .jp-btn-agregar-guardar:disabled { opacity: .6; cursor: not-allowed; }
    .jp-btn-agregar-guardar i { width: 15px; height: 15px; }
    .jp-btn-agregar-cancel {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .55rem 1.1rem; background: #f1f5f9; color: #475569;
        border: 1.5px solid #e2e8f0; border-radius: 12px; font-weight: 700;
        transition: all .18s ease;
    }
    .jp-btn-agregar-cancel:hover { background: #e2e8f0; color: #334155; }
    .jp-btn-agregar-cancel i { width: 15px; height: 15px; }

    /* ── Empty state en modal ───────────────────────── */
    .jp-modal-empty {
        text-align: center; padding: 2.5rem 1rem; color: #94a3b8;
    }
    .jp-modal-empty i { width: 40px; height: 40px; opacity: .4; }
    .jp-modal-empty p { margin-top: .75rem; font-size: .88rem; }
</style>
@endpush

@section('sidebar')
    @if (auth()->user()?->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @elseif (auth()->user()?->hasRole('Director'))
        @include('director.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
@php
    $esAdmin = auth()->user()?->hasRole('SuperAdmin');
    $esDirectorVista = request()->routeIs('director.*') && auth()->user()?->hasRole('Director');
    $rpM = $esAdmin ? 'admin-sistema.' : 'secretaria.';
    $indexRouteName = $esDirectorVista ? 'director.secciones.index' : $rpM . 'matriculas.index';
    $guardarSeccionRouteName = $esDirectorVista ? 'director.secciones.guardar' : $rpM . 'matriculas.secciones.guardar';
    $nuevaMatriculaRouteName = $esDirectorVista ? null : $rpM . 'matriculas.paso1';
    $resumenRouteName = $esDirectorVista ? null : $rpM . 'matriculas.resumen';

    $matriculasIndexRoute = Route::has($indexRouteName)
        ? route($indexRouteName)
        : route('secretaria.matriculas.index');

    $guardarSeccionRoute = Route::has($guardarSeccionRouteName)
        ? route($guardarSeccionRouteName)
        : null;

    $nuevaMatriculaRoute = $nuevaMatriculaRouteName && Route::has($nuevaMatriculaRouteName)
        ? route($nuevaMatriculaRouteName)
        : null;

    $mostrarResumenAccion = $resumenRouteName && Route::has($resumenRouteName);

    $nivelesAcademicos = collect(['Inicial', 'Primaria', 'Secundaria'])
        ->merge(($seccionesPorNivel ?? collect())->keys())
        ->unique()
        ->values();

    $totalMatriculas = $matriculas->total();
@endphp

<div class="jp-attendance-wrap">

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-1 mb-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold" style="color:#162061;font-size:1.05rem;">
                {{ $esDirectorVista ? 'Gestionar Secciones' : 'Matrículas' }}
            </h5>
            <span class="badge" style="background:#e2eefe;color:#1c297e;font-size:.78rem;font-weight:700;border-radius:999px;padding:.3rem .75rem;">
                {{ $matriculas->total() }} resultado{{ $matriculas->total() !== 1 ? 's' : '' }}
            </span>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @if(!$esDirectorVista && Route::has($rpM . 'matriculas.exportar'))
                <a href="{{ route($rpM . 'matriculas.exportar', request()->only(['dni','grado_id','seccion_id','nivel'])) }}"
                   class="jp-docente-history-btn"
                   style="background:linear-gradient(135deg,#15803d,#22c55e);color:#fff;border:none;">
                    <i data-feather="download"></i>
                    Exportar Excel
                </a>
            @endif
            <button type="button" class="jp-docente-history-btn is-success" data-bs-toggle="modal"
                data-bs-target="#gestionarSeccionesModal">
                <i data-feather="sliders"></i>
                Gestionar Secciones
            </button>
            @if ($nuevaMatriculaRoute)
                <a href="{{ $nuevaMatriculaRoute }}" class="jp-docente-history-btn">
                    <i data-feather="plus"></i>
                    Nueva Matrícula
                </a>
            @endif
        </div>
    </div>

    {{-- ── Filtros ────────────────────────────────────────────────── --}}
    <div class="card jp-search-card">
        <div class="card-body">
            <form method="GET" action="{{ $matriculasIndexRoute }}" id="filtrosForm">
                <input type="hidden" name="nivel" value="{{ $nivel ?? '' }}">
                <div class="row g-2 align-items-end">
                    {{-- DNI --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="search" style="width:12px;height:12px;"></i>
                            Buscar por DNI
                        </label>
                        <div class="input-group jp-search-input">
                            <span class="input-group-text"><i data-feather="search"></i></span>
                            <input type="text" name="dni" class="form-control" placeholder="Nº de DNI..."
                                value="{{ request('dni') }}">
                        </div>
                    </div>
                    {{-- Grado --}}
                    @if(!$esDirectorVista && isset($grados))
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="book" style="width:12px;height:12px;"></i>
                            Nivel / Grado
                        </label>
                        <select name="grado_id" class="form-select" style="border-radius:10px;min-height:44px;"
                                onchange="this.form.submit()">
                            <option value="">— Todos los grados —</option>
                            @foreach(($grados ?? collect())->groupBy('nivel') as $nivelGrupo => $gradosNivel)
                                <optgroup label="{{ $nivelGrupo }}">
                                    @foreach($gradosNivel as $grado)
                                        <option value="{{ $grado->id }}" @selected(request('grado_id') == $grado->id)>
                                            {{ $grado->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    {{-- Sección --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="grid" style="width:12px;height:12px;"></i>
                            Sección
                        </label>
                        <select name="seccion_id" class="form-select" style="border-radius:10px;min-height:44px;"
                                onchange="this.form.submit()">
                            <option value="">— Todas las secciones —</option>
                            @foreach($seccionesParaFiltro ?? [] as $sec)
                                <option value="{{ $sec->id }}" @selected(request('seccion_id') == $sec->id)>
                                    {{ $sec->grado?->nombre }} {{ $sec->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    {{-- Botones --}}
                    <div class="col-md-{{ $esDirectorVista ? 9 : 3 }} d-flex gap-2 justify-content-end">
                        <button class="btn jp-btn-search" type="submit">
                            <i data-feather="search" style="width:15px;height:15px;"></i>
                            Buscar
                        </button>
                        @if(request()->hasAny(['dni','grado_id','seccion_id']))
                            @php
                                $limpiarUrl = $nivel
                                    ? $matriculasIndexRoute . '?nivel=' . $nivel
                                    : $matriculasIndexRoute;
                            @endphp
                            <a href="{{ $limpiarUrl }}" class="btn btn-light rounded-pill px-3 d-flex align-items-center gap-1 small fw-bold">
                                <i data-feather="x" style="width:14px;height:14px;"></i>
                                Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tabs de nivel ───────────────────────────────────────── --}}
    @if(!$esDirectorVista)
    <div class="jp-nivel-tabs">
        @php
            $tabsNivel = [
                ''           => ['label' => 'Todos',       'icon' => 'users'],
                'Inicial'    => ['label' => 'Inicial',     'icon' => 'star'],
                'Primaria'   => ['label' => 'Primaria',    'icon' => 'book'],
                'Secundaria' => ['label' => 'Secundaria',  'icon' => 'award'],
            ];
            $conteosPorNivel = $conteosPorNivel ?? [];
        @endphp
        @foreach ($tabsNivel as $tabNivel => $tab)
            @php
                $isActive  = ($nivel ?? '') === $tabNivel;
                $tabParams = array_filter([
                    'nivel'     => $tabNivel ?: null,
                    'dni'       => request('dni') ?: null,
                    'grado_id'  => request('grado_id') ?: null,
                    'seccion_id'=> request('seccion_id') ?: null,
                ]);
                $tabUrl  = $matriculasIndexRoute . ($tabParams ? '?' . http_build_query($tabParams) : '');
                $tabCount = $tabNivel === ''
                    ? array_sum($conteosPorNivel)
                    : ($conteosPorNivel[$tabNivel] ?? 0);
            @endphp
            <a href="{{ $tabUrl }}" class="jp-nivel-tab {{ $isActive ? 'is-active' : '' }}">
                <i data-feather="{{ $tab['icon'] }}"></i>
                {{ $tab['label'] }}
                <span class="jp-nivel-tab-badge">{{ $tabCount }}</span>
            </a>
        @endforeach
    </div>
    @endif

    {{-- ── Tabla de matrículas ─────────────────────────────────── --}}
    <div class="card jp-attendance-card">
        <div class="table-responsive">
            <table class="table jp-attendance-table mb-0">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Estudiante</th>
                        <th>Nivel/Grado</th>
                        <th>Sección</th>
                        <th>Periodo</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        @if(!$esDirectorVista)
                            <th>Registrado por</th>
                        @endif
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matriculas as $m)
                        <tr>
                            <td>
                                <span class="font-monospace small fw-semibold">{{ $m->estudiante?->dni ?? '-' }}</span>
                            </td>
                            <td>
                                <div style="font-weight:700;font-size:.88rem;">{{ $m->estudiante?->nombres }} {{ $m->estudiante?->apellidos }}</div>
                            </td>
                            <td>{{ $m->grado?->nombre ?? '-' }}</td>
                            <td>{{ $m->seccion?->nombre ?? '-' }}</td>
                            <td>
                                <span class="small">
                                    {{ $m->periodoAcademico?->codigo ?? '-' }}
                                    @if ($m->periodoAcademico?->nombre)
                                        — {{ $m->periodoAcademico->nombre }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="small text-muted">
                                    {{ $m->fecha_matricula ? \Illuminate\Support\Carbon::parse($m->fecha_matricula)->format('d/m/Y') : ($m->created_at?->format('d/m/Y') ?? '-') }}
                                </span>
                            </td>
                            <td>
                                <span class="jp-status-badge">
                                    <i data-feather="check-circle"></i>
                                    {{ strtoupper($m->estado ?? 'registrada') }}
                                </span>
                            </td>
                            @if(!$esDirectorVista)
                                <td>
                                    @if($m->registradoPor)
                                        <div style="display:flex;align-items:center;gap:.5rem;">
                                            <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#162061,#1c297e);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:#fff;flex-shrink:0;">
                                                {{ strtoupper(substr($m->registradoPor->name, 0, 1)) }}
                                            </div>
                                            <span class="small fw-semibold" style="max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $m->registradoPor->name }}">
                                                {{ $m->registradoPor->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                @if ($mostrarResumenAccion)
                                    <button type="button" class="btn jp-table-action jp-matricula-resumen"
                                        data-bs-toggle="modal" data-bs-target="#matriculaResumenModal"
                                        data-url="{{ route($resumenRouteName, $m) }}">
                                        <i data-feather="eye"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $esDirectorVista ? 8 : 9 }}" class="text-center text-muted py-4">
                                <i data-feather="inbox" style="width:36px;height:36px;opacity:.3;display:block;margin:0 auto 8px;"></i>
                                No hay matrículas que coincidan con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-0">
            {{ $matriculas->links() }}
        </div>
    </div>

    {{-- ── Pre-matrículas (lo que llega del formulario web de Admisión) ── --}}
    @if(!$esDirectorVista)
        @include('secretaria.matriculas._pre_matriculas')
    @endif
</div>

<div class="modal fade jp-info-modal" id="matriculaResumenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i data-feather="file-text"></i>
                    Resumen de Matr&iacute;cula
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="jp-info-loading">Cargando resumen...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="credPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="credPasswordForm">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="lock"></i>
                        Cambiar contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="credUserEmail" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" name="password" id="credPassword" minlength="8" required placeholder="Mínimo 8 caracteres">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" name="password_confirmation" id="credPasswordConfirm" minlength="8" required>
                    </div>
                    <div class="text-danger small d-none" id="credPasswordError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-white">Guardar contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade jp-manage-modal" id="gestionarSeccionesModal" tabindex="-1" aria-labelledby="gestionarSeccionesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gestionarSeccionesModalLabel">
                    <i data-feather="settings"></i>
                    Gestionar Secciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Barra institucional --}}
                <div class="jp-inst-bar">
                    <i data-feather="award"></i>
                    <strong>R.A.S</strong>
                    <span>&nbsp;·&nbsp; Valores: Estudio · Trabajo · Disciplina</span>
                </div>

                {{-- Título + botón agregar --}}
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                    <div>
                        <h5 class="fw-800 mb-0" style="color:#1e293b;">Grados y Secciones</h5>
                        <p class="text-muted small mb-0 mt-1">
                            Administra los grados, secciones y vacantes disponibles por nivel académico.
                        </p>
                    </div>
                    <button type="button" class="jp-btn-add-seccion"
                        data-bs-target="#agregarGradoSeccionModal" data-bs-toggle="modal">
                        <i data-feather="plus"></i>
                        Agregar Grado/Sección
                    </button>
                </div>

                @if (($seccionesPorNivel ?? collect())->isNotEmpty())

                    {{-- Tabs de nivel --}}
                    <ul class="nav jp-nivel-tabs" id="tabNiveles" role="tablist">
                        @foreach ($seccionesPorNivel as $nivel => $secciones)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="nivel-{{ $loop->index }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#nivel-{{ $loop->index }}"
                                    type="button"
                                    role="tab"
                                >
                                    {{ $nivel }}
                                    <span style="margin-left:.35rem;font-size:.7rem;opacity:.75;">({{ $secciones->count() }})</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Contenido por nivel --}}
                    <div class="tab-content" id="tabNivelesContent">
                        @foreach ($seccionesPorNivel as $nivel => $secciones)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                 id="nivel-{{ $loop->index }}" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table jp-seccion-table mb-0">
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
                                                    $capacidad   = $seccion->aula?->capacidad;
                                                    $matriculados = $seccion->matriculas_count ?? 0;
                                                    $vacantes    = is_null($capacidad) ? null : max($capacidad - $matriculados, 0);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span class="jp-seccion-id">#{{ $seccion->id }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-700">{{ $seccion->grado?->nombre ?? '—' }}</span>
                                                        <span class="text-muted"> — {{ $seccion->nombre }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="fw-600">{{ $matriculados }}</span>
                                                        @if(!is_null($capacidad))
                                                            <span class="text-muted small"> / {{ $capacidad }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (is_null($vacantes))
                                                            <span class="jp-vac-nd">
                                                                <i data-feather="minus-circle"></i>
                                                                Sin definir
                                                            </span>
                                                        @elseif($vacantes === 0)
                                                            <span class="jp-vac-nd" style="background:#f5f9ff;color:#1c297e;border-color:rgba(28, 41, 126,.25);">
                                                                <i data-feather="x-circle"></i>
                                                                Lleno
                                                            </span>
                                                        @else
                                                            <span class="jp-vac-ok">
                                                                <i data-feather="check-circle"></i>
                                                                {{ $vacantes }} libre(s)
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="jp-sec-btn-edit" disabled title="Próximamente">
                                                                <i data-feather="edit-2"></i> Editar
                                                            </button>
                                                            <button type="button" class="jp-sec-btn-del" disabled title="Próximamente">
                                                                <i data-feather="trash-2"></i> Eliminar
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="jp-modal-empty">
                                                            <i data-feather="inbox"></i>
                                                            <p>No hay secciones registradas en <strong>{{ $nivel }}</strong>.</p>
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
                    <div class="jp-modal-empty" style="padding:3rem;">
                        <i data-feather="layers"></i>
                        <p>No hay niveles o secciones registradas todavía.<br>
                           Usa el botón <strong>Agregar Grado/Sección</strong> para comenzar.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<div class="modal fade jp-add-modal" id="agregarGradoSeccionModal" tabindex="-1" aria-labelledby="agregarGradoSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="agregarGradoSeccionModalLabel">
                    <i data-feather="plus-circle" style="width:18px;height:18px;"></i>
                    Agregar Grado / Sección
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">

                @if (session('status'))
                    <div class="alert border-0 rounded-3 mb-3 d-flex align-items-center gap-2"
                         style="background:#ecfdf5;color:#065f46;border-left:4px solid #10b981!important;">
                        <i data-feather="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if (!$guardarSeccionRoute)
                    <div class="alert border-0 rounded-3 mb-3 d-flex align-items-center gap-2"
                         style="background:#ebfaff;color:#17226a;border-left:4px solid #22329c!important;">
                        <i data-feather="alert-triangle" style="width:16px;height:16px;flex-shrink:0;"></i>
                        La ruta para guardar secciones no está definida. Ejecuta <code>php artisan route:clear</code>.
                    </div>
                @endif

                <form method="POST" action="{{ $guardarSeccionRoute ?? '#' }}" class="row g-3">
                    @csrf

                    <div class="col-md-4 jp-agregar-field">
                        <label>
                            <i data-feather="layers"></i> Nivel Académico
                        </label>
                        <select name="nivel_academico"
                                class="form-select @error('nivel_academico') is-invalid @enderror"
                                required>
                            <option value="">Seleccionar nivel...</option>
                            @foreach ($nivelesAcademicos as $nivel)
                                <option value="{{ $nivel }}" {{ old('nivel_academico') === $nivel ? 'selected' : '' }}>
                                    {{ $nivel }}
                                </option>
                            @endforeach
                        </select>
                        @error('nivel_academico')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5 jp-agregar-field">
                        <label>
                            <i data-feather="tag"></i> Nombre del Grado / Sección
                        </label>
                        <input type="text"
                               name="nombre_grado_seccion"
                               class="form-control @error('nombre_grado_seccion') is-invalid @enderror"
                               placeholder="Ej: Primero - A"
                               value="{{ old('nombre_grado_seccion') }}"
                               required>
                        @error('nombre_grado_seccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 jp-agregar-field">
                        <label>
                            <i data-feather="users"></i> Vacantes
                        </label>
                        <input type="number"
                               name="vacantes"
                               min="1"
                               class="form-control @error('vacantes') is-invalid @enderror"
                               value="{{ old('vacantes', 30) }}"
                               required>
                        @error('vacantes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-1">
                        <button type="button" class="jp-btn-agregar-cancel"
                                data-bs-target="#gestionarSeccionesModal" data-bs-toggle="modal">
                            <i data-feather="arrow-left"></i> Volver
                        </button>
                        <button type="submit" class="jp-btn-agregar-guardar"
                                {{ $guardarSeccionRoute ? '' : 'disabled' }}>
                            <i data-feather="check"></i> Guardar Sección
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ url('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const finalizeSuccessMessage = @json(session('matricula_finalize_success'));
        if (finalizeSuccessMessage) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Matrícula registrada',
                    text: finalizeSuccessMessage,
                    confirmButtonText: 'Aceptar',
                });
            } else {
                alert(finalizeSuccessMessage);
            }
        }

        const credModal = document.getElementById('credPasswordModal');
        const credForm = document.getElementById('credPasswordForm');
        const credUserEmail = document.getElementById('credUserEmail');
        const credPassword = document.getElementById('credPassword');
        const credPasswordConfirm = document.getElementById('credPasswordConfirm');
        const credError = document.getElementById('credPasswordError');

        const showCredError = (message) => {
            if (!credError) return;
            credError.textContent = message;
            credError.classList.remove('d-none');
        };

        const clearCredError = () => {
            if (!credError) return;
            credError.textContent = '';
            credError.classList.add('d-none');
        };

        const openAddModal = @json(
            $errors->has('nivel_academico')
            || $errors->has('nombre_grado_seccion')
            || $errors->has('vacantes')
        );

        const openManageModal = @json(session('open_gestionar_secciones_modal') || $esDirectorVista);

        if (openAddModal) {
            const addModalElement = document.getElementById('agregarGradoSeccionModal');
            if (addModalElement && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(addModalElement).show();
            }
            return;
        }

        if (openManageModal) {
            const manageModalElement = document.getElementById('gestionarSeccionesModal');
            if (manageModalElement && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(manageModalElement).show();
            }
        }

        const resumenModal = document.getElementById('matriculaResumenModal');
        if (resumenModal) {
            const loadResumen = async (url, loadingMessage = 'Cargando resumen...') => {
                const body = resumenModal.querySelector('.modal-body');
                if (!body) return;

                body.innerHTML = `<div class="jp-info-loading">${loadingMessage}</div>`;
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) {
                    throw new Error('Error al cargar el resumen');
                }
                const html = await response.text();
                body.innerHTML = html;
                if (window.feather) {
                    window.feather.replace();
                }
            };

            resumenModal.addEventListener('show.bs.modal', async (event) => {
                const button = event.relatedTarget;
                const url = button?.getAttribute('data-url');
                if (!url) return;

                resumenModal.dataset.resumenUrl = url;

                try {
                    await loadResumen(url);
                } catch (error) {
                    const body = resumenModal.querySelector('.modal-body');
                    body.innerHTML = '<div class="text-danger">No se pudo cargar el resumen.</div>';
                }
            });

            resumenModal.addEventListener('click', async (event) => {
                const target = event.target;
                const resetButton = target instanceof Element
                    ? target.closest('.jp-cred-reset')
                    : null;
                if (!resetButton) return;

                event.preventDefault();
                const url = resetButton.getAttribute('data-url');
                if (!url) return;

                if (!confirm('Se generará una nueva contraseña temporal. ¿Deseas continuar?')) {
                    return;
                }

                resetButton.setAttribute('disabled', 'disabled');

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token ?? '',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Error al actualizar las credenciales');
                    }

                    const resumenUrl = resumenModal.dataset.resumenUrl;
                    if (resumenUrl) {
                        await loadResumen(resumenUrl, 'Actualizando credenciales...');
                    }
                } catch (error) {
                    alert('No se pudo generar la nueva contraseña.');
                } finally {
                    resetButton.removeAttribute('disabled');
                }
            });

            resumenModal.addEventListener('click', (event) => {
                const target = event.target;
                const customButton = target instanceof Element
                    ? target.closest('.jp-cred-custom')
                    : null;
                if (!customButton || !credModal || !credForm) return;

                event.preventDefault();
                const url = customButton.getAttribute('data-url');
                const email = customButton.getAttribute('data-email') || '';
                if (!url) return;

                credForm.dataset.url = url;
                if (credUserEmail) {
                    credUserEmail.value = email;
                }
                if (credPassword) {
                    credPassword.value = '';
                }
                if (credPasswordConfirm) {
                    credPasswordConfirm.value = '';
                }
                clearCredError();

                if (window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(credModal).show();
                }
            });

            if (credForm) {
                credForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearCredError();

                    const url = credForm.dataset.url;
                    if (!url) return;

                    const password = credPassword?.value ?? '';
                    const passwordConfirm = credPasswordConfirm?.value ?? '';

                    if (!password || password.length < 8) {
                        showCredError('La contraseña debe tener al menos 8 caracteres.');
                        return;
                    }

                    if (password !== passwordConfirm) {
                        showCredError('Las contraseñas no coinciden.');
                        return;
                    }

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const formData = new FormData(credForm);
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': token ?? '',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        if (!response.ok) {
                            const data = await response.json().catch(() => null);
                            const message = data?.message
                                || data?.errors?.password?.[0]
                                || 'No se pudo actualizar la contraseña.';
                            throw new Error(message);
                        }

                        if (window.bootstrap?.Modal) {
                            window.bootstrap.Modal.getOrCreateInstance(credModal).hide();
                        }

                        const resumenUrl = resumenModal.dataset.resumenUrl;
                        if (resumenUrl) {
                            await loadResumen(resumenUrl, 'Actualizando credenciales...');
                        }
                    } catch (error) {
                        showCredError(error?.message || 'No se pudo actualizar la contraseña.');
                    }
                });
            }
        }
    });
</script>
@endpush
