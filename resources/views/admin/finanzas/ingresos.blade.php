@extends('layouts.mantis')

@section('title', 'Ingresos | Sistema de Raul')
@section('page-title', 'Ingresos')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ── HERO ───────────────────────────────────────────── */
        .ing-hero {
            background: linear-gradient(135deg, #162061 0%, #1c297e 35%, #213196 75%, #23339e 100%);
            border-radius: 20px;
            border: none;
            overflow: hidden;
            position: relative;
        }
        .ing-hero::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .ing-hero::after {
            content: '';
            position: absolute;
            bottom: -60px; left: 30%;
            width: 280px; height: 280px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }
        .ing-hero .card-body { position: relative; z-index: 2; padding: 1.6rem 2rem; }

        .ing-hero-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.18);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.22);
        }
        .ing-hero-icon svg { width: 28px; height: 28px; color: #fff; stroke: #fff; }

        .ing-hero-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.74rem; font-weight: 700;
            color: #fff; letter-spacing: 0.4px;
            text-transform: uppercase; margin-bottom: 8px;
        }
        .ing-hero-pill svg { width: 13px; height: 13px; }
        .ing-hero-title { color: #fff; font-weight: 800; font-size: 1.55rem; margin-bottom: 4px; line-height: 1.2; }
        .ing-hero-sub   { color: rgba(255,255,255,0.78); font-size: 0.88rem; margin: 0; }

        .ing-hero-note {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 12px; padding: 10px 18px;
            color: #fff; font-size: 0.84rem; font-weight: 600;
            backdrop-filter: blur(4px);
            white-space: nowrap;
        }
        .ing-hero-note svg { width: 15px; height: 15px; }

        .ing-btn-excel {
            background: #fff;
            color: #1f8f5f;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 0.88rem;
            display: inline-flex; align-items: center; gap: 7px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
            cursor: pointer;
        }
        .ing-btn-excel:hover { background: #f0fdf4; color: #166534; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.18); }
        .ing-btn-excel svg { width: 16px; height: 16px; }

        /* ── KPI CARDS ──────────────────────────────────────── */
        .ing-kpi-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #d1fae5;
            box-shadow: 0 2px 12px rgba(31,143,95,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
            position: relative;
        }
        .ing-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(31,143,95,0.13); }
        .ing-kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            border-radius: 4px 0 0 4px;
        }
        .ing-kpi-card.kpi-red::before    { background: linear-gradient(180deg, #2b3a99, #1c297e); }
        .ing-kpi-card.kpi-amber::before  { background: linear-gradient(180deg, #23339f, #22329c); }
        .ing-kpi-card.kpi-blue::before   { background: linear-gradient(180deg, #3d4a9e, #213197); }
        .ing-kpi-card.kpi-green::before  { background: linear-gradient(180deg, #34d399, #1f8f5f); }

        .ing-kpi-card .card-body { padding: 1.25rem 1.4rem; }
        .ing-kpi-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 6px; }
        .ing-kpi-value { font-size: 1.55rem; font-weight: 800; line-height: 1.1; color: #111427; margin-bottom: 6px; }
        .ing-kpi-value.green { color: #1f8f5f; }
        .ing-kpi-badge {
            display: inline-flex; align-items: center;
            border-radius: 999px; padding: 3px 10px;
            font-size: 0.7rem; font-weight: 700;
            border: 1px solid transparent;
        }
        .ing-kpi-badge.red   { background: rgba(43, 58, 153,0.1);  color: #1c297e; border-color: rgba(43, 58, 153,0.22); }
        .ing-kpi-badge.amber { background: rgba(34, 50, 156,0.1); color: #17226a; border-color: rgba(34, 50, 156,0.22); }
        .ing-kpi-badge.blue  { background: rgba(37,53,156,0.1); color: #1f2e8d; border-color: rgba(37,53,156,0.22); }
        .ing-kpi-badge.green { background: rgba(31,143,95,0.1);  color: #1f8f5f; border-color: rgba(31,143,95,0.22); }

        .ing-kpi-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .ing-kpi-icon svg { width: 20px; height: 20px; }
        .ing-kpi-icon.red   { background: rgba(43, 58, 153,0.1);  color: #2b3a99; }
        .ing-kpi-icon.amber { background: rgba(34, 50, 156,0.1); color: #22329c; }
        .ing-kpi-icon.blue  { background: rgba(37,53,156,0.1); color: #25359c; }
        .ing-kpi-icon.green { background: rgba(31,143,95,0.1);  color: #1f8f5f; }

        /* ── FILTER CARD ────────────────────────────────────── */
        .ing-filter-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #d1fae5;
            box-shadow: 0 2px 8px rgba(31,143,95,0.05);
        }
        .ing-filter-card .card-header {
            background: #f0fdf4;
            border-bottom: 1px solid #d1fae5;
            border-radius: 16px 16px 0 0;
            padding: 0.9rem 1.4rem;
            display: flex; align-items: center; gap: 10px;
        }
        .ing-filter-header-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #a7f3d0, #6ee7b7);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .ing-filter-header-icon svg { width: 15px; height: 15px; color: #065f46; stroke: #065f46; }
        .ing-filter-title { font-weight: 700; font-size: 0.9rem; color: #374151; margin: 0; }

        .ing-filter-label {
            font-weight: 600; color: #6b7280; font-size: 0.8rem; margin-bottom: 5px;
            display: flex; align-items: center; gap: 5px;
        }
        .ing-filter-label svg { width: 12px; height: 12px; }

        .ing-filter-card .form-control,
        .ing-filter-card .form-select {
            border-radius: 10px; border: 1.5px solid #e5e7eb;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .ing-filter-card .form-control:focus,
        .ing-filter-card .form-select:focus {
            border-color: #1f8f5f;
            box-shadow: 0 0 0 3px rgba(31,143,95,0.1);
        }
        .ing-btn-filter {
            background: linear-gradient(135deg, #065f46, #1f8f5f);
            color: #fff; border: none;
            border-radius: 10px;
            font-weight: 700; font-size: 0.88rem;
            padding: 9px 18px;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .ing-btn-filter:hover { background: linear-gradient(135deg, #064e3b, #065f46); color: #fff; transform: translateY(-1px); }
        .ing-btn-filter svg { width: 15px; height: 15px; }

        .ing-btn-clear {
            border: 1.5px solid #e5e7eb;
            background: #fff; color: #6b7280;
            border-radius: 10px;
            font-weight: 600; font-size: 0.88rem;
            padding: 9px 18px;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .ing-btn-clear:hover { border-color: #d1d5db; background: #f9fafb; color: #374151; }
        .ing-btn-clear svg { width: 15px; height: 15px; }

        /* ── TABLA GENERAL ──────────────────────────────────── */
        .ing-table-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #d1fae5;
            box-shadow: 0 2px 12px rgba(31,143,95,0.06);
            overflow: hidden;
        }
        .ing-table-card .card-header {
            background: #fff;
            border-bottom: 1px solid #d1fae5;
            padding: 1.1rem 1.5rem;
        }
        .ing-table-card-title { font-weight: 800; font-size: 1rem; color: #111427; margin: 0; }
        .ing-table-badge {
            display: inline-flex; align-items: center;
            background: rgba(31,143,95,0.1);
            color: #1f8f5f;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.75rem; font-weight: 700;
        }

        .ing-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .ing-table thead th {
            background: #f0fdf4;
            font-size: 0.73rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            color: #6b7280;
            padding: 12px 14px;
            border-bottom: 2px solid #d1fae5;
            white-space: nowrap;
        }
        .ing-table thead th:first-child { padding-left: 20px; }
        .ing-table thead th:last-child  { padding-right: 20px; }
        .ing-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.15s ease;
        }
        .ing-table tbody tr:hover { background: #f0fdf4; }
        .ing-table tbody tr:last-child { border-bottom: none; }
        .ing-table tbody td {
            padding: 13px 14px;
            font-size: 0.865rem;
            color: #374151;
            vertical-align: middle;
        }
        .ing-table tbody td:first-child { padding-left: 20px; }
        .ing-table tbody td:last-child  { padding-right: 20px; }

        /* Fecha badge */
        .ing-date-badge {
            display: inline-flex; flex-direction: column;
            align-items: center;
            background: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 4px 9px; min-width: 52px;
            line-height: 1.2;
        }
        .ing-date-day   { font-size: 1rem; font-weight: 800; color: #1f8f5f; }
        .ing-date-month { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: #059669; letter-spacing: 0.4px; }

        /* Chips categorias */
        .ing-cat {
            display: inline-flex; align-items: center;
            border-radius: 999px; padding: 3px 9px;
            font-size: 0.7rem; font-weight: 700;
            border: 1px solid transparent;
            text-transform: uppercase; letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .ing-cat--matriculas { background: rgba(28, 41, 126,0.1);  color: #172269; border-color: rgba(28, 41, 126,0.22); }
        .ing-cat--pensiones  { background: rgba(34, 50, 156,0.12); color: #17226a; border-color: rgba(34, 50, 156,0.26); }
        .ing-cat--ventas     { background: rgba(37,53,156,0.1);  color: #1f2e8d; border-color: rgba(37,53,156,0.22); }

        /* Money */
        .ing-money {
            font-weight: 800; color: #1f8f5f;
            white-space: nowrap; font-size: 0.9rem;
        }
        .ing-money-prefix { font-size: 0.73rem; font-weight: 600; opacity: 0.7; }

        .ing-money-total {
            font-weight: 800; color: #1f8f5f;
            white-space: nowrap; font-size: 0.95rem;
        }

        /* Metodo badge */
        .ing-metodo {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f3f4f6; border-radius: 8px;
            padding: 4px 9px; font-size: 0.76rem; font-weight: 600; color: #374151;
        }
        .ing-metodo svg { width: 12px; height: 12px; color: #6b7280; }

        /* Fuente badge */
        .ing-fuente {
            display: inline-flex; align-items: center; gap: 5px;
            border-radius: 8px; padding: 4px 9px;
            font-size: 0.73rem; font-weight: 600;
            background: rgba(31,143,95,0.08); color: #1f8f5f;
            border: 1px solid rgba(31,143,95,0.18);
        }

        /* Resumen mensual row highlight */
        .ing-mes-row td { font-size: 0.875rem; }
        .ing-mes-row:hover { background: #f0fdf4 !important; }
        .ing-mes-label { font-weight: 700; color: #111427; }
        .ing-mes-zero  { color: #d1d5db; font-weight: 500; }

        /* Empty state */
        .ing-empty { padding: 4rem 2rem; text-align: center; }
        .ing-empty-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border-radius: 20px; margin: 0 auto 1rem;
            display: flex; align-items: center; justify-content: center;
        }
        .ing-empty-icon svg { width: 36px; height: 36px; color: #1f8f5f; stroke: #1f8f5f; }
        .ing-empty-title { font-weight: 700; color: #374151; font-size: 1rem; margin-bottom: 6px; }
        .ing-empty-sub   { color: #9ca3af; font-size: 0.85rem; }

        /* ── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 576px) {
            .ing-hero .card-body { padding: 1.2rem; }
            .ing-hero-title { font-size: 1.2rem; }
            .ing-kpi-value  { font-size: 1.3rem; }
        }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- ══ HERO ══════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card ing-hero">
                <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ing-hero-icon">
                            <i data-feather="trending-up"></i>
                        </div>
                        <div>
                            <div class="ing-hero-pill">
                                <i data-feather="layers"></i>
                                Modulo de Finanzas
                            </div>
                            <h4 class="ing-hero-title mb-1">Reporte de Ingresos</h4>
                            <p class="ing-hero-sub">Visualiza ingresos confirmados: matriculas, pensiones y ventas.</p>
                        </div>
                    </div>
                    <div class="ing-hero-note">
                        <i data-feather="check-circle"></i>
                        Solo ingresos confirmados &middot; Filtros avanzados
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ KPI CARDS ═════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card ing-kpi-card kpi-red h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="ing-kpi-icon red">
                        <i data-feather="file-text"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="ing-kpi-label">Matriculas</div>
                        <div class="ing-kpi-value">S/ {{ number_format((float) ($totales['matriculas'] ?? 0), 2) }}</div>
                        <span class="ing-kpi-badge red">Ingresos por matriculas</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card ing-kpi-card kpi-amber h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="ing-kpi-icon amber">
                        <i data-feather="calendar"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="ing-kpi-label">Pensiones</div>
                        <div class="ing-kpi-value">S/ {{ number_format((float) ($totales['pensiones'] ?? 0), 2) }}</div>
                        <span class="ing-kpi-badge amber">Cuotas mensuales</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card ing-kpi-card kpi-blue h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="ing-kpi-icon blue">
                        <i data-feather="shopping-cart"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="ing-kpi-label">Ventas</div>
                        <div class="ing-kpi-value">S/ {{ number_format((float) ($totales['ventas'] ?? 0), 2) }}</div>
                        <span class="ing-kpi-badge blue">Otros ingresos</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card ing-kpi-card kpi-green h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="ing-kpi-icon green">
                        <i data-feather="dollar-sign"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="ing-kpi-label">Total general</div>
                        <div class="ing-kpi-value green">S/ {{ number_format((float) ($totales['general'] ?? 0), 2) }}</div>
                        <span class="ing-kpi-badge green">Suma de todos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ FILTROS ════════════════════════════════════════════ --}}
    <div class="card ing-filter-card mb-4">
        <div class="card-header">
            <div class="ing-filter-header-icon">
                <i data-feather="filter"></i>
            </div>
            <span class="ing-filter-title">Filtros de busqueda</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin-sistema.finanzas.ingresos') }}" id="formFiltrosIngresos">
                <div class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-6">
                        <label class="ing-filter-label">
                            <i data-feather="book-open"></i>
                            Anio escolar
                        </label>
                        <select name="anio_escolar_id" class="form-select">
                            @forelse ($anios as $opcion)
                                <option value="{{ $opcion->id }}" @selected((int) ($anioId ?? 0) === (int) $opcion->id)>
                                    {{ $opcion->nombre }}
                                </option>
                            @empty
                                <option value="">Sin anios escolares</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="ing-filter-label">
                            <i data-feather="calendar"></i>
                            Desde
                        </label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $filtros['fecha_inicio_input'] ?? '' }}">
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="ing-filter-label">
                            <i data-feather="calendar"></i>
                            Hasta
                        </label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $filtros['fecha_fin_input'] ?? '' }}">
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="ing-filter-label">
                            <i data-feather="tag"></i>
                            Categoria
                        </label>
                        <select name="categoria" class="form-select">
                            <option value="todas" @selected(($filtros['categoria'] ?? 'todas') === 'todas')>Todas</option>
                            <option value="matriculas" @selected(($filtros['categoria'] ?? '') === 'matriculas')>Matriculas</option>
                            <option value="pensiones" @selected(($filtros['categoria'] ?? '') === 'pensiones')>Pensiones</option>
                            <option value="ventas" @selected(($filtros['categoria'] ?? '') === 'ventas')>Ventas</option>
                        </select>
                    </div>
                    <div class="col-xl-1 col-md-3">
                        <label class="ing-filter-label">
                            <i data-feather="chevrons-right"></i>
                            Mes desde
                        </label>
                        <select name="mes_desde" class="form-select">
                            <option value="">-</option>
                            @foreach ($mesesCatalogo as $numero => $nombreMes)
                                <option value="{{ $numero }}" @selected((int) ($filtros['mes_desde'] ?? 0) === (int) $numero)>
                                    {{ $nombreMes }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-1 col-md-3">
                        <label class="ing-filter-label">
                            <i data-feather="chevrons-left"></i>
                            Mes hasta
                        </label>
                        <select name="mes_hasta" class="form-select">
                            <option value="">-</option>
                            @foreach ($mesesCatalogo as $numero => $nombreMes)
                                <option value="{{ $numero }}" @selected((int) ($filtros['mes_hasta'] ?? 0) === (int) $numero)>
                                    {{ $nombreMes }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-auto col-md-6 d-flex gap-2 flex-wrap">
                        <button type="submit" class="ing-btn-filter">
                            <i data-feather="search"></i>
                            Filtrar
                        </button>
                        <a href="{{ route('admin-sistema.finanzas.ingresos', ['anio_escolar_id' => $anioId]) }}" class="ing-btn-clear">
                            <i data-feather="x"></i>
                            Limpiar
                        </a>
                        <button
                            type="submit"
                            formaction="{{ route('admin-sistema.finanzas.ingresos.excel') }}"
                            class="ing-btn-excel"
                            title="Descargar Excel con los filtros actuales"
                        >
                            <i data-feather="download-cloud"></i>
                            Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ RESUMEN MENSUAL ════════════════════════════════════ --}}
    <div class="card ing-table-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <h5 class="ing-table-card-title">Resumen mensual</h5>
                <small class="text-muted">Distribucion de ingresos por mes</small>
            </div>
            <span class="ing-table-badge">
                {{ count($resumenMensual ?? []) }} meses
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="ing-table mb-0">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th class="text-end">Matriculas</th>
                            <th class="text-end">Pensiones</th>
                            <th class="text-end">Ventas</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resumenMensual as $item)
                            <tr class="ing-mes-row">
                                <td>
                                    <span class="ing-mes-label">{{ $item['mes_label'] ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    @if ((float)($item['matriculas'] ?? 0) > 0)
                                        <span class="ing-money">
                                            <span class="ing-money-prefix">S/</span> {{ number_format((float) ($item['matriculas'] ?? 0), 2) }}
                                        </span>
                                    @else
                                        <span class="ing-mes-zero">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ((float)($item['pensiones'] ?? 0) > 0)
                                        <span class="ing-money">
                                            <span class="ing-money-prefix">S/</span> {{ number_format((float) ($item['pensiones'] ?? 0), 2) }}
                                        </span>
                                    @else
                                        <span class="ing-mes-zero">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ((float)($item['ventas'] ?? 0) > 0)
                                        <span class="ing-money">
                                            <span class="ing-money-prefix">S/</span> {{ number_format((float) ($item['ventas'] ?? 0), 2) }}
                                        </span>
                                    @else
                                        <span class="ing-mes-zero">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="ing-money-total">
                                        S/ {{ number_format((float) ($item['total'] ?? 0), 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="ing-empty">
                                        <div class="ing-empty-icon">
                                            <i data-feather="bar-chart-2"></i>
                                        </div>
                                        <div class="ing-empty-title">Sin datos mensuales</div>
                                        <div class="ing-empty-sub">No hay ingresos en el rango seleccionado.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══ DETALLE DE MOVIMIENTOS ════════════════════════════ --}}
    <div class="card ing-table-card">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <h5 class="ing-table-card-title">Detalle de movimientos</h5>
                <small class="text-muted">Listado de ingresos confirmados</small>
            </div>
            <span class="ing-table-badge">
                {{ number_format((int) ($totalMovimientos ?? 0)) }} movimientos
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="ing-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Categoria</th>
                            <th>Concepto</th>
                            <th>Alumno / Comprador</th>
                            <th>Metodo</th>
                            <th>Fuente</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movimientos as $mov)
                            @php
                                $fechaDia = '-';
                                $fechaMes = '';
                                $fechaFull = '-';
                                try {
                                    if (!empty($mov['fecha'])) {
                                        $dt = \Illuminate\Support\Carbon::parse($mov['fecha']);
                                        $fechaDia  = $dt->format('d');
                                        $fechaMes  = strtoupper($dt->translatedFormat('M'));
                                        $fechaFull = $dt->format('d/m/Y H:i');
                                    }
                                } catch (\Throwable $e) {
                                    $fechaDia  = (string) ($mov['fecha'] ?? '-');
                                    $fechaFull = $fechaDia;
                                }
                                $categoria = (string) ($mov['categoria'] ?? 'ventas');
                            @endphp
                            <tr>
                                <td>
                                    <div class="ing-date-badge" title="{{ $fechaFull }}">
                                        <span class="ing-date-day">{{ $fechaDia }}</span>
                                        @if($fechaMes)
                                            <span class="ing-date-month">{{ $fechaMes }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="ing-cat ing-cat--{{ $categoria }}">
                                        {{ $mov['categoria_label'] ?? 'Ventas' }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:600;color:#111427;font-size:0.875rem;">{{ $mov['concepto'] ?? '-' }}</span>
                                </td>
                                <td>
                                    @if(!empty($mov['persona']) && $mov['persona'] !== '-')
                                        <span style="font-size:0.85rem;color:#374151;">{{ $mov['persona'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($mov['metodo']) && $mov['metodo'] !== '-')
                                        <span class="ing-metodo">
                                            <i data-feather="credit-card"></i>
                                            {{ $mov['metodo'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($mov['fuente']) && $mov['fuente'] !== '-')
                                        <span class="ing-fuente">{{ $mov['fuente'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="ing-money">
                                        <span class="ing-money-prefix">S/</span> {{ number_format((float) ($mov['monto'] ?? 0), 2) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="ing-empty">
                                        <div class="ing-empty-icon">
                                            <i data-feather="inbox"></i>
                                        </div>
                                        <div class="ing-empty-title">Sin movimientos registrados</div>
                                        <div class="ing-empty-sub">No hay ingresos confirmados para los filtros seleccionados.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movimientos->hasPages())
                <div class="px-4 py-3 border-top" style="border-color:#d1fae5!important;">
                    {{ $movimientos->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
