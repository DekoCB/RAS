@extends('layouts.mantis')

@section('title', 'Mis Asistencias | Auxiliar')
@section('page-title', 'Mis Asistencias')
@section('body-class', 'role-auxiliar')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/auxiliar-dashboard.css') }}?v={{ filemtime(public_path('css/auxiliar-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    <style>
        /* ── Hero de sección ── */
        .aux-asist-hero {
            border-radius: var(--aux-radius);
            overflow: hidden;
            position: relative;
            background: var(--aux-gradient);
            box-shadow: var(--aux-shadow-lg);
            margin-bottom: 22px;
        }
        .aux-asist-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 80% 20%, rgba(255,255,255,.12) 0%, transparent 55%),
                radial-gradient(ellipse at 10% 90%, rgba(0,0,0,.12) 0%, transparent 40%);
            pointer-events: none;
        }
        .aux-asist-hero__inner {
            position: relative; z-index: 1;
            padding: 26px 30px;
            display: flex; align-items: center;
            justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
        }
        .aux-asist-hero__left  { display: flex; align-items: center; gap: 16px; }
        .aux-asist-hero__icon  {
            width: 56px; height: 56px; border-radius: 18px;
            background: rgba(255,255,255,.2); backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,.3);
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .aux-asist-hero__icon i { width: 26px; height: 26px; color: #fff; }
        .aux-asist-hero__title  { font-size: 1.5rem; font-weight: 900; color: #fff; margin: 0 0 4px; }
        .aux-asist-hero__sub    { font-size: .87rem; color: rgba(255,255,255,.8); margin: 0; }
        .aux-asist-hero__pills  { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .aux-asist-hero__pill   {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(0,0,0,.18); border: 1px solid rgba(255,255,255,.22);
            color: #fff; padding: 6px 14px; border-radius: 999px;
            font-size: .78rem; font-weight: 700;
        }
        .aux-asist-hero__pill i      { width: 13px; height: 13px; }
        .aux-asist-hero__pill.is-light {
            background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.35);
        }

        /* ── KPI resumen ── */
        .aux-asist-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .aux-asist-kpi {
            background: #fff;
            border-radius: var(--aux-radius);
            padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            box-shadow: var(--aux-shadow-sm);
            border: 1px solid var(--aux-border);
        }
        .aux-asist-kpi__icon {
            width: 44px; height: 44px; border-radius: 14px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
        }
        .aux-asist-kpi__icon i { width: 20px; height: 20px; }
        .aux-asist-kpi__num  { font-size: 1.6rem; font-weight: 800; color: var(--aux-ink); line-height: 1; }
        .aux-asist-kpi__label { font-size: .72rem; color: var(--aux-muted); font-weight: 600;
                                 text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }

        /* ── Filtro ── */
        .aux-asist-filter {
            background: #fff;
            border: 1px solid var(--aux-border);
            border-radius: var(--aux-radius);
            box-shadow: var(--aux-shadow-sm);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .aux-asist-filter__header {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--aux-border);
            background: linear-gradient(180deg, var(--aux-soft-bg) 0%, #fff 100%);
        }
        .aux-asist-filter__icon {
            width: 32px; height: 32px; border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--aux-gradient); color: #fff; flex-shrink: 0;
        }
        .aux-asist-filter__icon i { width: 14px; height: 14px; }
        .aux-asist-filter__title  { font-weight: 800; color: var(--aux-ink); margin: 0; font-size: .93rem; }
        .aux-asist-filter__body   { padding: 16px 20px; }

        /* ── Badges ENTRADA / SALIDA ── */
        .aux-asist-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 999px;
            font-size: .74rem; font-weight: 800;
            letter-spacing: .4px; border: 1px solid transparent;
        }
        .aux-asist-badge i { width: 11px; height: 11px; }
        .aux-asist-badge--entrada {
            background: rgba(22,163,74,.1); color: #15803d;
            border-color: rgba(22,163,74,.25);
        }
        .aux-asist-badge--salida {
            background: rgba(28, 41, 126,.08); color: #1c297e;
            border-color: rgba(28, 41, 126,.2);
        }

        /* ── Tabla ── */
        .aux-asist-table thead th {
            background: var(--aux-gradient);
            color: #fff; font-weight: 700; font-size: .82rem;
            padding: 13px 16px; border: none; white-space: nowrap;
        }
        .aux-asist-table tbody td {
            padding: 13px 16px; vertical-align: middle;
            border-top: 1px solid var(--aux-border); font-size: .9rem;
        }
        .aux-asist-table tbody tr:hover { background: rgba(28, 41, 126,.03); }
        .aux-asist-date {
            font-weight: 700; color: var(--aux-ink);
            display: inline-flex; align-items: center; gap: 7px;
        }
        .aux-asist-date i  { width: 14px; height: 14px; color: var(--aux-blue-500); flex-shrink: 0; }
        .aux-asist-time    {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .84rem; font-weight: 700; color: var(--aux-indigo-700);
        }
        .aux-asist-time i  { width: 13px; height: 13px; color: var(--aux-blue-400); }
        .aux-asist-by      {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .84rem; color: var(--aux-muted);
        }
        .aux-asist-by i    { width: 13px; height: 13px; }

        /* ── Empty state ── */
        .aux-asist-empty { text-align: center; padding: 52px 24px; }
        .aux-asist-empty__icon {
            width: 64px; height: 64px; border-radius: 20px;
            background: var(--aux-gradient); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(28, 41, 126,.2);
        }
        .aux-asist-empty__icon i   { width: 28px; height: 28px; }
        .aux-asist-empty__title    { font-weight: 800; color: var(--aux-ink); margin: 0 0 6px; }
        .aux-asist-empty__sub      { color: var(--aux-muted); font-size: .88rem; margin: 0; }

        @media (max-width: 768px) {
            .aux-asist-hero__inner { flex-direction: column; align-items: flex-start; }
        }
    </style>
@endpush

@section('sidebar')
    @include('auxiliar.partials.sidebar')
@endsection

@section('content')
@php
    $totalEntradas = $asistencias->getCollection()->where('tipo', 'ENTRADA')->count();
    $totalSalidas  = $asistencias->getCollection()->where('tipo', 'SALIDA')->count();
@endphp

    {{-- ── HERO ── --}}
    <div class="aux-asist-hero">
        <div class="aux-asist-hero__inner">
            <div class="aux-asist-hero__left">
                <div class="aux-asist-hero__icon">
                    <i data-feather="clock"></i>
                </div>
                <div>
                    <h1 class="aux-asist-hero__title">Mis asistencias</h1>
                    <p class="aux-asist-hero__sub">Historial de entradas y salidas registradas</p>
                </div>
            </div>
            <div class="aux-asist-hero__pills">
                <span class="aux-asist-hero__pill is-light">
                    <i data-feather="calendar"></i>
                    {{ now()->format('d/m/Y') }}
                </span>
                <span class="aux-asist-hero__pill">
                    <i data-feather="list"></i>
                    {{ $asistencias->total() }} {{ $asistencias->total() === 1 ? 'registro' : 'registros' }}
                </span>
                @if($anio)
                    <span class="aux-asist-hero__pill is-light">
                        <i data-feather="bookmark"></i>
                        {{ $anio->nombre ?? $anio->anio }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── KPI RESUMEN ── --}}
    <div class="aux-asist-kpi-grid">
        <div class="aux-asist-kpi">
            <div class="aux-asist-kpi__icon" style="background:linear-gradient(135deg,#15803d,#22c55e);box-shadow:0 6px 14px rgba(34,197,94,.25);">
                <i data-feather="log-in"></i>
            </div>
            <div>
                <div class="aux-asist-kpi__num">{{ $asistencias->total() > 0 ? \App\Models\AsistenciaAuxiliar::where('user_id', $user->id)->where('tipo','ENTRADA')->count() : 0 }}</div>
                <div class="aux-asist-kpi__label">Entradas</div>
            </div>
        </div>
        <div class="aux-asist-kpi">
            <div class="aux-asist-kpi__icon" style="background:var(--aux-gradient);box-shadow:var(--aux-shadow-sm);">
                <i data-feather="log-out"></i>
            </div>
            <div>
                <div class="aux-asist-kpi__num">{{ $asistencias->total() > 0 ? \App\Models\AsistenciaAuxiliar::where('user_id', $user->id)->where('tipo','SALIDA')->count() : 0 }}</div>
                <div class="aux-asist-kpi__label">Salidas</div>
            </div>
        </div>
        <div class="aux-asist-kpi">
            <div class="aux-asist-kpi__icon" style="background:linear-gradient(135deg,#1d2a82,#213196);box-shadow:0 6px 14px rgba(29, 42, 130,.25);">
                <i data-feather="calendar"></i>
            </div>
            <div>
                <div class="aux-asist-kpi__num" style="font-size:1.2rem;">{{ $anio?->nombre ?? $anio?->anio ?? '—' }}</div>
                <div class="aux-asist-kpi__label">Año activo</div>
            </div>
        </div>
    </div>

    {{-- ── FILTROS ── --}}
    <div class="aux-asist-filter">
        <div class="aux-asist-filter__header">
            <div class="aux-asist-filter__icon">
                <i data-feather="filter"></i>
            </div>
            <span class="aux-asist-filter__title">Filtrar registros</span>
        </div>
        <div class="aux-asist-filter__body">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fw-semibold text-muted" style="font-size:.82rem;">Desde</label>
                        <input type="date" name="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fw-semibold text-muted" style="font-size:.82rem;">Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fw-semibold text-muted" style="font-size:.82rem;">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="">Todos</option>
                            <option value="ENTRADA" @selected($tipo === 'ENTRADA')>ENTRADA</option>
                            <option value="SALIDA"  @selected($tipo === 'SALIDA')>SALIDA</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 d-flex gap-2">
                        <button class="jp-btn-search flex-grow-1" type="submit">
                            <i data-feather="search"></i> Filtrar
                        </button>
                        <a class="btn btn-outline-secondary" href="{{ route('auxiliar.mis-asistencias.index') }}"
                           title="Limpiar filtros" style="border-radius:999px; padding:8px 16px;">
                            <i data-feather="x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── TABLA ── --}}
    <div class="card" style="border-radius:var(--aux-radius);border:1px solid var(--aux-border);box-shadow:var(--aux-shadow-sm);">
        <div class="card-header d-flex align-items-center justify-content-between gap-3"
             style="background:#fff;border-bottom:1px solid var(--aux-border);border-radius:var(--aux-radius) var(--aux-radius) 0 0;padding:16px 20px;">
            <div class="d-flex align-items-center gap-2">
                <div class="aux-card-icon"><i data-feather="clock"></i></div>
                <div>
                    <p class="aux-card-title">Registros de asistencia</p>
                    <p class="aux-card-sub">Ordenados del más reciente al más antiguo</p>
                </div>
            </div>
            @if($asistencias->total() > 0)
                <span class="aux-asist-hero__pill"
                      style="background:rgba(28, 41, 126,.08);border-color:rgba(28, 41, 126,.2);color:var(--aux-indigo-700);">
                    <i data-feather="database"></i>
                    {{ $asistencias->total() }} {{ $asistencias->total() === 1 ? 'resultado' : 'resultados' }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table aux-asist-table mb-0">
                    <thead>
                        <tr>
                            <th><i data-feather="calendar" style="width:13px;height:13px;margin-right:5px;"></i>Fecha</th>
                            <th><i data-feather="sun" style="width:13px;height:13px;margin-right:5px;"></i>Día</th>
                            <th><i data-feather="tag" style="width:13px;height:13px;margin-right:5px;"></i>Tipo</th>
                            <th><i data-feather="clock" style="width:13px;height:13px;margin-right:5px;"></i>Hora</th>
                            <th><i data-feather="user" style="width:13px;height:13px;margin-right:5px;"></i>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asistencias as $a)
                            <tr>
                                <td>
                                    <span class="aux-asist-date">
                                        <i data-feather="calendar"></i>
                                        {{ $a->fecha?->format('d/m/Y') ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:.85rem;">
                                    {{ $a->fecha?->locale('es')->dayName ?? '-' }}
                                </td>
                                <td>
                                    @if(strtoupper($a->tipo) === 'ENTRADA')
                                        <span class="aux-asist-badge aux-asist-badge--entrada">
                                            <i data-feather="log-in"></i> ENTRADA
                                        </span>
                                    @else
                                        <span class="aux-asist-badge aux-asist-badge--salida">
                                            <i data-feather="log-out"></i> SALIDA
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="aux-asist-time">
                                        <i data-feather="clock"></i>
                                        {{ $a->created_at?->timezone('America/Lima')->format('H:i') ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="aux-asist-by">
                                        <i data-feather="user-check"></i>
                                        {{ $a->registrador?->name ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <div class="aux-asist-empty">
                                        <div class="aux-asist-empty__icon">
                                            <i data-feather="clock"></i>
                                        </div>
                                        <p class="aux-asist-empty__title">Sin registros de asistencia</p>
                                        <p class="aux-asist-empty__sub">
                                            @if($desde || $hasta || $tipo)
                                                No hay resultados para los filtros aplicados.
                                            @else
                                                Aún no se han registrado asistencias para tu cuenta.<br>
                                                <span style="font-size:.8rem;">La secretaría debe escanear tu carnet QR.</span>
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($asistencias->hasPages())
                <div class="px-4 py-3" style="border-top:1px solid var(--aux-border);">
                    {{ $asistencias->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
