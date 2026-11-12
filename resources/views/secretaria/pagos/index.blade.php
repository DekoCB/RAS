@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp
@section('title', 'Pagos | Secretaria')
@section('page-title', 'Pagos')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
    <style>
        :root {
            --pag-gradient: linear-gradient(135deg, #162061 0%, #1c297e 35%, #213196 75%, #23339e 100%);
            --pag-border:   #d4ecfd;
            --pag-soft-bg:  #f5faff;
            --pag-shadow:   0 4px 20px rgba(33, 49, 150,.08);
        }

        /* ── HERO ── */
        .pag-hero {
            background: var(--pag-gradient);
            border-radius: 22px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 50px rgba(28, 41, 126,.28);
        }
        .pag-hero::before {
            content: ''; position: absolute; top: -70px; right: -70px;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.07); border-radius: 50%; pointer-events: none;
        }
        .pag-hero::after {
            content: ''; position: absolute; bottom: -90px; left: 30%;
            width: 340px; height: 340px;
            background: rgba(255,255,255,0.04); border-radius: 50%; pointer-events: none;
        }
        .pag-hero__inner {
            position: relative; z-index: 2;
            display: flex; align-items: center;
            justify-content: space-between; gap: 2rem;
            flex-wrap: wrap; padding: 2rem 2.4rem;
        }
        .pag-hero__left   { display: flex; align-items: center; gap: 1.2rem; }
        .pag-hero__logo   {
            width: 58px; height: 58px; border-radius: 16px; overflow: hidden;
            background: rgba(255,255,255,0.18); flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid rgba(255,255,255,0.28); backdrop-filter: blur(6px);
        }
        .pag-hero__logo img { width: 42px; height: 42px; object-fit: contain; }
        .pag-hero__tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28);
            border-radius: 999px; padding: 4px 13px;
            font-size: 0.7rem; font-weight: 700; color: #fff;
            text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;
        }
        .pag-hero__school { color: rgba(255,255,255,0.65); font-size: 0.72rem; font-weight: 600; margin: 0 0 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .pag-hero__title  { color: #fff; font-weight: 800; font-size: 1.85rem; margin: 0 0 4px; line-height: 1.15; }
        .pag-hero__sub    { color: rgba(255,255,255,0.72); font-size: 0.87rem; margin: 0; }
        .pag-hero__right  { display: flex; flex-wrap: wrap; gap: 10px; }
        .pag-hero__pill {
            display: flex; flex-direction: column;
            background: rgba(0,0,0,0.18); border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px; padding: 12px 18px;
            min-width: 130px; backdrop-filter: blur(4px);
        }
        .pag-hero__pill-label { color: rgba(255,255,255,0.6); font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .pag-hero__pill-val   { color: #fff; font-size: 1.15rem; font-weight: 800; line-height: 1; }

        /* ── KPI GRID ── */
        .pag-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }
        @media(max-width:1200px){ .pag-kpi-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:576px)  { .pag-kpi-grid { grid-template-columns: 1fr; } }

        .pag-kpi {
            background: #fff; border-radius: 16px;
            border: 1px solid var(--pag-border);
            box-shadow: var(--pag-shadow);
            padding: 1.15rem 1.25rem;
            display: flex; align-items: center; gap: 14px;
            position: relative; overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .pag-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(33, 49, 150,.13); }
        .pag-kpi::before {
            content: ''; position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            border-radius: 4px 0 0 4px;
        }
        .pag-kpi.kc-green::before  { background: linear-gradient(180deg,#34d399,#1f8f5f); }
        .pag-kpi.kc-orange::before { background: linear-gradient(180deg,#26369f,#213196); }
        .pag-kpi.kc-amber::before  { background: linear-gradient(180deg,#23339f,#22329c); }
        .pag-kpi.kc-blue::before   { background: linear-gradient(180deg,#3d4a9e,#213197); }

        .pag-kpi__icon {
            width: 48px; height: 48px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .pag-kpi__icon svg { width: 22px; height: 22px; }
        .pag-kpi__icon.kc-green  { background: rgba(31,143,95,.11);  color: #1f8f5f; }
        .pag-kpi__icon.kc-orange { background: rgba(33, 49, 150,.11);  color: #213196; }
        .pag-kpi__icon.kc-amber  { background: rgba(34, 50, 156,.11); color: #1f2e8e; }
        .pag-kpi__icon.kc-blue   { background: rgba(33,49,151,.11);  color: #213197; }

        .pag-kpi__num   { font-size: 1.65rem; font-weight: 800; color: #111427; line-height: 1; }
        .pag-kpi__label { font-size: 0.73rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .45px; margin: 3px 0; }
        .pag-kpi__sub   {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f3f4f6; border-radius: 999px;
            padding: 2px 9px; font-size: 0.69rem; font-weight: 600; color: #6b7280;
        }
        .pag-kpi__sub svg { width: 11px; height: 11px; }

        /* ── CARD / TABLE ── */
        .pag-card {
            background: #fff; border-radius: 18px;
            border: 1px solid var(--pag-border);
            box-shadow: var(--pag-shadow); overflow: hidden;
        }
        .pag-card__header {
            background: var(--pag-soft-bg);
            border-bottom: 1px solid var(--pag-border);
            padding: .95rem 1.4rem;
            display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
        }
        .pag-card__header-left { display: flex; align-items: center; gap: 10px; }
        .pag-card__ico {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            background: rgba(33, 49, 150,.12); color: #213196;
        }
        .pag-card__ico svg { width: 17px; height: 17px; }
        .pag-card__title { font-weight: 800; font-size: .92rem; color: #111427; margin: 0; }
        .pag-card__sub   { font-size: .73rem; color: #9ca3af; margin: 1px 0 0; }

        /* ── SEARCH BAR ── */
        .pag-search {
            display: flex; align-items: center; gap: 10px;
            background: #fff; border: 1.5px solid var(--pag-border);
            border-radius: 12px; padding: 8px 16px;
            box-shadow: var(--pag-shadow);
        }
        .pag-search svg { width: 18px; height: 18px; color: #213196; flex-shrink: 0; }
        .pag-search input {
            border: none; outline: none; background: transparent;
            flex: 1; font-size: .88rem; color: #374151;
        }
        .pag-search input::placeholder { color: #9ca3af; }
        .pag-search .btn-buscar {
            background: var(--pag-gradient); color: #fff;
            border: none; border-radius: 8px;
            padding: 7px 20px; font-size: .84rem; font-weight: 700;
            cursor: pointer; transition: opacity .2s;
            white-space: nowrap;
        }
        .pag-search .btn-buscar:hover { opacity: .88; }

        /* ── TABLE ── */
        .pag-tbl { width: 100%; border-collapse: separate; border-spacing: 0; }
        .pag-tbl thead th {
            background: var(--pag-soft-bg);
            font-size: .7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; color: #6b7280;
            padding: 10px 14px; border-bottom: 1.5px solid var(--pag-border); white-space: nowrap;
        }
        .pag-tbl thead th:first-child { padding-left: 20px; }
        .pag-tbl thead th:last-child  { padding-right: 20px; }
        .pag-tbl tbody tr { border-bottom: 1px solid #f9fafb; transition: background .15s; }
        .pag-tbl tbody tr:hover { background: var(--pag-soft-bg); }
        .pag-tbl tbody tr:last-child { border-bottom: none; }
        .pag-tbl tbody td { padding: 11px 14px; font-size: .845rem; color: #374151; vertical-align: middle; }
        .pag-tbl tbody td:first-child { padding-left: 20px; }
        .pag-tbl tbody td:last-child  { padding-right: 20px; }

        /* ── STATUS PILL ── */
        .pag-pill {
            display: inline-flex; align-items: center; gap: 4px;
            border-radius: 999px; padding: 3px 10px; font-size: .7rem; font-weight: 700;
        }
        .pag-pill::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
        .pag-pill.p-green  { background: rgba(31,143,95,.1);  color: #1f8f5f; }
        .pag-pill.p-green::before  { background: #1f8f5f; }
        .pag-pill.p-amber  { background: rgba(31, 46, 142,.1);  color: #1f2e8e; }
        .pag-pill.p-amber::before  { background: #1f2e8e; }
        .pag-pill.p-red    { background: rgba(31, 46, 143,.1);  color: #1f2e8f; }
        .pag-pill.p-red::before    { background: #1f2e8f; }
        .pag-pill.p-gray   { background: rgba(107,114,128,.1);color: #6b7280; }
        .pag-pill.p-gray::before   { background: #6b7280; }

        /* ── ACTIONS ── */
        .pag-actions { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; }
        .pag-action-btn {
            width: 32px; height: 32px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            border: none; cursor: pointer; transition: all .2s; text-decoration: none;
        }
        .pag-action-btn svg { width: 15px; height: 15px; }
        .pag-action-btn.is-print  { background: rgba(33,49,151,.1);  color: #213197; }
        .pag-action-btn.is-view   { background: rgba(15,118,110,.1); color: #0f766e; }
        .pag-action-btn.is-edit   { background: rgba(31, 46, 142,.1);  color: #1f2e8e; }
        .pag-action-btn.is-delete { background: rgba(220,38,38,.1);  color: #dc2626; }
        .pag-action-btn:hover { filter: brightness(.88); transform: scale(1.07); }

        /* ── EMPTY STATE ── */
        .pag-empty { padding: 2.5rem 1rem; text-align: center; }
        .pag-empty svg { width: 38px; height: 38px; color: #d1d5db; margin-bottom: 8px; }
        .pag-empty p { color: #9ca3af; font-size: .83rem; margin: 0; }

        /* ── BADGE HERMANO ── */
        .hermano-badge {
            display: inline-flex; align-items: center;
            font-size: .68rem; font-weight: 800;
            border-radius: .35rem; padding: .12em .45em;
            vertical-align: middle; margin-left: 4px; flex-shrink: 0;
        }
        .hermano-badge.hm  { background:#c3f0fe; color:#17226a; border:1px solid #b8c1fd; }
        .hermano-badge.hp  { background:#eff6ff; color:#1f2e8d; border:1px solid #bfdbfe; }
        .hermano-badge.hm0 { background:#f0fdf4; color:#166534; border:1px solid #86efac; }

        /* ── BTN REGISTRAR ── */
        .btn-pag-new {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--pag-gradient); color: #fff;
            border: none; border-radius: 12px;
            padding: 9px 20px; font-size: .875rem; font-weight: 700;
            text-decoration: none; transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 14px rgba(28, 41, 126,.3);
        }
        .btn-pag-new:hover { opacity: .88; color: #fff; transform: translateY(-1px); }
        .btn-pag-new svg { width: 16px; height: 16px; }

        @media(max-width:576px){
            .pag-hero__inner { padding: 1.3rem; }
            .pag-hero__title { font-size: 1.35rem; }
            .pag-kpi__num { font-size: 1.4rem; }
        }
    </style>
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')

    {{-- ── HERO ── --}}
    <div class="pag-hero mb-4">
        <div class="pag-hero__inner">
            <div class="pag-hero__left">
                <div>
                    <p class="pag-hero__school">R.A.S</p>
                    <h1 class="pag-hero__title">Pagos y Abonos</h1>
                    <p class="pag-hero__sub">Historial de pagos registrados y su estado.</p>
                </div>
            </div>
            <div class="pag-hero__right">
                <div class="pag-hero__pill">
                    <span class="pag-hero__pill-label">Ingresos Totales</span>
                    <span class="pag-hero__pill-val">S/ {{ number_format($stats['ingresos'], 2) }}</span>
                </div>
                <div class="pag-hero__pill">
                    <span class="pag-hero__pill-label">Pagos Hoy</span>
                    <span class="pag-hero__pill-val">{{ $stats['hoy'] }}</span>
                </div>
                <div class="pag-hero__pill">
                    <span class="pag-hero__pill-label">Total Registros</span>
                    <span class="pag-hero__pill-val">{{ $stats['total'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ALERT ── --}}
    @if (session('status'))
        <div class="alert alert-success mb-3" style="border-radius:12px;">{{ session('status') }}</div>
    @endif

    {{-- ── KPI GRID ── --}}
    <div class="pag-kpi-grid mb-4">
        <div class="pag-kpi kc-green">
            <div class="pag-kpi__icon kc-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="pag-kpi__num">{{ $stats['confirmados'] }}</div>
                <div class="pag-kpi__label">Confirmados</div>
                <span class="pag-kpi__sub">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
                    S/ {{ number_format($stats['ingresos'], 2) }}
                </span>
            </div>
        </div>
        <div class="pag-kpi kc-amber">
            <div class="pag-kpi__icon kc-amber">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <div class="pag-kpi__num">{{ $stats['pendientes'] }}</div>
                <div class="pag-kpi__label">Pendientes</div>
                <span class="pag-kpi__sub">Por confirmar</span>
            </div>
        </div>
        <div class="pag-kpi kc-blue">
            <div class="pag-kpi__icon kc-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
                <div class="pag-kpi__num">{{ $stats['hoy'] }}</div>
                <div class="pag-kpi__label">Registrados hoy</div>
                <span class="pag-kpi__sub">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="pag-kpi kc-orange">
            <div class="pag-kpi__icon kc-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div>
                <div class="pag-kpi__num">{{ $stats['total'] }}</div>
                <div class="pag-kpi__label">Total registros</div>
                <span class="pag-kpi__sub">Historial completo</span>
            </div>
        </div>
    </div>

    {{-- ── SEARCH + TABLE CARD ── --}}
    <div class="pag-card">
        <div class="pag-card__header">
            <div class="pag-card__header-left">
                <div class="pag-card__ico">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
                <div>
                    <div class="pag-card__title">Pagos registrados</div>
                    <div class="pag-card__sub">Historial de pagos y abonos</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                {{-- Search form --}}
                <form method="GET" action="{{ route($rp . 'pagos.index') }}" class="d-flex">
                    <div class="pag-search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="dni" placeholder="Buscar por DNI..." value="{{ request('dni') }}">
                        <button type="submit" class="btn-buscar">Buscar</button>
                    </div>
                </form>
                @can('pago.registrar')
                <a href="{{ route($rp . 'pagos.create') }}" class="btn-pag-new">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Registrar pago
                </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive">
            <table class="pag-tbl">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>DNI / Alumno</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Aplicado</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagos as $pago)
                        @php
                            $deudas = $pago->deudas;
                            $deuda  = $deudas->first();
                            $estudiantesRelacionados = $deudas
                                ->map(fn($d) => $d?->matricula?->estudiante)
                                ->filter()->unique('id')->values();

                            $alumnoLabel = $estudiantesRelacionados->isNotEmpty()
                                ? $estudiantesRelacionados->map(function ($e) use ($pago, $hermanoTags) {
                                    $dni    = trim((string)($e->dni ?? ''));
                                    $nombre = trim(($e->apellidos ?? '') . ', ' . ($e->nombres ?? ''), ' ,');
                                    $texto  = e(($dni ?: 'SIN DNI') . ' - ' . ($nombre ?: 'Sin nombre'));
                                    $tag    = $hermanoTags[$e->id][$pago->anio_escolar_id] ?? null;
                                    $clsMap = ['H-' => 'hm', 'H+' => 'hp', 'H' => 'hm0'];
                                    $badge  = $tag
                                        ? '<span class="hermano-badge ' . ($clsMap[$tag] ?? 'hm0') . '">' . e($tag) . '</span>'
                                        : '';
                                    return $texto . $badge;
                                })->implode(' <span class="text-muted">|</span> ')
                                : '-';

                            $conceptoLabel = $deudas->count() > 1
                                ? 'Varios'
                                : ($deuda?->concepto?->nombre ?? '-');

                            $aplicado     = $pago->estado === 'confirmado'
                                ? (float)$deudas->sum('pivot.monto_aplicado')
                                : 0.0;
                            $montoMostrado = $aplicado > 0 ? $aplicado : (float)$pago->monto;

                            $pillClass = match($pago->estado) {
                                'confirmado' => 'p-green',
                                'pendiente'  => 'p-amber',
                                'anulado'    => 'p-red',
                                default      => 'p-gray',
                            };
                        @endphp
                        <tr>
                            <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : '-' }}</td>
                            <td><span style="font-weight:600;color:#111427;">{!! $alumnoLabel !!}</span></td>
                            <td>{{ $conceptoLabel }}</td>
                            <td><strong>S/ {{ number_format($montoMostrado, 2) }}</strong></td>
                            <td>S/ {{ number_format((float)$aplicado, 2) }}</td>
                            <td>{{ $pago->metodo_display }}</td>
                            <td><span class="pag-pill {{ $pillClass }}">{{ ucfirst($pago->estado) }}</span></td>
                            <td>{{ $pago->registradoPor->name ?? '-' }}</td>
                            <td>
                                <div class="pag-actions">
                                    <a href="{{ route($rp . 'pagos.voucher', $pago) }}" target="_blank"
                                        class="pag-action-btn is-print" title="Imprimir Voucher">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    </a>
                                    <a href="{{ route($rp . 'pagos.show', $pago) }}"
                                        class="pag-action-btn is-view" title="Ver Detalle">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    @can('pago.anular')
                                    <a href="{{ route($rp . 'pagos.edit', $pago) }}"
                                        class="pag-action-btn is-edit" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    @if($pago->estado !== 'anulado')
                                    <button type="button"
                                        class="pag-action-btn is-anular"
                                        title="Anular Pago"
                                        data-pago-id="{{ $pago->id }}"
                                        data-pago-monto="S/ {{ number_format($montoMostrado, 2) }}"
                                        data-pago-alumno="{{ strip_tags($alumnoLabel) }}"
                                        data-anular-url="{{ route($rp . 'pagos.anular', $pago) }}"
                                        onclick="abrirModalAnular(this)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="pag-empty">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                    <p>No hay pagos registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pagos->hasPages())
        <div style="padding: .85rem 1.4rem; border-top: 1px solid var(--pag-border); background: var(--pag-soft-bg);">
            {{ $pagos->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Anular Pago --}}
    <div class="modal fade" id="modalAnularPago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
            <div class="modal-content" style="border:none;border-radius:18px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);">
                <div style="background:linear-gradient(135deg,#162061,#1c297e);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:.9rem;">
                    <div style="width:44px;height:44px;background:rgba(255,255,255,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </div>
                    <div style="flex:1;">
                        <div style="color:#fff;font-weight:700;font-size:1rem;">Anular Pago</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.78rem;">Esta acción revertirá las deudas asociadas</div>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:rgba(255,255,255,.15);border:none;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div style="padding:1.4rem;background:#fff;">
                    <div style="background:#f5faff;border:1px solid #d4ecfd;border-left:4px solid #1c297e;border-radius:10px;padding:.8rem 1rem;margin-bottom:1.1rem;">
                        <div style="font-size:.72rem;color:#999;text-transform:uppercase;font-weight:700;letter-spacing:.06em;margin-bottom:.25rem;">Pago a anular</div>
                        <div id="anularAlumnoLabel" style="font-weight:700;color:#111;font-size:.92rem;"></div>
                        <div id="anularMontoLabel" style="color:#1c297e;font-weight:700;font-size:.85rem;margin-top:.15rem;"></div>
                    </div>
                    <form id="formAnularPago" method="POST">
                        @csrf
                        @method('PATCH')
                        <div style="margin-bottom:1rem;">
                            <label style="display:block;font-size:.8rem;font-weight:700;color:#555;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.05em;">
                                Motivo de anulación <span style="color:#1c297e;">*</span>
                            </label>
                            <textarea name="motivo" id="anularMotivo" rows="3" required
                                style="width:100%;padding:.7rem .85rem;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.9rem;outline:none;resize:vertical;font-family:inherit;transition:border-color .2s;"
                                placeholder="Indique el motivo de la anulación..."></textarea>
                        </div>
                        <div style="background:#f3f8ff;border:1px solid #cce2ff;border-radius:10px;padding:.7rem .9rem;margin-bottom:1.1rem;display:flex;gap:.6rem;align-items:flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#1c297e" stroke-width="2" width="16" height="16" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span style="font-size:.8rem;color:#192471;">El pago quedará marcado como <strong>Anulado</strong> y las deudas asociadas volverán a estado pendiente.</span>
                        </div>
                        <button type="submit" style="width:100%;padding:.85rem;background:linear-gradient(135deg,#162061,#1c297e);color:#fff;border:none;border-radius:12px;font-size:.95rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;box-shadow:0 4px 14px rgba(28, 41, 126,.4);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" width="17" height="17"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                            Confirmar Anulación
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
.pag-action-btn.is-anular {
    background: #f2f7ff;
    color: #1c297e;
    border: 1px solid #c8cffc;
}
.pag-action-btn.is-anular:hover {
    background: #1c297e;
    color: #fff;
    border-color: #1c297e;
}
.pag-action-btn.is-anular svg { stroke: currentColor; }
</style>
@endpush

@push('scripts')
<script>
function abrirModalAnular(btn) {
    document.getElementById('anularAlumnoLabel').textContent = btn.dataset.pagoAlumno;
    document.getElementById('anularMontoLabel').textContent  = btn.dataset.pagoMonto;
    document.getElementById('anularMotivo').value            = '';
    document.getElementById('formAnularPago').action         = btn.dataset.anularUrl;
    new bootstrap.Modal(document.getElementById('modalAnularPago')).show();
}
</script>
@endpush
