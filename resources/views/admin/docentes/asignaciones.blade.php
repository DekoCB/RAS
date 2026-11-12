@extends('layouts.mantis')

@section('title', 'Asignación Docente | R.A.S')
@section('page-title', 'Asignación Docente')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ── HERO ── */
        .asig-hero {
            background: linear-gradient(135deg, #223299 0%, #1f2d8b 45%, #1c297d 100%);
            border-radius: 18px; padding: 28px 36px; color: #fff;
            position: relative; overflow: hidden;
            box-shadow: 0 10px 40px rgba(29, 43, 133,.4); margin-bottom: 24px;
        }
        .asig-hero::before {
            content: ''; position: absolute; right: -30px; top: -50px;
            width: 230px; height: 230px;
            background: radial-gradient(circle, rgba(50, 65, 161,.18) 0%, transparent 70%);
            border-radius: 50%;
        }
        .asig-hero::after {
            content: ''; position: absolute; right: 80px; bottom: -60px;
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
            border-radius: 50%;
        }
        .asig-hero-inner { position: relative; z-index: 1; }
        .asig-hero-pill {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(50, 65, 161,.22); border: 1px solid rgba(50, 65, 161,.5);
            color: #c2caff; font-size: .72rem; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase;
            padding: 5px 16px; border-radius: 20px; margin-bottom: 12px;
        }
        .asig-hero-pill svg { width: 13px; height: 13px; }
        .asig-hero h4 { font-size: 1.6rem; font-weight: 800; margin: 0 0 5px; color: #fff; }
        .asig-hero p  { color: rgba(255,255,255,.68); font-size: .88rem; margin: 0; }

        /* ── ALERT SUCCESS ── */
        .asig-alert-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac; border-radius: 11px;
            padding: 14px 20px; color: #166534;
            display: flex; align-items: center; gap: 10px;
            font-weight: 600; margin-bottom: 22px;
        }
        .asig-alert-success svg { width: 18px; height: 18px; color: #16a34a; flex-shrink: 0; }

        /* ── FILTER CARD ── */
        .asig-filter-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 3px 16px rgba(0,0,0,.08);
            overflow: hidden; margin-bottom: 24px;
        }
        .asig-filter-header {
            background: linear-gradient(135deg, #202f91 0%, #1c297d 100%);
            padding: 14px 24px;
            display: flex; align-items: center; gap: 9px;
        }
        .asig-filter-header h6 {
            color: #fff; margin: 0; font-weight: 700; font-size: .95rem;
            display: flex; align-items: center; gap: 8px;
        }
        .asig-filter-header h6 svg { width: 16px; height: 16px; color: #c2caff; }
        .asig-filter-body { padding: 22px 24px; }
        .asig-filter-label {
            font-size: .8rem; font-weight: 700; color: #374151;
            margin-bottom: 6px; display: block;
        }
        .asig-filter-body .form-select {
            border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .87rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .asig-filter-body .form-select:focus {
            border-color: #23339e; box-shadow: 0 0 0 3px rgba(35, 51, 158,.15);
        }
        .asig-btn-cargar {
            background: linear-gradient(135deg, #23339e, #1d2a82);
            color: #fff; border: none; padding: 10px 24px; border-radius: 9px;
            font-size: .88rem; font-weight: 700; width: 100%;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 14px rgba(35, 51, 158,.38); cursor: pointer; transition: all .22s;
        }
        .asig-btn-cargar:hover { transform: translateY(-2px); box-shadow: 0 7px 20px rgba(35, 51, 158,.5); }
        .asig-btn-cargar svg { width: 16px; height: 16px; }

        /* ── STATS ROW ── */
        .asig-stat {
            background: #fff; border-radius: 13px; padding: 18px 20px;
            box-shadow: 0 3px 12px rgba(0,0,0,.07);
            border-left: 5px solid #202f91;
            display: flex; align-items: center; gap: 14px;
        }
        .asig-stat.green { border-left-color: #16a34a; }
        .asig-stat.amber { border-left-color: #1f2e8e; }
        .asig-stat-icon {
            width: 44px; height: 44px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(32, 47, 145,.11); flex-shrink: 0;
        }
        .asig-stat.green .asig-stat-icon { background: rgba(22,163,74,.1); }
        .asig-stat.amber .asig-stat-icon { background: rgba(31, 46, 142,.1); }
        .asig-stat-icon svg { width: 20px; height: 20px; color: #202f91; }
        .asig-stat.green .asig-stat-icon svg { color: #16a34a; }
        .asig-stat.amber .asig-stat-icon svg { color: #1f2e8e; }
        .asig-stat-num   { font-size: 1.6rem; font-weight: 800; line-height: 1; color: #1e293b; }
        .asig-stat-label { font-size: .76rem; color: #64748b; margin-top: 3px; font-weight: 500; }

        /* ── RESUMEN CARD ── */
        .asig-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 3px 16px rgba(0,0,0,.08); overflow: hidden; margin-bottom: 24px;
        }
        .asig-card-header {
            background: linear-gradient(135deg, #202f91 0%, #1c297d 100%);
            padding: 16px 24px;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
        }
        .asig-card-header h6 {
            color: #fff; margin: 0; font-weight: 700; font-size: 1rem;
            display: flex; align-items: center; gap: 8px;
        }
        .asig-card-header h6 svg { width: 16px; height: 16px; color: #c2caff; }
        .asig-card-sub { color: rgba(255,255,255,.7); font-size: .78rem; margin-top: 2px; }

        /* Search box in header */
        .asig-search-wrap { display: flex; align-items: center; gap: 8px; }
        .asig-search-input {
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.35);
            color: #fff; border-radius: 8px; padding: 7px 14px; font-size: .83rem;
            outline: none; transition: background .2s; width: 190px;
        }
        .asig-search-input::placeholder { color: rgba(255,255,255,.6); }
        .asig-search-input:focus { background: rgba(255,255,255,.22); }
        .asig-btn-buscar {
            background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.4);
            color: #fff; border-radius: 8px; padding: 7px 16px;
            font-size: .82rem; font-weight: 700; cursor: pointer; transition: all .2s;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .asig-btn-buscar:hover { background: rgba(255,255,255,.3); }
        .asig-btn-buscar svg { width: 13px; height: 13px; }

        /* ── TABLE ── */
        .asig-table { width: 100%; border-collapse: collapse; }
        .asig-table thead tr { background: #edf7ff; border-bottom: 2px solid #ccd2fe; }
        .asig-table thead th {
            padding: 13px 18px; font-size: .74rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .07em; color: #18246e; white-space: nowrap;
        }
        .asig-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
        .asig-table tbody tr:last-child { border-bottom: none; }
        .asig-table tbody tr:hover { background: #f0f9ff; }
        .asig-table td { padding: 14px 18px; font-size: .875rem; color: #334155; vertical-align: middle; }

        .asig-docente-name {
            font-weight: 700; color: #1e293b;
            display: flex; align-items: center; gap: 8px;
        }
        .asig-docente-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, #23339e, #213196);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: .78rem; flex-shrink: 0;
        }
        .asig-total-num {
            background: #edf7ff; color: #1d2a82;
            font-weight: 800; font-size: .85rem;
            padding: 3px 12px; border-radius: 20px;
            border: 1px solid #ccd2fe; display: inline-block;
        }
        .asig-curso-pill {
            display: inline-block; padding: 3px 10px; border-radius: 6px;
            font-size: .75rem; font-weight: 600;
            background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;
        }
        .asig-seccion-pill {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: .74rem; font-weight: 700;
            background: linear-gradient(135deg, #edf7ff, #ccebff);
            color: #18246e; border: 1px solid #ccd2fe;
        }

        /* ── EMPTY TABLE ── */
        .asig-empty { text-align: center; padding: 48px 20px; color: #94a3b8; }
        .asig-empty svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: .35; display: block; margin-inline: auto; }
        .asig-empty p { font-size: .88rem; margin: 0; }

        /* ── SECTION SELECTED BANNER ── */
        .asig-info-banner {
            background: linear-gradient(135deg, #edf8ff, #d5f0ff);
            border: 1px solid #ccd2fe; border-radius: 12px;
            padding: 16px 20px; display: flex; align-items: flex-start; gap: 14px;
            margin-bottom: 22px;
        }
        .asig-info-banner-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, #23339e, #213196);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .asig-info-banner-icon svg { width: 18px; height: 18px; color: #fff; }
        .asig-info-banner-title { font-weight: 700; color: #18246e; font-size: .9rem; margin-bottom: 3px; }
        .asig-info-banner-meta  { font-size: .82rem; color: #1d2a82; }

        /* ── ASSIGNMENT FORM TABLE ── */
        .asig-assign-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 3px 16px rgba(0,0,0,.08); overflow: hidden;
        }
        .asig-assign-header {
            background: linear-gradient(135deg, #26369f 0%, #1c297d 100%);
            padding: 16px 24px;
            display: flex; align-items: center; gap: 9px;
        }
        .asig-assign-header h6 {
            color: #fff; margin: 0; font-weight: 700; font-size: .95rem;
            display: flex; align-items: center; gap: 8px;
        }
        .asig-assign-header h6 svg { width: 16px; height: 16px; color: #c2caff; }
        .asig-assign-body { padding: 24px; }

        .asig-assign-table { width: 100%; border-collapse: collapse; }
        .asig-assign-table thead tr { background: #edf7ff; border-bottom: 2px solid #ccd2fe; }
        .asig-assign-table thead th {
            padding: 12px 18px; font-size: .74rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .07em; color: #18246e;
        }
        .asig-assign-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
        .asig-assign-table tbody tr:last-child { border-bottom: none; }
        .asig-assign-table tbody tr:hover { background: #f0f9ff; }
        .asig-assign-table td { padding: 13px 18px; vertical-align: middle; }
        .asig-curso-nombre { font-weight: 600; color: #1e293b; font-size: .88rem; }
        .asig-assign-table .form-select {
            border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .85rem;
            max-width: 320px; transition: border-color .2s, box-shadow .2s;
        }
        .asig-assign-table .form-select:focus {
            border-color: #23339e; box-shadow: 0 0 0 3px rgba(35, 51, 158,.15);
        }

        .asig-assign-footer {
            padding: 18px 24px; border-top: 1px solid #f1f5f9; background: #fafafa;
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .asig-btn-guardar {
            background: linear-gradient(135deg, #23339e, #1d2a82);
            color: #fff; border: none; padding: 11px 28px; border-radius: 10px;
            font-size: .88rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 14px rgba(35, 51, 158,.4); cursor: pointer; transition: all .22s;
        }
        .asig-btn-guardar:hover { transform: translateY(-2px); box-shadow: 0 7px 20px rgba(35, 51, 158,.55); }
        .asig-btn-guardar svg { width: 16px; height: 16px; }

        /* ── WARNING / INFO ── */
        .asig-warn {
            background: #ebfaff; border: 1px solid #b8c1fd; border-radius: 11px;
            padding: 14px 20px; color: #17226a; display: flex; align-items: center; gap: 10px;
            font-size: .87rem; font-weight: 500; margin-bottom: 22px;
        }
        .asig-warn svg { width: 18px; height: 18px; color: #1f2e8e; flex-shrink: 0; }
        .asig-tip {
            background: #edf8ff; border: 1px solid #ccd2fe; border-radius: 11px;
            padding: 14px 20px; color: #18246e; display: flex; align-items: center; gap: 10px;
            font-size: .87rem; font-weight: 500;
        }
        .asig-tip svg { width: 18px; height: 18px; color: #23339e; flex-shrink: 0; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- ── HERO ── --}}
    <div class="asig-hero">
        <div class="asig-hero-inner">
            <div class="asig-hero-pill">
                <i data-feather="user-check"></i>
                Académico
            </div>
            <h4>Asignación Docente</h4>
            <p>Asigna docentes a los cursos de cada sección por año escolar y periodo académico.</p>
        </div>
    </div>

    {{-- ── ALERT ── --}}
    @if (session('status'))
        <div class="asig-alert-success">
            <i data-feather="check-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    {{-- ── FILTER CARD ── --}}
    <div class="asig-filter-card">
        <div class="asig-filter-header">
            <h6><i data-feather="sliders"></i> Filtros de búsqueda</h6>
        </div>
        <div class="asig-filter-body">
            <form method="GET" action="{{ route('admin-sistema.asignacion-docente.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="asig-filter-label">Año escolar</label>
                        <select name="anio_escolar_id" class="form-select">
                            @foreach ($anios as $anioItem)
                                <option value="{{ $anioItem->id }}" @selected($anio?->id === $anioItem->id)>
                                    {{ $anioItem->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="asig-filter-label">Periodo académico</label>
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
                    <div class="col-md-4">
                        <label class="asig-filter-label">Sección</label>
                        <select name="seccion_id" class="form-select">
                            <option value="">Seleccione una sección</option>
                            @foreach ($secciones as $seccionItem)
                                <option value="{{ $seccionItem->id }}" @selected($seccion?->id === $seccionItem->id)>
                                    {{ $seccionItem->grado?->nivel ?? '-' }} - {{ $seccionItem->grado?->nombre ?? '-' }} - {{ $seccionItem->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="asig-btn-cargar">
                            <i data-feather="refresh-cw"></i> Cargar cursos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── RESUMEN GENERAL ── --}}
    @if ($anio)
        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="asig-stat">
                    <div class="asig-stat-icon"><i data-feather="check-square"></i></div>
                    <div>
                        <div class="asig-stat-num">{{ $totalAsignadas }}</div>
                        <div class="asig-stat-label">Cursos asignados</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="asig-stat amber">
                    <div class="asig-stat-icon"><i data-feather="clock"></i></div>
                    <div>
                        <div class="asig-stat-num">{{ $pendientes }}</div>
                        <div class="asig-stat-label">Pendientes</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="asig-stat green">
                    <div class="asig-stat-icon"><i data-feather="users"></i></div>
                    <div>
                        <div class="asig-stat-num">{{ $resumenAsignaciones->count() }}</div>
                        <div class="asig-stat-label">Docentes activos</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen table --}}
        <div class="asig-card">
            <div class="asig-card-header">
                <div>
                    <h6><i data-feather="bar-chart-2"></i> Resumen general</h6>
                    <div class="asig-card-sub">Año {{ $anio->nombre }}</div>
                </div>
                <form method="GET" action="{{ route('admin-sistema.asignacion-docente.index') }}"
                    class="asig-search-wrap">
                    <input type="hidden" name="anio_escolar_id" value="{{ $anio->id }}">
                    @if ($periodo)
                        <input type="hidden" name="periodo_academico_id" value="{{ $periodo->id }}">
                    @endif
                    @if ($seccion)
                        <input type="hidden" name="seccion_id" value="{{ $seccion->id }}">
                    @endif
                    <input type="text" name="docente" class="asig-search-input"
                        placeholder="Buscar docente..." value="{{ $docenteFiltro }}">
                    <button type="submit" class="asig-btn-buscar">
                        <i data-feather="search"></i> Buscar
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="asig-table">
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
                                <td>
                                    <div class="asig-docente-name">
                                        <div class="asig-docente-avatar">
                                            {{ strtoupper(substr($resumen['docente'], 0, 1)) }}
                                        </div>
                                        {{ $resumen['docente'] }}
                                    </div>
                                </td>
                                <td>
                                    <span class="asig-total-num">{{ $resumen['total_cursos'] }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($resumen['cursos'] as $curso)
                                            <span class="asig-curso-pill">{{ $curso }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($resumen['secciones'] as $seccionNombre)
                                            <span class="asig-seccion-pill">{{ $seccionNombre }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="asig-empty">
                                        <i data-feather="user-x"></i>
                                        <p>No hay asignaciones registradas para este año.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ── ASIGNACIÓN POR SECCIÓN ── --}}
    @if ($seccion)
        <div class="asig-assign-card" style="margin-top:24px;">
            <div class="asig-assign-header">
                <h6><i data-feather="edit-3"></i> Asignar docentes a la sección</h6>
            </div>
            <div class="asig-assign-body">

                {{-- Info banner sección --}}
                <div class="asig-info-banner">
                    <div class="asig-info-banner-icon"><i data-feather="bookmark"></i></div>
                    <div>
                        <div class="asig-info-banner-title">
                            Sección: {{ $seccion->grado?->nivel ?? '-' }} — {{ $seccion->grado?->nombre ?? '-' }} — {{ $seccion->nombre }}
                        </div>
                        <div class="asig-info-banner-meta">
                            Periodo académico:
                            {{ $periodo?->codigo ?? $anio?->nombre ?? '-' }}
                            @if ($periodo?->nombre) — {{ $periodo->nombre }} @endif
                        </div>
                    </div>
                </div>

                @if ($cursos->isEmpty())
                    <div class="asig-warn">
                        <i data-feather="alert-triangle"></i>
                        Esta sección no tiene cursos asignados. Asígnalos desde matrícula antes de continuar.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin-sistema.asignacion-docente.store') }}">
                        @csrf
                        <input type="hidden" name="anio_escolar_id" value="{{ $anio->id }}">
                        @if ($periodo)
                            <input type="hidden" name="periodo_academico_id" value="{{ $periodo->id }}">
                        @endif
                        <input type="hidden" name="seccion_id" value="{{ $seccion->id }}">

                        <div class="table-responsive">
                            <table class="asig-assign-table">
                                <thead>
                                    <tr>
                                        <th style="width:40%">Curso</th>
                                        <th>Docente asignado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cursos as $curso)
                                        <tr>
                                            <td>
                                                <span class="asig-curso-nombre">
                                                    {{ $curso->nombre }}
                                                </span>
                                            </td>
                                            <td>
                                                <select name="docentes[{{ $curso->id }}]" class="form-select">
                                                    <option value="">— Sin asignar —</option>
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

                        <div class="asig-assign-footer">
                            <button type="submit" class="asig-btn-guardar">
                                <i data-feather="save"></i> Guardar asignaciones
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    @else
        <div class="asig-tip" style="margin-top: {{ $anio ? '24px' : '0' }};">
            <i data-feather="info"></i>
            Selecciona el año escolar y la sección para ver y asignar los cursos.
        </div>
    @endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
