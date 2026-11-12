@extends('layouts.mantis')

@section('title', 'Flujo de Caja | Finanzas')
@section('page-title', 'Flujo de Caja')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ── VARIABLES ─────────────────────────────────────── */
        :root {
            --fc-grad: linear-gradient(135deg, #162061 0%, #1c297e 35%, #213196 75%, #23339e 100%);
            --fc-border: #d4ecfd;
            --fc-soft:   #f5faff;
            --fc-shadow: 0 4px 20px rgba(33, 49, 150,.07);
        }

        /* ── HERO ───────────────────────────────────────────── */
        .fc-hero {
            background: var(--fc-grad);
            border-radius: 22px; overflow: hidden; position: relative;
            box-shadow: 0 18px 48px rgba(28, 41, 126,.26);
        }
        .fc-hero::before {
            content: ''; position: absolute; top: -60px; right: -60px;
            width: 260px; height: 260px;
            background: rgba(255,255,255,0.07); border-radius: 50%;
        }
        .fc-hero::after {
            content: ''; position: absolute; bottom: -80px; left: 28%;
            width: 320px; height: 320px;
            background: rgba(255,255,255,0.04); border-radius: 50%;
        }
        .fc-hero__inner {
            position: relative; z-index: 2;
            padding: 2rem 2.4rem;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 1.5rem;
        }
        .fc-hero__left  { display: flex; align-items: center; gap: 1.1rem; }
        .fc-hero__logo  {
            width: 54px; height: 54px; border-radius: 15px;
            background: rgba(255,255,255,0.18); border: 1.5px solid rgba(255,255,255,0.28);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            backdrop-filter: blur(6px);
        }
        .fc-hero__logo svg { width: 26px; height: 26px; color: #fff; stroke: #fff; }
        .fc-hero__tag {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28);
            border-radius: 999px; padding: 3px 11px;
            font-size: .68rem; font-weight: 700; color: #fff;
            text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px;
        }
        .fc-hero__tag svg { width: 10px; height: 10px; }
        .fc-hero__title { color: #fff; font-weight: 800; font-size: 1.7rem; margin: 0 0 3px; line-height: 1.15; }
        .fc-hero__sub   { color: rgba(255,255,255,.72); font-size: .86rem; margin: 0; }
        .fc-hero__right { display: flex; flex-wrap: wrap; gap: 9px; }
        .fc-hero__pill  {
            display: flex; flex-direction: column;
            background: rgba(0,0,0,0.16); border: 1px solid rgba(255,255,255,0.16);
            border-radius: 13px; padding: 10px 16px; min-width: 120px;
            backdrop-filter: blur(4px);
        }
        .fc-hero__pill-label { color: rgba(255,255,255,.6); font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
        .fc-hero__pill-val   { color: #fff; font-size: 1.05rem; font-weight: 800; line-height: 1; }

        /* ── FILTRO MES ─────────────────────────────────────── */
        .fc-filter-bar {
            background: #fff; border-radius: 16px;
            border: 1px solid var(--fc-border);
            box-shadow: var(--fc-shadow);
            padding: .85rem 1.3rem;
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .fc-filter-bar label { font-size: .75rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin: 0; }
        .fc-filter-bar select {
            border: 1.5px solid #e5e7eb; border-radius: 9px;
            padding: 6px 12px; font-size: .83rem; font-weight: 600; color: #374151;
            background: #f9fafb;
        }
        .fc-filter-bar select:focus { outline: none; border-color: #1f2e8f; box-shadow: 0 0 0 3px rgba(31, 46, 143,.1); }
        .fc-filter-btn {
            background: var(--fc-grad); color: #fff; border: none;
            border-radius: 9px; padding: 7px 16px; font-size: .82rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
            transition: opacity .2s;
        }
        .fc-filter-btn:hover { opacity: .88; }
        .fc-filter-btn svg { width: 14px; height: 14px; }

        /* ── KPI GRID ───────────────────────────────────────── */
        .fc-kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }
        @media(max-width:1200px){ .fc-kpi-grid { grid-template-columns: repeat(3,1fr); } }
        @media(max-width:640px) { .fc-kpi-grid { grid-template-columns: repeat(2,1fr); } }

        .fc-kpi {
            background: #fff; border-radius: 16px;
            border: 1px solid var(--fc-border);
            box-shadow: var(--fc-shadow);
            padding: 1.1rem 1.15rem;
            display: flex; align-items: center; gap: 12px;
            position: relative; overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .fc-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(33, 49, 150,.13); }
        .fc-kpi::before {
            content: ''; position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            border-radius: 4px 0 0 4px;
        }
        .fc-kpi.kc-green::before  { background: linear-gradient(180deg,#34d399,#1f8f5f); }
        .fc-kpi.kc-red::before    { background: linear-gradient(180deg,#2b3a99,#1c297e); }
        .fc-kpi.kc-orange::before { background: linear-gradient(180deg,#26369f,#213196); }
        .fc-kpi.kc-blue::before   { background: linear-gradient(180deg,#3d4a9e,#213197); }
        .fc-kpi.kc-teal::before   { background: linear-gradient(180deg,#2dd4bf,#0f766e); }
        .fc-kpi.kc-amber::before  { background: linear-gradient(180deg,#23339f,#1f2e8e); }

        .fc-kpi__icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .fc-kpi__icon svg { width: 20px; height: 20px; }
        .fc-kpi__icon.kc-green  { background: rgba(31,143,95,.11);  color: #1f8f5f; }
        .fc-kpi__icon.kc-red    { background: rgba(31, 46, 143,.11);  color: #1f2e8f; }
        .fc-kpi__icon.kc-orange { background: rgba(33, 49, 150,.11);  color: #213196; }
        .fc-kpi__icon.kc-blue   { background: rgba(33,49,151,.11);  color: #213197; }
        .fc-kpi__icon.kc-teal   { background: rgba(15,118,110,.11); color: #0f766e; }
        .fc-kpi__icon.kc-amber  { background: rgba(31, 46, 142,.11);  color: #1f2e8e; }

        .fc-kpi__num   { font-size: 1.45rem; font-weight: 800; color: #111427; line-height: 1; }
        .fc-kpi__label { font-size: .68rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .4px; margin: 3px 0 4px; }
        .fc-kpi__sub   {
            display: inline-flex; align-items: center; gap: 3px;
            background: #f3f4f6; border-radius: 999px;
            padding: 2px 8px; font-size: .67rem; font-weight: 600; color: #6b7280;
        }
        .fc-kpi__sub svg { width: 10px; height: 10px; }

        /* ── CARD BASE ───────────────────────────────────────── */
        .fc-card {
            background: #fff; border-radius: 18px;
            border: 1px solid var(--fc-border);
            box-shadow: var(--fc-shadow); overflow: hidden;
        }
        .fc-card__header {
            background: var(--fc-soft);
            border-bottom: 1px solid var(--fc-border);
            padding: .9rem 1.3rem;
            display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
        }
        .fc-card__header-left { display: flex; align-items: center; gap: 9px; }
        .fc-card__ico {
            width: 34px; height: 34px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
        }
        .fc-card__ico svg { width: 16px; height: 16px; }
        .fc-card__ico.kc-green  { background: rgba(31,143,95,.12);  color: #1f8f5f; }
        .fc-card__ico.kc-red    { background: rgba(31, 46, 143,.12);  color: #1f2e8f; }
        .fc-card__ico.kc-orange { background: rgba(33, 49, 150,.12);  color: #213196; }
        .fc-card__ico.kc-blue   { background: rgba(33,49,151,.12);  color: #213197; }
        .fc-card__ico.kc-teal   { background: rgba(15,118,110,.12); color: #0f766e; }
        .fc-card__ico.kc-amber  { background: rgba(31, 46, 142,.12);  color: #1f2e8e; }
        .fc-card__ico.kc-purple { background: rgba(124,58,237,.12); color: #7c3aed; }

        .fc-card__title { font-weight: 800; font-size: .9rem; color: #111427; margin: 0; }
        .fc-card__sub   { font-size: .71rem; color: #9ca3af; margin: 1px 0 0; }
        .fc-card__body  { padding: 1.25rem; }

        /* ── BADGE ───────────────────────────────────────────── */
        .fc-badge {
            display: inline-flex; align-items: center;
            border-radius: 999px; padding: 3px 10px;
            font-size: .71rem; font-weight: 700;
        }
        .fc-badge.green  { background: rgba(31,143,95,.1);  color: #1f8f5f; }
        .fc-badge.red    { background: rgba(31, 46, 143,.1);  color: #1f2e8f; }
        .fc-badge.orange { background: rgba(33, 49, 150,.1);  color: #213196; }
        .fc-badge.amber  { background: rgba(31, 46, 142,.1);  color: #1f2e8e; }
        .fc-badge.blue   { background: rgba(33,49,151,.1);  color: #213197; }

        /* ── BALANCE STRIP ───────────────────────────────────── */
        .fc-balance-strip {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 10px;
            padding: 1.1rem 1.3rem;
        }
        @media(max-width:640px){ .fc-balance-strip { grid-template-columns: 1fr; } }
        .fc-bal-tile {
            border-radius: 14px; padding: 1rem 1.2rem;
            display: flex; flex-direction: column; justify-content: space-between; min-height: 100px;
            position: relative; overflow: hidden;
        }
        .fc-bal-tile::after {
            content: ''; position: absolute; top: -25px; right: -25px;
            width: 90px; height: 90px; background: rgba(255,255,255,.1); border-radius: 50%;
        }
        .fc-bal-tile.bt-ing  { background: linear-gradient(135deg,#065f46,#1f8f5f,#34d399); }
        .fc-bal-tile.bt-eg   { background: linear-gradient(135deg,#162061,#1c297e,#2b3a99); }
        .fc-bal-tile.bt-net  { background: linear-gradient(135deg,#162166,#213197,#3d4a9e); }
        .fc-bal-tile__label  { color: rgba(255,255,255,.7); font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
        .fc-bal-tile__val    { color: #fff; font-size: 1.5rem; font-weight: 800; line-height: 1; }
        .fc-bal-tile__chip   {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(255,255,255,.18); border-radius: 999px;
            padding: 3px 9px; font-size: .7rem; font-weight: 600; color: #fff; margin-top: 8px;
        }
        .fc-bal-tile__chip svg { width: 11px; height: 11px; }
        .fc-bal-tile__ico {
            position: absolute; bottom: 8px; right: 10px;
            width: 36px; height: 36px; background: rgba(255,255,255,.14);
            border-radius: 9px; display: flex; align-items: center; justify-content: center; z-index: 1;
        }
        .fc-bal-tile__ico svg { width: 18px; height: 18px; color: rgba(255,255,255,.8); stroke: rgba(255,255,255,.8); }

        /* ── COBRANZA PROGRESS ───────────────────────────────── */
        .fc-cobranza-wrap { padding: 1.25rem 1.3rem 1.1rem; }
        .fc-cob-header    { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .fc-cob-pct       { font-size: 2.2rem; font-weight: 900; color: #111427; line-height: 1; }
        .fc-cob-sub       { font-size: .75rem; color: #6b7280; margin-top: 2px; }
        .fc-cob-bar       { height: 12px; background: #f3f4f6; border-radius: 999px; overflow: hidden; margin-bottom: 10px; }
        .fc-cob-bar-fill  { height: 100%; border-radius: 999px; transition: width .6s ease; }
        .fc-cob-items     { display: flex; gap: 16px; flex-wrap: wrap; }
        .fc-cob-item      { display: flex; align-items: center; gap: 6px; font-size: .77rem; color: #374151; font-weight: 600; }
        .fc-cob-dot       { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* ── TABLA ───────────────────────────────────────────── */
        .fc-tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
        .fc-tbl thead th {
            background: var(--fc-soft);
            font-size: .68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .45px; color: #6b7280;
            padding: 9px 13px; border-bottom: 1.5px solid var(--fc-border); white-space: nowrap;
        }
        .fc-tbl thead th:first-child { padding-left: 18px; }
        .fc-tbl thead th:last-child  { padding-right: 18px; }
        .fc-tbl tbody tr { border-bottom: 1px solid #f9fafb; transition: background .15s; }
        .fc-tbl tbody tr:hover { background: var(--fc-soft); }
        .fc-tbl tbody tr:last-child { border-bottom: none; }
        .fc-tbl tbody td { padding: 10px 13px; font-size: .835rem; color: #374151; vertical-align: middle; }
        .fc-tbl tbody td:first-child { padding-left: 18px; }
        .fc-tbl tbody td:last-child  { padding-right: 18px; }
        .fc-pill {
            display: inline-flex; align-items: center; gap: 3px;
            border-radius: 999px; padding: 2px 8px; font-size: .68rem; font-weight: 700;
        }
        .fc-pill::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
        .fc-pill.p-green { background: rgba(31,143,95,.1);  color: #1f8f5f; }
        .fc-pill.p-green::before { background: #1f8f5f; }
        .fc-pill.p-amber { background: rgba(31, 46, 142,.1);  color: #1f2e8e; }
        .fc-pill.p-amber::before { background: #1f2e8e; }
        .fc-pill.p-red   { background: rgba(31, 46, 143,.1);  color: #1f2e8f; }
        .fc-pill.p-red::before   { background: #1f2e8f; }

        /* ── ALERTA BOX ──────────────────────────────────────── */
        .fc-alert {
            border-radius: 13px; padding: .9rem 1.1rem;
            display: flex; align-items: flex-start; gap: 11px;
        }
        .fc-alert.al-warn   { background: #ebfaff; border: 1px solid #b8c1fd; }
        .fc-alert.al-danger { background: #fef2f2; border: 1px solid #fecaca; }
        .fc-alert.al-info   { background: #eff6ff; border: 1px solid #bfdbfe; }
        .fc-alert__ico {
            width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .fc-alert__ico svg { width: 17px; height: 17px; }
        .fc-alert__ico.al-warn   { background: rgba(31, 46, 142,.14);  color: #1f2e8e; }
        .fc-alert__ico.al-danger { background: rgba(220,38,38,.14);  color: #dc2626; }
        .fc-alert__ico.al-info   { background: rgba(33,49,151,.14);  color: #213197; }
        .fc-alert__title { font-weight: 700; font-size: .84rem; color: #111427; margin-bottom: 2px; }
        .fc-alert__sub   { font-size: .76rem; color: #6b7280; }

        /* ── RESUMEN MENSUAL BARS ────────────────────────────── */
        .fc-month-bars { display: flex; gap: 8px; align-items: flex-end; height: 80px; padding: 0 .5rem; }
        .fc-month-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
        .fc-month-bar {
            width: 100%; border-radius: 6px 6px 0 0;
            background: linear-gradient(180deg,#213196,#1c297e);
            min-height: 4px; transition: height .4s ease;
            position: relative; cursor: default;
        }
        .fc-month-bar.is-current { background: linear-gradient(180deg,#23339e,#1f2e8f); box-shadow: 0 0 0 2px rgba(33, 49, 150,.3); }
        .fc-month-bar-label { font-size: .6rem; font-weight: 700; color: #9ca3af; }

        .fc-empty { padding: 2rem 1rem; text-align: center; }
        .fc-empty svg { width: 34px; height: 34px; color: #d1d5db; margin-bottom: 6px; }
        .fc-empty p { color: #9ca3af; font-size: .82rem; margin: 0; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $mesNombre      = $meses[$mesActual] ?? 'Mes';
    $tasaColor      = $tasaCobranza >= 70 ? 'green' : ($tasaCobranza >= 40 ? 'amber' : 'red');
    $balancePositivo = $balanceNeto >= 0;

    // Calcular max para resumen mensual
    $maxMes = $resumenMensual->max('total') ?: 1;
@endphp

{{-- ══ HERO ══════════════════════════════════════════════ --}}
<div class="fc-hero mb-4">
    <div class="fc-hero__inner">
        <div class="fc-hero__left">
            <div class="fc-hero__logo">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M2 12h20M2 6h20M2 18h20"/><path d="M6 6v12M18 6v12"/></svg>
            </div>
            <div>
                <div class="fc-hero__tag">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Módulo de Finanzas
                </div>
                <h2 class="fc-hero__title">Flujo de Caja</h2>
                <p class="fc-hero__sub">Control de ingresos, egresos y balance financiero &mdash; {{ $anio->nombre ?? date('Y') }}</p>
            </div>
        </div>
        <div class="fc-hero__right">
            <div class="fc-hero__pill">
                <span class="fc-hero__pill-label">Ingresos totales</span>
                <span class="fc-hero__pill-val">S/ {{ number_format($totalIngresos, 0) }}</span>
            </div>
            <div class="fc-hero__pill">
                <span class="fc-hero__pill-label">Balance neto</span>
                <span class="fc-hero__pill-val">S/ {{ number_format(abs($balanceNeto), 0) }}</span>
            </div>
            <div class="fc-hero__pill">
                <span class="fc-hero__pill-label">Cobranza</span>
                <span class="fc-hero__pill-val">{{ $tasaCobranza }}%</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ ALERTAS CRÍTICAS ═══════════════════════════════════ --}}
@if ($totalDeudas > 0 || $egresosMonto == 0)
<div class="row g-3 mb-4">
    @if ($totalDeudas > 0)
    <div class="col-md-6">
        <div class="fc-alert al-danger">
            <div class="fc-alert__ico al-danger"><i data-feather="alert-triangle"></i></div>
            <div>
                <div class="fc-alert__title">Deudas pendientes críticas</div>
                <div class="fc-alert__sub">Hay <strong style="color:#1f2e8f;">S/ {{ number_format($totalDeudas, 2) }}</strong> en deudas sin cobrar. La tasa de cobranza actual es <strong>{{ $tasaCobranza }}%</strong>.</div>
            </div>
        </div>
    </div>
    @endif
    @if ($egresosMonto == 0)
    <div class="col-md-6">
        <div class="fc-alert al-info">
            <div class="fc-alert__ico al-info"><i data-feather="info"></i></div>
            <div>
                <div class="fc-alert__title">Sin egresos registrados</div>
                <div class="fc-alert__sub">No hay egresos registrados aún. Usa el módulo de <strong>Egresos</strong> para registrar gastos y obtener el balance real.</div>
            </div>
        </div>
    </div>
    @endif
    @if ($tasaCobranza < 40 && $tasaCobranza > 0)
    <div class="col-md-6">
        <div class="fc-alert al-warn">
            <div class="fc-alert__ico al-warn"><i data-feather="trending-down"></i></div>
            <div>
                <div class="fc-alert__title">Tasa de cobranza baja</div>
                <div class="fc-alert__sub">Solo se ha cobrado el <strong style="color:#1f2e8e;">{{ $tasaCobranza }}%</strong> del total facturado. Se recomienda seguimiento urgente.</div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- ══ KPIs ════════════════════════════════════════════════ --}}
<div class="fc-kpi-grid mb-4">
    <div class="fc-kpi kc-green">
        <div class="fc-kpi__icon kc-green"><i data-feather="trending-up"></i></div>
        <div>
            <div class="fc-kpi__num">S/ {{ number_format($ingresosConfirmados, 0) }}</div>
            <div class="fc-kpi__label">Ingresos confirmados</div>
            <span class="fc-kpi__sub"><i data-feather="check-circle"></i> {{ number_format($pagosCount) }} pagos</span>
        </div>
    </div>
    <div class="fc-kpi kc-red">
        <div class="fc-kpi__icon kc-red"><i data-feather="trending-down"></i></div>
        <div>
            <div class="fc-kpi__num">S/ {{ number_format($egresosMonto, 0) }}</div>
            <div class="fc-kpi__label">Egresos registrados</div>
            <span class="fc-kpi__sub"><i data-feather="minus-circle"></i> {{ number_format($egresosCount) }} ops</span>
        </div>
    </div>
    <div class="fc-kpi {{ $balancePositivo ? 'kc-teal' : 'kc-orange' }}">
        <div class="fc-kpi__icon {{ $balancePositivo ? 'kc-teal' : 'kc-orange' }}">
            <i data-feather="{{ $balancePositivo ? 'check-circle' : 'x-circle' }}"></i>
        </div>
        <div>
            <div class="fc-kpi__num" style="color:{{ $balancePositivo ? '#0f766e' : '#213196' }};">S/ {{ number_format(abs($balanceNeto), 0) }}</div>
            <div class="fc-kpi__label">Balance neto</div>
            <span class="fc-kpi__sub" style="background:{{ $balancePositivo ? 'rgba(15,118,110,.1)' : 'rgba(33, 49, 150,.1)' }};color:{{ $balancePositivo ? '#0f766e' : '#213196' }};">
                {{ $balancePositivo ? 'Superávit' : 'Déficit' }}
            </span>
        </div>
    </div>
    <div class="fc-kpi kc-amber">
        <div class="fc-kpi__icon kc-amber"><i data-feather="alert-circle"></i></div>
        <div>
            <div class="fc-kpi__num">S/ {{ number_format($totalDeudas, 0) }}</div>
            <div class="fc-kpi__label">Deudas pendientes</div>
            <span class="fc-kpi__sub"><i data-feather="clock"></i> Por cobrar</span>
        </div>
    </div>
    <div class="fc-kpi kc-blue">
        <div class="fc-kpi__icon kc-blue"><i data-feather="percent"></i></div>
        <div>
            <div class="fc-kpi__num">{{ $tasaCobranza }}%</div>
            <div class="fc-kpi__label">Tasa de cobranza</div>
            <span class="fc-kpi__sub fc-badge {{ $tasaColor }}">
                {{ $tasaCobranza >= 70 ? 'Óptima' : ($tasaCobranza >= 40 ? 'Regular' : 'Crítica') }}
            </span>
        </div>
    </div>
</div>

{{-- ══ FILTRO MES ══════════════════════════════════════════ --}}
<div class="fc-filter-bar mb-4">
    <form method="GET" action="{{ route('admin-sistema.finanzas.flujo-caja') }}" class="d-flex align-items-center gap-3 flex-wrap w-100">
        @if ($anioId)
            <input type="hidden" name="anio_escolar_id" value="{{ $anioId }}">
        @endif
        <label>Ver mes:</label>
        <select name="mes" style="min-width:130px;">
            @foreach ($meses as $num => $nom)
                <option value="{{ $num }}" {{ $mesActual == $num ? 'selected' : '' }}>{{ $nom }}</option>
            @endforeach
        </select>
        <select name="anio_num" style="min-width:90px;">
            @for ($y = now()->year; $y >= now()->year - 2; $y--)
                <option value="{{ $y }}" {{ $anioNum == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="fc-filter-btn">
            <i data-feather="filter"></i> Filtrar
        </button>
        <span style="font-size:.78rem;color:#6b7280;margin-left:auto;">
            Mostrando movimientos de <strong>{{ $mesNombre }} {{ $anioNum }}</strong>
        </span>
    </form>
</div>

{{-- ══ FILA 1: Balance tiles + Cobranza ═══════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="fc-card h-100">
            <div class="fc-card__header">
                <div class="fc-card__header-left">
                    <div class="fc-card__ico kc-orange"><i data-feather="activity"></i></div>
                    <div>
                        <p class="fc-card__title">Resumen financiero del periodo</p>
                        <p class="fc-card__sub">Ingresos, egresos y balance consolidado</p>
                    </div>
                </div>
                <span class="fc-badge {{ $balancePositivo ? 'green' : 'red' }}">
                    {{ $balancePositivo ? '▲' : '▼' }} Balance: S/ {{ number_format(abs($balanceNeto), 2) }}
                </span>
            </div>
            <div class="fc-balance-strip">
                <div class="fc-bal-tile bt-ing">
                    <div>
                        <div class="fc-bal-tile__label">Total ingresos</div>
                        <div class="fc-bal-tile__val">S/ {{ number_format($totalIngresos, 2) }}</div>
                    </div>
                    <div class="fc-bal-tile__chip"><i data-feather="credit-card"></i> Pagos + Ventas</div>
                    <div class="fc-bal-tile__ico"><i data-feather="trending-up"></i></div>
                </div>
                <div class="fc-bal-tile bt-eg">
                    <div>
                        <div class="fc-bal-tile__label">Total egresos</div>
                        <div class="fc-bal-tile__val">S/ {{ number_format($egresosMonto, 2) }}</div>
                    </div>
                    <div class="fc-bal-tile__chip"><i data-feather="minus-circle"></i> Gastos registrados</div>
                    <div class="fc-bal-tile__ico"><i data-feather="trending-down"></i></div>
                </div>
                <div class="fc-bal-tile bt-net">
                    <div>
                        <div class="fc-bal-tile__label">Balance neto</div>
                        <div class="fc-bal-tile__val">S/ {{ number_format(abs($balanceNeto), 2) }}</div>
                    </div>
                    <div class="fc-bal-tile__chip">
                        <i data-feather="{{ $balancePositivo ? 'check-circle' : 'x-circle' }}"></i>
                        {{ $balancePositivo ? 'Superávit' : 'Déficit' }}
                    </div>
                    <div class="fc-bal-tile__ico"><i data-feather="dollar-sign"></i></div>
                </div>
            </div>

            {{-- Resumen mensual mini barras --}}
            <div style="padding: 0 1.3rem 1.2rem;">
                <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#9ca3af;margin-bottom:8px;">Ingresos por mes ({{ $anioNum }})</p>
                <div class="fc-month-bars">
                    @foreach ($meses as $num => $nom)
                        @php
                            $valMes = (float)($resumenMensual->get($num)->total ?? 0);
                            $hPct   = $maxMes > 0 ? max(4, round($valMes / $maxMes * 72)) : 4;
                        @endphp
                        <div class="fc-month-bar-wrap">
                            <div class="fc-month-bar {{ $num == $mesActual ? 'is-current' : '' }}"
                                 style="height:{{ $hPct }}px;"
                                 title="{{ $nom }}: S/ {{ number_format($valMes, 0) }}">
                            </div>
                            <span class="fc-month-bar-label">{{ substr($nom,0,3) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="fc-card h-100">
            <div class="fc-card__header">
                <div class="fc-card__header-left">
                    <div class="fc-card__ico kc-blue"><i data-feather="percent"></i></div>
                    <div>
                        <p class="fc-card__title">Tasa de cobranza</p>
                        <p class="fc-card__sub">Cobrado vs pendiente total</p>
                    </div>
                </div>
            </div>
            <div class="fc-cobranza-wrap">
                <div class="fc-cob-header">
                    <div>
                        <div class="fc-cob-pct">{{ $tasaCobranza }}%</div>
                        <div class="fc-cob-sub">de lo facturado cobrado</div>
                    </div>
                    <span class="fc-badge {{ $tasaColor }}" style="font-size:.8rem;padding:5px 12px;">
                        {{ $tasaCobranza >= 70 ? 'Óptima' : ($tasaCobranza >= 40 ? 'Regular' : 'Crítica') }}
                    </span>
                </div>
                <div class="fc-cob-bar">
                    <div class="fc-cob-bar-fill"
                         style="width:{{ $tasaCobranza }}%;
                                background:{{ $tasaCobranza >= 70 ? 'linear-gradient(90deg,#34d399,#1f8f5f)' : ($tasaCobranza >= 40 ? 'linear-gradient(90deg,#23339f,#1f2e8e)' : 'linear-gradient(90deg,#a7b2f8,#1f2e8f)') }};">
                    </div>
                </div>
                <div class="fc-cob-items mb-4">
                    <div class="fc-cob-item">
                        <div class="fc-cob-dot" style="background:#1f8f5f;"></div>
                        Cobrado: S/ {{ number_format($totalIngresos, 0) }}
                    </div>
                    <div class="fc-cob-item">
                        <div class="fc-cob-dot" style="background:#1f2e8f;"></div>
                        Pendiente: S/ {{ number_format($totalDeudas, 0) }}
                    </div>
                </div>

                {{-- Donut mini --}}
                <div class="d-flex align-items-center justify-content-center" style="min-height:180px;">
                    <canvas id="chartCobranza" style="max-height:170px;max-width:170px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILA 2: Gráfico diario + Métodos de pago ═══════════ --}}
<div class="row g-4 mb-4">
    <div class="col-xl-7">
        <div class="fc-card h-100">
            <div class="fc-card__header">
                <div class="fc-card__header-left">
                    <div class="fc-card__ico kc-green"><i data-feather="bar-chart-2"></i></div>
                    <div>
                        <p class="fc-card__title">Ingresos diarios &mdash; {{ $mesNombre }} {{ $anioNum }}</p>
                        <p class="fc-card__sub">Pagos confirmados por día del mes</p>
                    </div>
                </div>
                <span class="fc-badge green">{{ number_format($ingresosDiarios->sum('total'), 0) }} S/</span>
            </div>
            <div class="fc-card__body">
                @if ($ingresosDiarios->isEmpty())
                    <div class="fc-empty">
                        <i data-feather="bar-chart-2"></i>
                        <p>Sin ingresos en {{ $mesNombre }} {{ $anioNum }}</p>
                    </div>
                @else
                    <canvas id="chartDiario" style="max-height:240px;"></canvas>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="fc-card h-100">
            <div class="fc-card__header">
                <div class="fc-card__header-left">
                    <div class="fc-card__ico kc-purple"><i data-feather="pie-chart"></i></div>
                    <div>
                        <p class="fc-card__title">Métodos de pago</p>
                        <p class="fc-card__sub">Distribución por forma de cobro</p>
                    </div>
                </div>
            </div>
            <div class="fc-card__body d-flex align-items-center justify-content-center" style="min-height:260px;">
                @if ($pagosPorMetodo->isEmpty())
                    <div class="fc-empty">
                        <i data-feather="credit-card"></i>
                        <p>Sin pagos registrados</p>
                    </div>
                @else
                    <canvas id="chartMetodos" style="max-height:230px;max-width:230px;"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ FILA 3: Deudas por categoría + Egresos por tipo ════ --}}
<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="fc-card h-100">
            <div class="fc-card__header">
                <div class="fc-card__header-left">
                    <div class="fc-card__ico kc-red"><i data-feather="alert-circle"></i></div>
                    <div>
                        <p class="fc-card__title">Deudas por categoría</p>
                        <p class="fc-card__sub">Total pendiente agrupado por concepto</p>
                    </div>
                </div>
                <span class="fc-badge red">S/ {{ number_format($totalDeudas, 0) }}</span>
            </div>
            <div class="fc-card__body">
                @if ($deudasPorCategoria->isEmpty())
                    <div class="fc-empty">
                        <i data-feather="check-circle"></i>
                        <p>Sin deudas pendientes</p>
                    </div>
                @else
                    @php $maxDeuda = $deudasPorCategoria->max('monto') ?: 1; @endphp
                    @foreach ($deudasPorCategoria as $cat)
                        @php
                            $pct = min(100, round($cat->monto / $maxDeuda * 100));
                            $colores = ['Pensiones'=>'#1f2e8f','Matrícula'=>'#213197','Otros'=>'#6b7280'];
                            $col = $colores[$cat->categoria] ?? '#6b7280';
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-weight:700;font-size:.84rem;color:#374151;">{{ $cat->categoria }}</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-size:.73rem;color:#9ca3af;">{{ $cat->c }} deudas</span>
                                    <span style="font-weight:800;font-size:.85rem;color:{{ $col }};">S/ {{ number_format($cat->monto, 0) }}</span>
                                </div>
                            </div>
                            <div style="height:8px;background:#f3f4f6;border-radius:999px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;height:100%;background:{{ $col }};border-radius:999px;opacity:.75;"></div>
                            </div>
                        </div>
                    @endforeach
                    <canvas id="chartDeudas" style="max-height:160px;margin-top:1rem;"></canvas>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="fc-card h-100">
            <div class="fc-card__header">
                <div class="fc-card__header-left">
                    <div class="fc-card__ico kc-teal"><i data-feather="shopping-bag"></i></div>
                    <div>
                        <p class="fc-card__title">Egresos por tipo</p>
                        <p class="fc-card__sub">Distribución de gastos registrados</p>
                    </div>
                </div>
                <span class="fc-badge orange">S/ {{ number_format($egresosMonto, 0) }}</span>
            </div>
            <div class="fc-card__body d-flex align-items-center justify-content-center" style="min-height:260px;">
                @if ($egresosPorCategoria->isEmpty())
                    <div class="fc-empty">
                        <i data-feather="inbox"></i>
                        <p>Sin egresos registrados aún</p>
                    </div>
                @else
                    <canvas id="chartEgresos" style="max-height:240px;max-width:240px;"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ TABLA: Movimientos del mes ══════════════════════════ --}}
<div class="fc-card mb-4">
    <div class="fc-card__header">
        <div class="fc-card__header-left">
            <div class="fc-card__ico kc-green"><i data-feather="list"></i></div>
            <div>
                <p class="fc-card__title">Movimientos del mes &mdash; {{ $mesNombre }} {{ $anioNum }}</p>
                <p class="fc-card__sub">Últimos pagos confirmados en el periodo seleccionado</p>
            </div>
        </div>
        <a href="{{ route('admin-sistema.finanzas.ingresos') }}"
           style="font-size:.77rem;font-weight:700;color:#213196;text-decoration:none;">
            Ver todos <i data-feather="arrow-right" style="width:12px;height:12px;vertical-align:middle;"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="fc-tbl">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="text-end">Monto</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimientosRecientes as $mov)
                    @php
                        $est = strtolower($mov->estado ?? 'pendiente');
                        $pc  = match($est) {
                            'confirmado', 'pagado' => 'p-green',
                            'pendiente'            => 'p-amber',
                            default                => 'p-red',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:600;white-space:nowrap;">
                            {{ $mov->fecha_pago?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="text-end" style="font-weight:800;color:#1f8f5f;font-size:.93rem;">
                            S/ {{ number_format($mov->monto, 2) }}
                        </td>
                        <td>
                            <span style="background:#f3f4f6;border-radius:7px;padding:2px 8px;font-size:.76rem;font-weight:600;color:#374151;">
                                {{ $mov->metodo ?? '-' }}
                            </span>
                        </td>
                        <td><span class="fc-pill {{ $pc }}">{{ ucfirst($est) }}</span></td>
                        <td style="font-size:.79rem;color:#9ca3af;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $mov->comentario ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="fc-empty">
                                <i data-feather="inbox"></i>
                                <p>Sin movimientos en {{ $mesNombre }} {{ $anioNum }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
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
    Chart.defaults.plugins.legend.labels.boxWidth = 11;
    Chart.defaults.plugins.legend.labels.padding  = 14;
    Chart.defaults.plugins.legend.labels.font     = { size: 11, weight: '600' };

    const $el = id => document.getElementById(id);

    function donut(id, labels, data, colors, opts = {}) {
        const el = $el(id); if (!el) return;
        if (!data || data.every(v => +v === 0)) return;
        new Chart(el, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }] },
            options: {
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: S/ ${parseFloat(ctx.parsed).toLocaleString('es-PE', { minimumFractionDigits: 2 })}` } },
                    ...opts.plugins
                },
                ...opts,
            }
        });
    }

    /* 1. Donut cobranza */
    donut('chartCobranza',
        ['Cobrado', 'Pendiente'],
        [{{ (float)$totalIngresos }}, {{ (float)$totalDeudas }}],
        [P.green, P.red]
    );

    /* 2. Bar ingresos diarios */
    (function () {
        const el = $el('chartDiario'); if (!el) return;
        const raw = @json($ingresosDiarios);
        if (!raw || !raw.length) return;
        new Chart(el, {
            type: 'bar',
            data: {
                labels: raw.map(r => {
                    const d = new Date(r.dia + 'T00:00:00');
                    return d.toLocaleDateString('es-PE', { day: '2-digit', month: 'short' });
                }),
                datasets: [{
                    label: 'Ingresos (S/)',
                    data: raw.map(r => parseFloat(r.total)),
                    backgroundColor: P.greenA,
                    borderColor: P.green,
                    borderWidth: 2, borderRadius: 9, borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` S/ ${ctx.parsed.y.toLocaleString('es-PE', { minimumFractionDigits: 2 })} (${raw[ctx.dataIndex].cant} pago/s)` } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '700' } } },
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, callback: v => 'S/ ' + Number(v).toLocaleString() } }
                }
            }
        });
    })();

    /* 3. Donut métodos de pago */
    (function () {
        const raw = @json($pagosPorMetodo);
        if (!raw || !raw.length) return;
        const cols = [P.green, P.blue, P.orange, P.purple, P.teal, P.amber, P.rose, P.red];
        donut('chartMetodos',
            raw.map(r => r.metodo || 'Sin método'),
            raw.map(r => parseFloat(r.total)),
            raw.map((_, i) => cols[i % cols.length]),
            { plugins: { tooltip: { callbacks: { label: ctx => {
                const item = raw[ctx.dataIndex];
                return ` ${ctx.label}: S/ ${parseFloat(item.total).toLocaleString('es-PE', { minimumFractionDigits: 2 })} (${item.cant} pago/s)`;
            }}}}}
        );
    })();

    /* 4. Bar deudas por categoría */
    (function () {
        const el = $el('chartDeudas'); if (!el) return;
        const raw = @json($deudasPorCategoria);
        if (!raw || !raw.length) return;
        const colMap = { 'Pensiones': P.red, 'Matrícula': P.blue, 'Otros': P.gray };
        new Chart(el, {
            type: 'bar',
            data: {
                labels: raw.map(r => r.categoria),
                datasets: [{
                    label: 'Deuda pendiente (S/)',
                    data: raw.map(r => parseFloat(r.monto)),
                    backgroundColor: raw.map(r => (colMap[r.categoria] || P.gray) + '33'),
                    borderColor:     raw.map(r => colMap[r.categoria] || P.gray),
                    borderWidth: 2, borderRadius: 8, borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` S/ ${ctx.parsed.y.toLocaleString('es-PE', { minimumFractionDigits: 2 })}` } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '700' } } },
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, callback: v => 'S/ ' + Number(v).toLocaleString() } }
                }
            }
        });
    })();

    /* 5. Donut egresos por tipo */
    (function () {
        const raw = @json($egresosPorCategoria);
        if (!raw || !raw.length) return;
        const cols = [P.orange, P.red, P.amber, P.purple, P.teal, P.blue, P.rose, P.green];
        const etiquetas = {
            pago_docente: 'Pago Docente', salario_administrativo: 'Salario Adm.',
            alquiler_local: 'Alquiler', servicios_basicos: 'Servicios',
            materiales_belleza: 'Act. Escolares', insumos_productos: 'Insumos',
            equipos_herramientas: 'Equipos', marketing_publicidad: 'Marketing',
            limpieza_mantenimiento: 'Limpieza', impuestos_tributos: 'Impuestos',
            papeleria_utiles: 'Papelería', alimentacion_personal: 'Alimentación',
            transporte_movilidad: 'Transporte', capacitacion_docente: 'Capacitación',
            uniformes_vestimenta: 'Uniformes', seguro_sctr: 'Seguro',
            otros_gastos: 'Otros',
        };
        donut('chartEgresos',
            raw.map(r => etiquetas[r.subtipo] || r.subtipo || 'Otros'),
            raw.map(r => parseFloat(r.total)),
            raw.map((_, i) => cols[i % cols.length])
        );
    })();

})();
</script>
@endpush
