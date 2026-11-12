@extends('layouts.mantis')

@section('title', 'Inicio | Panel Gerente')
@section('page-title', 'Panel de Gerencia')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ── VARIABLES ───────────────────────────────────────── */
        :root {
            --ger-gradient: linear-gradient(135deg, #162061 0%, #1c297e 35%, #213196 75%, #23339e 100%);
            --ger-border:   #d4ecfd;
            --ger-soft-bg:  #f5faff;
            --ger-shadow:   0 4px 20px rgba(33, 49, 150,.08);
        }

        /* ── HERO ────────────────────────────────────────────── */
        .ger-hero {
            background: var(--ger-gradient);
            border-radius: 22px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 50px rgba(28, 41, 126,.28);
        }
        .ger-hero::before {
            content: '';
            position: absolute; top: -70px; right: -70px;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.07); border-radius: 50%;
            pointer-events: none;
        }
        .ger-hero::after {
            content: '';
            position: absolute; bottom: -90px; left: 30%;
            width: 340px; height: 340px;
            background: rgba(255,255,255,0.04); border-radius: 50%;
            pointer-events: none;
        }
        .ger-hero__inner {
            position: relative; z-index: 2;
            display: flex; align-items: center;
            justify-content: space-between; gap: 2rem;
            flex-wrap: wrap; padding: 2rem 2.4rem;
        }
        .ger-hero__left   { display: flex; align-items: center; gap: 1.2rem; }
        .ger-hero__logo   {
            width: 58px; height: 58px; border-radius: 16px; overflow: hidden;
            background: rgba(255,255,255,0.18); flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid rgba(255,255,255,0.28);
            backdrop-filter: blur(6px);
        }
        .ger-hero__logo img { width: 42px; height: 42px; object-fit: contain; }
        .ger-hero__tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28);
            border-radius: 999px; padding: 4px 13px;
            font-size: 0.7rem; font-weight: 700; color: #fff;
            text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;
        }
        .ger-hero__tag svg { width: 11px; height: 11px; }
        .ger-hero__school { color: rgba(255,255,255,0.65); font-size: 0.72rem; font-weight: 600; margin: 0 0 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .ger-hero__title  { color: #fff; font-weight: 800; font-size: 1.85rem; margin: 0 0 4px; line-height: 1.15; }
        .ger-hero__sub    { color: rgba(255,255,255,0.72); font-size: 0.87rem; margin: 0; }
        .ger-hero__right  { display: flex; flex-wrap: wrap; gap: 10px; }
        .ger-hero__pill {
            display: flex; flex-direction: column;
            background: rgba(0,0,0,0.18); border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px; padding: 12px 18px;
            min-width: 130px; backdrop-filter: blur(4px);
        }
        .ger-hero__pill-label { color: rgba(255,255,255,0.6); font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .ger-hero__pill-val   { color: #fff; font-size: 1.1rem; font-weight: 800; line-height: 1; display: flex; align-items: center; gap: 6px; }
        .ger-hero__pill-val svg { width: 15px; height: 15px; opacity: .8; }

        /* ── TABS ─────────────────────────────────────────────── */
        .ger-tabs {
            display: flex; gap: 4px;
            background: #fff; border-radius: 14px;
            padding: 5px; border: 1px solid var(--ger-border);
            box-shadow: var(--ger-shadow); width: fit-content;
        }
        .ger-tab {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 22px; border-radius: 10px;
            font-size: 0.875rem; font-weight: 700; color: #6b7280;
            text-decoration: none; transition: all .2s;
        }
        .ger-tab svg { width: 16px; height: 16px; }
        .ger-tab:hover { background: var(--ger-soft-bg); color: #1c297e; }
        .ger-tab.is-active {
            background: var(--ger-gradient);
            color: #fff; box-shadow: 0 4px 14px rgba(28, 41, 126,.3);
        }

        /* ── KPI GRID ─────────────────────────────────────────── */
        .ger-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }
        @media(max-width:1200px){ .ger-kpi-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:576px) { .ger-kpi-grid { grid-template-columns: 1fr; } }

        .ger-kpi {
            background: #fff; border-radius: 16px;
            border: 1px solid var(--ger-border);
            box-shadow: var(--ger-shadow);
            padding: 1.15rem 1.25rem;
            display: flex; align-items: center; gap: 14px;
            position: relative; overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .ger-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(33, 49, 150,.13); }
        .ger-kpi::before {
            content: ''; position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            border-radius: 4px 0 0 4px;
        }
        .ger-kpi.kc-green::before  { background: linear-gradient(180deg,#34d399,#1f8f5f); }
        .ger-kpi.kc-orange::before { background: linear-gradient(180deg,#26369f,#213196); }
        .ger-kpi.kc-blue::before   { background: linear-gradient(180deg,#3d4a9e,#213197); }
        .ger-kpi.kc-amber::before  { background: linear-gradient(180deg,#23339f,#22329c); }
        .ger-kpi.kc-red::before    { background: linear-gradient(180deg,#2b3a99,#1c297e); }
        .ger-kpi.kc-purple::before { background: linear-gradient(180deg,#a78bfa,#7c3aed); }
        .ger-kpi.kc-teal::before   { background: linear-gradient(180deg,#2dd4bf,#0f766e); }
        .ger-kpi.kc-rose::before   { background: linear-gradient(180deg,#a8b3fb,#202f92); }

        .ger-kpi__icon {
            width: 48px; height: 48px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ger-kpi__icon svg { width: 22px; height: 22px; }
        .ger-kpi__icon.kc-green  { background: rgba(31,143,95,.11);  color: #1f8f5f; }
        .ger-kpi__icon.kc-orange { background: rgba(33, 49, 150,.11);  color: #213196; }
        .ger-kpi__icon.kc-blue   { background: rgba(33,49,151,.11);  color: #213197; }
        .ger-kpi__icon.kc-amber  { background: rgba(34, 50, 156,.11); color: #1f2e8e; }
        .ger-kpi__icon.kc-red    { background: rgba(31, 46, 143,.11);  color: #1f2e8f; }
        .ger-kpi__icon.kc-purple { background: rgba(124,58,237,.11); color: #7c3aed; }
        .ger-kpi__icon.kc-teal   { background: rgba(15,118,110,.11); color: #0f766e; }
        .ger-kpi__icon.kc-rose   { background: rgba(32, 47, 146,.11);  color: #202f92; }

        .ger-kpi__num   { font-size: 1.65rem; font-weight: 800; color: #111427; line-height: 1; }
        .ger-kpi__label { font-size: 0.73rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .45px; margin: 3px 0; }
        .ger-kpi__sub   {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f3f4f6; border-radius: 999px;
            padding: 2px 9px; font-size: 0.69rem; font-weight: 600; color: #6b7280;
        }
        .ger-kpi__sub svg { width: 11px; height: 11px; }

        /* ── CARD BASE ───────────────────────────────────────── */
        .ger-card {
            background: #fff; border-radius: 18px;
            border: 1px solid var(--ger-border);
            box-shadow: var(--ger-shadow); overflow: hidden;
        }
        .ger-card__header {
            background: var(--ger-soft-bg);
            border-bottom: 1px solid var(--ger-border);
            padding: .95rem 1.4rem;
            display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
        }
        .ger-card__header-left { display: flex; align-items: center; gap: 10px; }
        .ger-card__ico {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ger-card__ico svg { width: 17px; height: 17px; }
        .ger-card__ico.kc-green  { background: rgba(31,143,95,.12);  color: #1f8f5f; }
        .ger-card__ico.kc-blue   { background: rgba(33,49,151,.12);  color: #213197; }
        .ger-card__ico.kc-amber  { background: rgba(34, 50, 156,.12); color: #1f2e8e; }
        .ger-card__ico.kc-red    { background: rgba(31, 46, 143,.12);  color: #1f2e8f; }
        .ger-card__ico.kc-purple { background: rgba(124,58,237,.12); color: #7c3aed; }
        .ger-card__ico.kc-orange { background: rgba(33, 49, 150,.12);  color: #213196; }
        .ger-card__ico.kc-teal   { background: rgba(15,118,110,.12); color: #0f766e; }
        .ger-card__ico.kc-rose   { background: rgba(32, 47, 146,.12);  color: #202f92; }

        .ger-card__title { font-weight: 800; font-size: .92rem; color: #111427; margin: 0; }
        .ger-card__sub   { font-size: .73rem; color: #9ca3af; margin: 1px 0 0; }
        .ger-card__body  { padding: 1.3rem; }

        /* ── BALANCE HERO (el grande de finanzas) ───────────── */
        .ger-balance {
            background: #fff; border-radius: 18px;
            border: 1px solid var(--ger-border);
            box-shadow: var(--ger-shadow);
            overflow: hidden;
        }
        .ger-balance__header {
            background: linear-gradient(90deg, #f5faff 60%, #f2f7fe 100%);
            border-bottom: 1px solid var(--ger-border);
            padding: 1rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
        }
        .ger-balance__tiles {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 12px;
            padding: 1.3rem;
        }
        @media(max-width:700px){ .ger-balance__tiles { grid-template-columns: 1fr; } }

        .ger-bal-tile {
            border-radius: 14px; padding: 1.1rem 1.25rem;
            position: relative; overflow: hidden;
            display: flex; flex-direction: column; justify-content: space-between; min-height: 110px;
        }
        .ger-bal-tile.bt-ing   { background: linear-gradient(135deg,#065f46,#1f8f5f,#34d399); }
        .ger-bal-tile.bt-deu   { background: linear-gradient(135deg,#162061,#1c297e,#2b3a99); }
        .ger-bal-tile.bt-ven   { background: linear-gradient(135deg,#162166,#213197,#3d4a9e); }
        .ger-bal-tile::after {
            content: ''; position: absolute; top: -30px; right: -30px;
            width: 100px; height: 100px; background: rgba(255,255,255,.1); border-radius: 50%;
        }
        .ger-bal-tile__label { color: rgba(255,255,255,.72); font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
        .ger-bal-tile__val   { color: #fff; font-size: 1.55rem; font-weight: 800; line-height: 1; }
        .ger-bal-tile__chip  {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,.18); border-radius: 999px;
            padding: 4px 10px; font-size: .72rem; font-weight: 600; color: #fff; margin-top: 10px;
        }
        .ger-bal-tile__chip svg { width: 12px; height: 12px; }
        .ger-bal-tile__icon {
            position: absolute; bottom: 10px; right: 14px;
            width: 40px; height: 40px; background: rgba(255,255,255,.14);
            border-radius: 10px; display: flex; align-items: center; justify-content: center; z-index: 1;
        }
        .ger-bal-tile__icon svg { width: 20px; height: 20px; color: rgba(255,255,255,.8); stroke: rgba(255,255,255,.8); }

        /* ── TABLA GERENTE ───────────────────────────────────── */
        .ger-tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
        .ger-tbl thead th {
            background: var(--ger-soft-bg);
            font-size: .7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; color: #6b7280;
            padding: 10px 14px; border-bottom: 1.5px solid var(--ger-border); white-space: nowrap;
        }
        .ger-tbl thead th:first-child { padding-left: 20px; }
        .ger-tbl thead th:last-child  { padding-right: 20px; }
        .ger-tbl tbody tr { border-bottom: 1px solid #f9fafb; transition: background .15s; }
        .ger-tbl tbody tr:hover { background: var(--ger-soft-bg); }
        .ger-tbl tbody tr:last-child { border-bottom: none; }
        .ger-tbl tbody td { padding: 11px 14px; font-size: .845rem; color: #374151; vertical-align: middle; }
        .ger-tbl tbody td:first-child { padding-left: 20px; }
        .ger-tbl tbody td:last-child  { padding-right: 20px; }

        /* pill de estado en tabla */
        .ger-pill {
            display: inline-flex; align-items: center; gap: 4px;
            border-radius: 999px; padding: 2px 9px; font-size: .7rem; font-weight: 700;
        }
        .ger-pill::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
        .ger-pill.p-green  { background: rgba(31,143,95,.1);  color: #1f8f5f; }
        .ger-pill.p-green::before  { background: #1f8f5f; }
        .ger-pill.p-blue   { background: rgba(33,49,151,.1);  color: #213197; }
        .ger-pill.p-blue::before   { background: #213197; }
        .ger-pill.p-amber  { background: rgba(31, 46, 142,.1);  color: #1f2e8e; }
        .ger-pill.p-amber::before  { background: #1f2e8e; }
        .ger-pill.p-red    { background: rgba(31, 46, 143,.1);  color: #1f2e8f; }
        .ger-pill.p-red::before    { background: #1f2e8f; }
        .ger-pill.p-gray   { background: rgba(107,114,128,.1);color: #6b7280; }
        .ger-pill.p-gray::before   { background: #6b7280; }

        /* ── ALERTAS FINANCIERAS ─────────────────────────────── */
        .ger-alert-box {
            border-radius: 14px; padding: 1rem 1.25rem;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .ger-alert-box.ab-danger { background: #fef2f2; border: 1px solid #fecaca; }
        .ger-alert-box.ab-warn   { background: #ebfaff; border: 1px solid #b8c1fd; }
        .ger-alert-ico {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .ger-alert-ico svg { width: 18px; height: 18px; }
        .ger-alert-ico.ab-danger { background: rgba(220,38,38,.14); color: #dc2626; }
        .ger-alert-ico.ab-warn   { background: rgba(31, 46, 142,.14);  color: #1f2e8e; }
        .ger-alert-title { font-weight: 700; font-size: .85rem; color: #111427; margin-bottom: 2px; }
        .ger-alert-sub   { font-size: .77rem; color: #6b7280; }

        /* chart badge */
        .ger-chart-badge {
            display: inline-flex; align-items: center;
            border-radius: 999px; padding: 3px 11px;
            font-size: .73rem; font-weight: 700;
        }
        .ger-chart-badge.green  { background: rgba(31,143,95,.1); color: #1f8f5f; }
        .ger-chart-badge.red    { background: rgba(31, 46, 143,.1); color: #1f2e8f; }
        .ger-chart-badge.amber  { background: rgba(31, 46, 142,.1); color: #1f2e8e; }

        /* empty state */
        .ger-empty { padding: 2.5rem 1rem; text-align: center; }
        .ger-empty svg { width: 38px; height: 38px; color: #d1d5db; margin-bottom: 8px; }
        .ger-empty p { color: #9ca3af; font-size: .83rem; margin: 0; }

        @media(max-width:576px){
            .ger-hero__inner { padding: 1.3rem; }
            .ger-hero__title { font-size: 1.35rem; }
            .ger-kpi__num { font-size: 1.4rem; }
        }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $activeTab     = request()->query('tab', 'general');
    $totalIngresos = (float) $pagosMonto + (float) $ventasMonto;
    $balanceNeto   = $totalIngresos - (float) $deudasPendientes;
@endphp

{{-- ══ HERO ═══════════════════════════════════════════════ --}}
<div class="ger-hero mb-4">
    <div class="ger-hero__inner">
        <div class="ger-hero__left">
            <div>
                <p class="ger-hero__school">R.A.S</p>
                <h2 class="ger-hero__title">Panel de Gerencia</h2>
                <p class="ger-hero__sub">Bienvenido, {{ auth()->user()->name ?? 'Gerente' }} &mdash; Control financiero y academico.</p>
            </div>
        </div>
        <div class="ger-hero__right">
            <div class="ger-hero__pill">
                <span class="ger-hero__pill-label">Ingresos totales</span>
                <span class="ger-hero__pill-val"><i data-feather="trending-up"></i> S/ {{ number_format($totalIngresos, 0) }}</span>
            </div>
            <div class="ger-hero__pill">
                <span class="ger-hero__pill-label">Deudas pendientes</span>
                <span class="ger-hero__pill-val"><i data-feather="alert-circle"></i> S/ {{ number_format($deudasPendientes, 0) }}</span>
            </div>
            <div class="ger-hero__pill">
                <span class="ger-hero__pill-label">Estudiantes activos</span>
                <span class="ger-hero__pill-val"><i data-feather="users"></i> {{ number_format($estudiantesActivos) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ TABS ════════════════════════════════════════════════ --}}
<div class="ger-tabs mb-4">
    <a class="ger-tab {{ $activeTab === 'general' ? 'is-active' : '' }}"
        href="{{ route('admin-sistema.dashboard', ['tab' => 'general']) }}">
        <i data-feather="bar-chart-2"></i>
        Resumen General
    </a>
    <a class="ger-tab {{ $activeTab === 'pagos' ? 'is-active' : '' }}"
        href="{{ route('admin-sistema.dashboard', ['tab' => 'pagos']) }}">
        <i data-feather="dollar-sign"></i>
        Finanzas y Cobros
    </a>
    <a class="ger-tab"
        href="{{ route('admin-sistema.asistencias-docentes.index') }}">
        <i data-feather="clock"></i>
        Asistencias Docentes
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB GENERAL
══════════════════════════════════════════════════════════ --}}
@if ($activeTab === 'general')

    {{-- ── KPI Financieros (fila 1) ── --}}
    <div class="ger-kpi-grid mb-3">
        <div class="ger-kpi kc-green">
            <div class="ger-kpi__icon kc-green"><i data-feather="trending-up"></i></div>
            <div>
                <div class="ger-kpi__num">S/ {{ number_format($pagosMonto, 0) }}</div>
                <div class="ger-kpi__label">Ingresos por pagos</div>
                <span class="ger-kpi__sub"><i data-feather="credit-card"></i> {{ number_format($pagosTotal) }} transacciones</span>
            </div>
        </div>
        <div class="ger-kpi kc-red">
            <div class="ger-kpi__icon kc-red"><i data-feather="alert-circle"></i></div>
            <div>
                <div class="ger-kpi__num">S/ {{ number_format($deudasPendientes, 0) }}</div>
                <div class="ger-kpi__label">Deudas pendientes</div>
                <span class="ger-kpi__sub"><i data-feather="clock"></i> Por cobrar</span>
            </div>
        </div>
        <div class="ger-kpi kc-blue">
            <div class="ger-kpi__icon kc-blue"><i data-feather="shopping-bag"></i></div>
            <div>
                <div class="ger-kpi__num">S/ {{ number_format($ventasMonto, 0) }}</div>
                <div class="ger-kpi__label">Ventas de inventario</div>
                <span class="ger-kpi__sub"><i data-feather="package"></i> {{ number_format($ventasTotal) }} operaciones</span>
            </div>
        </div>
        <div class="ger-kpi {{ $balanceNeto >= 0 ? 'kc-teal' : 'kc-rose' }}">
            <div class="ger-kpi__icon {{ $balanceNeto >= 0 ? 'kc-teal' : 'kc-rose' }}">
                <i data-feather="{{ $balanceNeto >= 0 ? 'check-circle' : 'x-circle' }}"></i>
            </div>
            <div>
                <div class="ger-kpi__num" style="color:{{ $balanceNeto >= 0 ? '#0f766e' : '#202f92' }};">
                    S/ {{ number_format(abs($balanceNeto), 0) }}
                </div>
                <div class="ger-kpi__label">Balance neto</div>
                <span class="ger-kpi__sub" style="background:{{ $balanceNeto >= 0 ? 'rgba(15,118,110,.1)' : 'rgba(32, 47, 146,.1)' }};color:{{ $balanceNeto >= 0 ? '#0f766e' : '#202f92' }};">
                    {{ $balanceNeto >= 0 ? 'Superavit' : 'Deficit' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── KPI Academicos (fila 2) ── --}}
    <div class="ger-kpi-grid mb-4">
        <div class="ger-kpi kc-orange">
            <div class="ger-kpi__icon kc-orange"><i data-feather="users"></i></div>
            <div>
                <div class="ger-kpi__num">{{ number_format($estudiantesTotal) }}</div>
                <div class="ger-kpi__label">Estudiantes totales</div>
                <span class="ger-kpi__sub"><i data-feather="user-check"></i> Activos: {{ number_format($estudiantesActivos) }}</span>
            </div>
        </div>
        <div class="ger-kpi kc-amber">
            <div class="ger-kpi__icon kc-amber"><i data-feather="clipboard"></i></div>
            <div>
                <div class="ger-kpi__num">{{ number_format($matriculasTotal) }}</div>
                <div class="ger-kpi__label">Matriculas</div>
                <span class="ger-kpi__sub"><i data-feather="check"></i> Registradas: {{ number_format($matriculasRegistradas) }}</span>
            </div>
        </div>
        <div class="ger-kpi kc-purple">
            <div class="ger-kpi__icon kc-purple"><i data-feather="user"></i></div>
            <div>
                <div class="ger-kpi__num">{{ number_format($docentesTotal) }}</div>
                <div class="ger-kpi__label">Docentes</div>
                <span class="ger-kpi__sub"><i data-feather="check-circle"></i> Activos: {{ number_format($docentesActivos) }}</span>
            </div>
        </div>
        <div class="ger-kpi kc-teal">
            <div class="ger-kpi__icon kc-teal"><i data-feather="calendar"></i></div>
            <div>
                <div class="ger-kpi__num">{{ number_format($asistenciasUltimos7) }}</div>
                <div class="ger-kpi__label">Asistencias (7 dias)</div>
                <span class="ger-kpi__sub"><i data-feather="trending-up"></i> Total: {{ number_format($asistenciasTotal) }}</span>
            </div>
        </div>
    </div>

    {{-- ── Alertas financieras criticas ── --}}
    @if ($deudasPendientes > 0)
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="ger-alert-box ab-danger">
                <div class="ger-alert-ico ab-danger"><i data-feather="alert-triangle"></i></div>
                <div>
                    <div class="ger-alert-title">Deudas pendientes de cobro</div>
                    <div class="ger-alert-sub">Hay <strong style="color:#1f2e8f;">S/ {{ number_format($deudasPendientes, 2) }}</strong> en deudas pendientes que aun no han sido cobradas.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ger-alert-box ab-warn">
                <div class="ger-alert-ico ab-warn"><i data-feather="percent"></i></div>
                <div>
                    @php
                        $tasaCobro = $totalIngresos > 0 ? min(100, round(($pagosMonto / ($pagosMonto + $deudasPendientes)) * 100, 1)) : 0;
                    @endphp
                    <div class="ger-alert-title">Tasa de cobranza actual</div>
                    <div class="ger-alert-sub">Se ha cobrado el <strong style="color:#1f2e8e;">{{ $tasaCobro }}%</strong> del total facturado. Quedan S/ {{ number_format($deudasPendientes, 2) }} por recuperar.</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Graficos fila 1: Balance + Cobranza ── --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-green"><i data-feather="activity"></i></div>
                        <div>
                            <p class="ger-card__title">Balance financiero</p>
                            <p class="ger-card__sub">Ingresos vs Deudas vs Ventas del periodo</p>
                        </div>
                    </div>
                    <span class="ger-chart-badge {{ $balanceNeto >= 0 ? 'green' : 'red' }}">
                        Balance: S/ {{ number_format(abs($balanceNeto), 0) }}
                    </span>
                </div>
                <div class="ger-card__body">
                    <canvas id="chartBalance" style="max-height:260px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-blue"><i data-feather="pie-chart"></i></div>
                        <div>
                            <p class="ger-card__title">Estado de cobranza</p>
                            <p class="ger-card__sub">Distribucion de pagos por estado</p>
                        </div>
                    </div>
                </div>
                <div class="ger-card__body d-flex align-items-center justify-content-center" style="min-height:270px;">
                    <canvas id="chartCobranza" style="max-height:230px;max-width:230px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Graficos fila 2: Asistencias + Notas ── --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-purple"><i data-feather="edit-3"></i></div>
                        <div>
                            <p class="ger-card__title">Rendimiento academico</p>
                            <p class="ger-card__sub">Promedio de notas por periodo</p>
                        </div>
                    </div>
                </div>
                <div class="ger-card__body">
                    <canvas id="chartNotas" style="max-height:240px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-teal"><i data-feather="calendar"></i></div>
                        <div>
                            <p class="ger-card__title">Asistencia diaria</p>
                            <p class="ger-card__sub">Registros de los ultimos 7 dias</p>
                        </div>
                    </div>
                    <span class="ger-chart-badge green">Total: {{ number_format($asistenciasTotal) }}</span>
                </div>
                <div class="ger-card__body">
                    <canvas id="chartAsistencias" style="max-height:240px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabla: Pagos recientes (informacion critica) ── --}}
    <div class="ger-card mb-4">
        <div class="ger-card__header">
            <div class="ger-card__header-left">
                <div class="ger-card__ico kc-green"><i data-feather="credit-card"></i></div>
                <div>
                    <p class="ger-card__title">Pagos recientes</p>
                    <p class="ger-card__sub">Ultimas transacciones registradas en el sistema</p>
                </div>
            </div>
            <a href="{{ route('admin-sistema.finanzas.ingresos') }}" style="font-size:.78rem;font-weight:700;color:#213196;text-decoration:none;">
                Ver todos <i data-feather="arrow-right" style="width:13px;height:13px;vertical-align:middle;"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="ger-tbl">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th class="text-end">Monto</th>
                        <th>Metodo de pago</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagosRecientes as $pago)
                        @php
                            $estado = strtolower($pago->estado ?? 'pendiente');
                            $pillClass = match($estado) {
                                'pagado', 'pagada', 'completado' => 'p-green',
                                'pendiente' => 'p-amber',
                                'anulado', 'cancelado' => 'p-red',
                                default => 'p-blue',
                            };
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;font-weight:600;">{{ $pago->fecha_pago?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-end" style="font-weight:800;color:#1f8f5f;font-size:.95rem;">S/ {{ number_format($pago->monto, 2) }}</td>
                            <td>
                                <span style="background:#f3f4f6;border-radius:8px;padding:3px 9px;font-size:.77rem;font-weight:600;color:#374151;">
                                    {{ $pago->metodo ?? '-' }}
                                </span>
                            </td>
                            <td><span class="ger-pill {{ $pillClass }}">{{ ucfirst($estado) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="ger-empty"><i data-feather="inbox"></i><p>Sin pagos recientes</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════
     TAB FINANZAS Y COBROS
══════════════════════════════════════════════════════════ --}}
@if ($activeTab === 'pagos')

    {{-- ── Balance tiles grandes ── --}}
    <div class="ger-balance mb-4">
        <div class="ger-balance__header">
            <div class="d-flex align-items-center gap-2">
                <div class="ger-card__ico kc-green" style="width:32px;height:32px;"><i data-feather="activity"></i></div>
                <div>
                    <p style="font-weight:800;font-size:.92rem;color:#111427;margin:0;">Resumen financiero del periodo</p>
                    <p style="font-size:.73rem;color:#9ca3af;margin:0;">Ingresos, deudas y ventas consolidadas</p>
                </div>
            </div>
            <span class="ger-chart-badge {{ $balanceNeto >= 0 ? 'green' : 'red' }}" style="font-size:.8rem;padding:5px 14px;">
                Balance neto: S/ {{ number_format(abs($balanceNeto), 2) }} {{ $balanceNeto >= 0 ? '(Superavit)' : '(Deficit)' }}
            </span>
        </div>
        <div class="ger-balance__tiles">
            <div class="ger-bal-tile bt-ing">
                <div>
                    <div class="ger-bal-tile__label">Total ingresos</div>
                    <div class="ger-bal-tile__val">S/ {{ number_format($totalIngresos, 2) }}</div>
                </div>
                <div class="ger-bal-tile__chip">
                    <i data-feather="credit-card"></i> Pagos + Ventas
                </div>
                <div class="ger-bal-tile__icon"><i data-feather="trending-up"></i></div>
            </div>
            <div class="ger-bal-tile bt-deu">
                <div>
                    <div class="ger-bal-tile__label">Deudas pendientes</div>
                    <div class="ger-bal-tile__val">S/ {{ number_format($deudasPendientes, 2) }}</div>
                </div>
                <div class="ger-bal-tile__chip">
                    <i data-feather="alert-circle"></i> Por cobrar
                </div>
                <div class="ger-bal-tile__icon"><i data-feather="clock"></i></div>
            </div>
            <div class="ger-bal-tile bt-ven">
                <div>
                    <div class="ger-bal-tile__label">Ventas de inventario</div>
                    <div class="ger-bal-tile__val">S/ {{ number_format($ventasMonto, 2) }}</div>
                </div>
                <div class="ger-bal-tile__chip">
                    <i data-feather="package"></i> {{ number_format($ventasTotal) }} ops.
                </div>
                <div class="ger-bal-tile__icon"><i data-feather="shopping-bag"></i></div>
            </div>
        </div>
    </div>

    {{-- ── Graficos: Pagos estado + Productos ── --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-green"><i data-feather="pie-chart"></i></div>
                        <div>
                            <p class="ger-card__title">Estado de pagos</p>
                            <p class="ger-card__sub">Distribucion por estado</p>
                        </div>
                    </div>
                </div>
                <div class="ger-card__body d-flex align-items-center justify-content-center" style="min-height:270px;">
                    <canvas id="chartPagosEstado" style="max-height:240px;max-width:240px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-rose"><i data-feather="alert-circle"></i></div>
                        <div>
                            <p class="ger-card__title">Cobrabilidad</p>
                            <p class="ger-card__sub">Cobrado vs pendiente</p>
                        </div>
                    </div>
                </div>
                <div class="ger-card__body d-flex align-items-center justify-content-center" style="min-height:270px;">
                    <canvas id="chartCobrabilidad" style="max-height:240px;max-width:240px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-blue"><i data-feather="package"></i></div>
                        <div>
                            <p class="ger-card__title">Top productos</p>
                            <p class="ger-card__sub">Mas vendidos del periodo</p>
                        </div>
                    </div>
                </div>
                <div class="ger-card__body">
                    @php $maxCant = $productosTop->max('total_cantidad') ?? 0; @endphp
                    @forelse ($productosTop as $prod)
                        @php
                            $cant = (int)($prod->total_cantidad ?? 0);
                            $pct  = $maxCant > 0 ? min(100, round($cant / $maxCant * 100)) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span style="font-weight:600;font-size:.83rem;color:#374151;">{{ $prod->producto->nombre ?? 'Producto' }}</span>
                                <span style="font-weight:700;font-size:.78rem;color:#6b7280;">{{ number_format($cant) }} und</span>
                            </div>
                            <div style="height:6px;background:#f3f4f6;border-radius:999px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;height:100%;background:linear-gradient(90deg,#213197,#3d4a9e);border-radius:999px;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="ger-empty"><i data-feather="package"></i><p>Sin productos vendidos</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Graficos: Balance barras + Notas ── --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-orange"><i data-feather="bar-chart-2"></i></div>
                        <div>
                            <p class="ger-card__title">Comparativo financiero</p>
                            <p class="ger-card__sub">Ingresos vs Deudas vs Ventas</p>
                        </div>
                    </div>
                </div>
                <div class="ger-card__body">
                    <canvas id="chartComparativo" style="max-height:240px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="ger-card h-100">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-purple"><i data-feather="edit-3"></i></div>
                        <div>
                            <p class="ger-card__title">Notas por periodo</p>
                            <p class="ger-card__sub">Promedio academico y aprobados</p>
                        </div>
                    </div>
                </div>
                <div class="ger-card__body">
                    <canvas id="chartNotasFin" style="max-height:240px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tablas: Pagos + Ventas ── --}}
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="ger-card">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-green"><i data-feather="credit-card"></i></div>
                        <div>
                            <p class="ger-card__title">Pagos recientes</p>
                            <p class="ger-card__sub">Ultimas 6 transacciones</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="ger-tbl">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                                <th>Metodo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pagosRecientes as $pago)
                                @php
                                    $est = strtolower($pago->estado ?? 'pendiente');
                                    $pc = match($est){ 'pagado','pagada','completado'=>'p-green','pendiente'=>'p-amber','anulado','cancelado'=>'p-red',default=>'p-blue' };
                                @endphp
                                <tr>
                                    <td style="font-weight:600;">{{ $pago->fecha_pago?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="text-end" style="font-weight:800;color:#1f8f5f;">S/ {{ number_format($pago->monto, 2) }}</td>
                                    <td style="font-size:.8rem;">{{ $pago->metodo ?? '-' }}</td>
                                    <td><span class="ger-pill {{ $pc }}">{{ ucfirst($est) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="ger-empty"><i data-feather="inbox"></i><p>Sin pagos</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="ger-card">
                <div class="ger-card__header">
                    <div class="ger-card__header-left">
                        <div class="ger-card__ico kc-amber"><i data-feather="shopping-bag"></i></div>
                        <div>
                            <p class="ger-card__title">Ventas recientes</p>
                            <p class="ger-card__sub">Ultimas 6 ventas de inventario</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="ger-tbl">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Comprador</th>
                                <th class="text-end">Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ventasRecientes as $venta)
                                <tr>
                                    <td style="font-weight:600;">{{ $venta->fecha_venta?->format('d/m/Y') ?? '-' }}</td>
                                    <td style="font-size:.82rem;">{{ $venta->comprador_nombre ?? 'Venta directa' }}</td>
                                    <td class="text-end" style="font-weight:800;color:#1f8f5f;">S/ {{ number_format($venta->total, 2) }}</td>
                                    <td><span class="ger-pill p-green">{{ ucfirst($venta->estado ?? 'pagado') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="ger-empty"><i data-feather="inbox"></i><p>Sin ventas</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    /* ── Paleta ─────────────────────────── */
    const P = {
        green:  '#1f8f5f', greenA: 'rgba(31,143,95,.18)',
        red:    '#1f2e8f', redA:   'rgba(31, 46, 143,.18)',
        orange: '#213196', orangeA:'rgba(33, 49, 150,.18)',
        blue:   '#213197', blueA:  'rgba(33,49,151,.18)',
        amber:  '#1f2e8e', amberA: 'rgba(31, 46, 142,.18)',
        purple: '#7c3aed', purpleA:'rgba(124,58,237,.18)',
        teal:   '#0f766e', tealA:  'rgba(15,118,110,.18)',
        rose:   '#202f92', roseA:  'rgba(32, 47, 146,.18)',
        gray:   '#94a3b8',
    };

    Chart.defaults.font.family = "'Manrope', sans-serif";
    Chart.defaults.plugins.legend.labels.boxWidth  = 12;
    Chart.defaults.plugins.legend.labels.padding   = 16;
    Chart.defaults.plugins.legend.labels.font      = { size: 12, weight: '600' };

    const $ = id => document.getElementById(id);

    /* ── helpers ─────────────────────────── */
    function donut(id, labels, data, colors, opts = {}) {
        const el = $(id); if (!el) return;
        if (!data || data.every(v => v === 0)) {
            el.closest('[style*="min-height"]')?.insertAdjacentHTML('beforeend',
                '<p style="text-align:center;color:#9ca3af;font-size:.82rem;margin-top:1rem;">Sin datos disponibles</p>');
            return;
        }
        new Chart(el, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }] },
            options: {
                cutout: '70%',
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.formattedValue}` } }, ...opts.plugins },
                ...opts,
            }
        });
    }

    @if ($activeTab === 'general')

    /* 1. Balance horizontal bar ──────────── */
    (function(){
        const el = $('chartBalance'); if (!el) return;
        new Chart(el, {
            type: 'bar',
            data: {
                labels: ['Ingresos por pagos', 'Deudas pendientes', 'Ventas inventario'],
                datasets: [{
                    label: 'S/ Monto',
                    data: [{{ (float)$pagosMonto }}, {{ (float)$deudasPendientes }}, {{ (float)$ventasMonto }}],
                    backgroundColor: [P.greenA, P.redA, P.blueA],
                    borderColor:     [P.green,  P.red,  P.blue],
                    borderWidth: 2, borderRadius: 10, borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` S/ ${parseFloat(ctx.parsed.y).toLocaleString('es-PE', {minimumFractionDigits:2})}` } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 12, weight: '700' } } },
                    y: {
                        beginAtZero: true, grid: { color: '#f3f4f6' },
                        ticks: { font: { size: 11 }, callback: v => 'S/ ' + Number(v).toLocaleString() }
                    }
                }
            }
        });
    })();

    /* 2. Donut Cobranza / Pagos estado ───── */
    (function(){
        const raw = @json($pagosPorEstado);
        if (!raw || !raw.length) return;
        const colMap = { pagado: P.green, pagada: P.green, completado: P.green, pendiente: P.amber, anulado: P.red, cancelado: P.rose };
        donut('chartCobranza',
            raw.map(r => (r.estado ?? 'N/D').charAt(0).toUpperCase() + (r.estado ?? 'N/D').slice(1)),
            raw.map(r => parseInt(r.total)),
            raw.map(r => colMap[r.estado?.toLowerCase()] ?? P.gray),
            { plugins: { tooltip: { callbacks: { label: ctx => { const item = raw[ctx.dataIndex]; return ` ${ctx.label}: ${ctx.parsed} (S/ ${parseFloat(item.monto ?? 0).toFixed(2)})`; }}}}}
        );
    })();

    /* 3. Bar Notas por periodo ───────────── */
    (function(){
        const el = $('chartNotas'); if (!el) return;
        const raw = @json($notasPorPeriodo);
        if (!raw || !raw.length) {
            el.closest('.ger-card__body').innerHTML = '<div class="ger-empty"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="38" height="38"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><p>Sin notas registradas</p></div>';
            return;
        }
        new Chart(el, {
            type: 'bar',
            data: {
                labels: raw.map(r => r.periodo || 'Sin periodo'),
                datasets: [
                    { label: 'Promedio', data: raw.map(r => parseFloat(r.promedio ?? 0).toFixed(2)), backgroundColor: P.purpleA, borderColor: P.purple, borderWidth: 2, borderRadius: 8, borderSkipped: false, yAxisID: 'y' },
                    { label: 'Aprobados', data: raw.map(r => parseInt(r.aprobados ?? 0)), backgroundColor: P.greenA, borderColor: P.green, borderWidth: 2, borderRadius: 8, borderSkipped: false, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                    y: { type: 'linear', position: 'left', min: 0, max: 20, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } }, title: { display: true, text: 'Promedio', font: { size: 11 } } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { font: { size: 11 } }, title: { display: true, text: 'Aprobados', font: { size: 11 } } }
                }
            }
        });
    })();

    /* 4. Bar Asistencias ─────────────────── */
    (function(){
        const el = $('chartAsistencias'); if (!el) return;
        const raw = @json($asistenciasPorDia->sortBy('fecha')->values());
        if (!raw || !raw.length) return;
        new Chart(el, {
            type: 'bar',
            data: {
                labels: raw.map(r => { const d = new Date(r.fecha + 'T00:00:00'); return d.toLocaleDateString('es-PE', { day: '2-digit', month: 'short' }); }),
                datasets: [{ label: 'Asistencias', data: raw.map(r => parseInt(r.total)), backgroundColor: P.tealA, borderColor: P.teal, borderWidth: 2, borderRadius: 8, borderSkipped: false }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} asistencias` } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { size: 11 } } }
                }
            }
        });
    })();

    @endif {{-- general --}}

    @if ($activeTab === 'pagos')

    /* 5. Donut pagos por estado ──────────── */
    (function(){
        const raw = @json($pagosPorEstado);
        if (!raw || !raw.length) return;
        const colMap = { pagado: P.green, pagada: P.green, completado: P.green, pendiente: P.amber, anulado: P.red, cancelado: P.rose };
        donut('chartPagosEstado',
            raw.map(r => (r.estado ?? 'N/D').charAt(0).toUpperCase() + (r.estado ?? 'N/D').slice(1)),
            raw.map(r => parseInt(r.total)),
            raw.map(r => colMap[r.estado?.toLowerCase()] ?? P.gray),
            { plugins: { tooltip: { callbacks: { label: ctx => { const item = raw[ctx.dataIndex]; return ` ${ctx.label}: ${ctx.parsed} | S/ ${parseFloat(item.monto ?? 0).toFixed(2)}`; }}}}}
        );
    })();

    /* 6. Donut cobrabilidad ──────────────── */
    (function(){
        const cobrado  = {{ (float)$pagosMonto }};
        const pendiente= {{ (float)$deudasPendientes }};
        donut('chartCobrabilidad',
            ['Cobrado', 'Por cobrar'],
            [cobrado, pendiente],
            [P.green, P.red],
            { plugins: { tooltip: { callbacks: { label: ctx => ` S/ ${ctx.parsed.toLocaleString('es-PE', {minimumFractionDigits:2})}` }}}}
        );
    })();

    /* 7. Bar comparativo financiero ─────── */
    (function(){
        const el = $('chartComparativo'); if (!el) return;
        new Chart(el, {
            type: 'bar',
            data: {
                labels: ['Pagos', 'Deudas', 'Ventas'],
                datasets: [{
                    label: 'Monto (S/)',
                    data: [{{ (float)$pagosMonto }}, {{ (float)$deudasPendientes }}, {{ (float)$ventasMonto }}],
                    backgroundColor: [P.greenA, P.redA, P.blueA],
                    borderColor: [P.green, P.red, P.blue],
                    borderWidth: 2, borderRadius: 10, borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` S/ ${parseFloat(ctx.parsed.y).toLocaleString('es-PE', {minimumFractionDigits:2})}` } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 12, weight: '700' } } },
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, callback: v => 'S/ ' + Number(v).toLocaleString() } }
                }
            }
        });
    })();

    /* 8. Bar Notas fin ───────────────────── */
    (function(){
        const el = $('chartNotasFin'); if (!el) return;
        const raw = @json($notasPorPeriodo);
        if (!raw || !raw.length) { el.closest('.ger-card__body').innerHTML = '<div class="ger-empty"><p>Sin notas registradas</p></div>'; return; }
        new Chart(el, {
            type: 'bar',
            data: {
                labels: raw.map(r => r.periodo || 'Sin periodo'),
                datasets: [
                    { label: 'Promedio', data: raw.map(r => parseFloat(r.promedio ?? 0).toFixed(2)), backgroundColor: P.purpleA, borderColor: P.purple, borderWidth: 2, borderRadius: 8, borderSkipped: false, yAxisID: 'y' },
                    { label: 'Aprobados', data: raw.map(r => parseInt(r.aprobados ?? 0)), backgroundColor: P.orangeA, borderColor: P.orange, borderWidth: 2, borderRadius: 8, borderSkipped: false, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                    y: { type: 'linear', position: 'left', min: 0, max: 20, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } }, title: { display: true, text: 'Promedio', font: { size: 11 } } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { font: { size: 11 } }, title: { display: true, text: 'Aprobados', font: { size: 11 } } }
                }
            }
        });
    })();

    @endif {{-- pagos --}}

})();
</script>
@endpush
