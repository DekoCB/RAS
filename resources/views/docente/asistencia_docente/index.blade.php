@extends('layouts.mantis')

@section('title', 'Mis asistencias | Docente')
@section('page-title', 'Mis asistencias')
@section('body-class', 'role-docente')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/docente-dashboard.css') }}">
    <style>
        /* ── Mis Asistencias: hero de sección ── */
        .doc-asist-hero {
            border-radius: var(--doc-radius);
            overflow: hidden;
            position: relative;
            background: var(--doc-gradient);
            box-shadow: var(--doc-shadow-lg);
            border: none;
            margin-bottom: 22px;
        }
        .doc-asist-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 80% 20%, rgba(255,255,255,.12) 0%, transparent 55%),
                radial-gradient(ellipse at 10% 90%, rgba(0,0,0,.12) 0%, transparent 40%);
            pointer-events: none;
        }
        .doc-asist-hero__inner {
            position: relative; z-index: 1;
            padding: 26px 30px;
            display: flex; align-items: center;
            justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
        }
        .doc-asist-hero__left { display: flex; align-items: center; gap: 16px; }
        .doc-asist-hero__icon {
            width: 56px; height: 56px; border-radius: 18px;
            background: rgba(255,255,255,.2); backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,.3);
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .doc-asist-hero__icon i { width: 26px; height: 26px; color: #fff; }
        .doc-asist-hero__title {
            font-size: 1.5rem; font-weight: 900; color: #fff; margin: 0 0 4px;
            text-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .doc-asist-hero__sub { font-size: .87rem; color: rgba(255,255,255,.8); margin: 0; }
        .doc-asist-hero__pills { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .doc-asist-hero__pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(0,0,0,.18); border: 1px solid rgba(255,255,255,.22);
            color: #fff; padding: 6px 14px; border-radius: 999px;
            font-size: .78rem; font-weight: 700;
        }
        .doc-asist-hero__pill i { width: 13px; height: 13px; }
        .doc-asist-hero__pill.is-light {
            background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.35);
        }

        /* ── Filtro mejorado ── */
        .doc-asist-filter {
            background: #fff;
            border: 1px solid var(--doc-border);
            border-radius: var(--doc-radius);
            box-shadow: var(--doc-shadow-sm);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .doc-asist-filter__header {
            display: flex; align-items: center; gap: 10px;
            padding: 16px 22px 14px;
            border-bottom: 1px solid var(--doc-border);
            background: linear-gradient(180deg, #f5faff 0%, #fff 100%);
        }
        .doc-asist-filter__icon {
            width: 34px; height: 34px; border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--doc-gradient); color: #fff; flex-shrink: 0;
        }
        .doc-asist-filter__icon i { width: 15px; height: 15px; }
        .doc-asist-filter__title { font-weight: 800; color: var(--doc-ink); margin: 0; font-size: .95rem; }
        .doc-asist-filter__body { padding: 18px 22px; }

        /* ── Badge ENTRADA / SALIDA ── */
        .doc-asist-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 999px;
            font-size: .74rem; font-weight: 800;
            letter-spacing: .4px; border: 1px solid transparent;
        }
        .doc-asist-badge i { width: 11px; height: 11px; }
        .doc-asist-badge--entrada {
            background: rgba(22,163,74,.1); color: #15803d;
            border-color: rgba(22,163,74,.25);
        }
        .doc-asist-badge--salida {
            background: rgba(28, 41, 126,.08); color: #1c297e;
            border-color: rgba(28, 41, 126,.2);
        }

        /* ── Hora pill ── */
        .doc-asist-time {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .84rem; font-weight: 700;
            color: var(--doc-sky-900);
        }
        .doc-asist-time i { width: 13px; height: 13px; color: var(--doc-sky-500); }

        /* ── Registrado por ── */
        .doc-asist-by {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .84rem; color: var(--doc-muted);
        }
        .doc-asist-by i { width: 13px; height: 13px; }

        /* ── Tabla de asistencias ── */
        .doc-asist-table thead th {
            background: var(--doc-gradient);
            color: #fff; font-weight: 700; font-size: .82rem;
            padding: 13px 16px; border: none; white-space: nowrap;
        }
        .doc-asist-table thead th:first-child { border-radius: 0; }
        .doc-asist-table tbody td {
            padding: 13px 16px; vertical-align: middle;
            border-top: 1px solid var(--doc-border); font-size: .9rem;
        }
        .doc-asist-table tbody tr:hover { background: rgba(33, 49, 150,.04); }

        /* ── Fecha cell ── */
        .doc-asist-date {
            font-weight: 700; color: var(--doc-ink);
            display: inline-flex; align-items: center; gap: 7px;
            font-size: .9rem;
        }
        .doc-asist-date i { width: 14px; height: 14px; color: var(--doc-sky-500); flex-shrink: 0; }

        /* ── Empty state ── */
        .doc-asist-empty {
            text-align: center; padding: 48px 24px;
        }
        .doc-asist-empty__icon {
            width: 64px; height: 64px; border-radius: 20px;
            background: var(--doc-gradient); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(33, 49, 150,.2);
        }
        .doc-asist-empty__icon i { width: 28px; height: 28px; }
        .doc-asist-empty__title { font-weight: 800; color: var(--doc-ink); margin: 0 0 6px; }
        .doc-asist-empty__sub { color: var(--doc-muted); font-size: .88rem; margin: 0; }

        @media (max-width: 768px) {
            .doc-asist-hero__inner { flex-direction: column; align-items: flex-start; }
            .doc-asist-filter__body .row { gap: 4px; }
        }
    </style>
@endpush

@section('sidebar')
    @include('docente.partials.sidebar')
@endsection

@section('content')
    @if (session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    {{-- ── HERO ── --}}
    <div class="doc-asist-hero">
        <div class="doc-asist-hero__inner">
            <div class="doc-asist-hero__left">
                <div class="doc-asist-hero__icon">
                    <i data-feather="check-square"></i>
                </div>
                <div>
                    <h1 class="doc-asist-hero__title">Mis asistencias</h1>
                    <p class="doc-asist-hero__sub">Historial de entradas y salidas registradas por la secretaría</p>
                </div>
            </div>
            <div class="doc-asist-hero__pills">
                <span class="doc-asist-hero__pill is-light">
                    <i data-feather="calendar"></i>
                    {{ now()->format('d/m/Y') }}
                </span>
                <span class="doc-asist-hero__pill">
                    <i data-feather="list"></i>
                    {{ $asistencias->total() }} {{ $asistencias->total() === 1 ? 'registro' : 'registros' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── FILTRO ── --}}
    <div class="doc-asist-filter">
        <div class="doc-asist-filter__header">
            <div class="doc-asist-filter__icon">
                <i data-feather="filter"></i>
            </div>
            <span class="doc-asist-filter__title">Filtrar registros</span>
        </div>
        <div class="doc-asist-filter__body">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fw-600 text-muted" style="font-size:.82rem;">Desde</label>
                        <input type="date" name="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fw-600 text-muted" style="font-size:.82rem;">Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fw-600 text-muted" style="font-size:.82rem;">Tipo</label>
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
                        <a class="btn btn-outline-secondary" href="{{ route('docente.asistencia-docente.index') }}"
                           title="Limpiar filtros" style="border-radius:999px; padding:8px 16px;">
                            <i data-feather="x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── TABLA ── --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="doc-card-icon">
                    <i data-feather="clock"></i>
                </div>
                <div>
                    <p class="doc-card-title">Registros de asistencia</p>
                    <p class="doc-card-sub">Ordenados del más reciente al más antiguo</p>
                </div>
            </div>
            @if($asistencias->total() > 0)
                <span class="doc-asist-hero__pill" style="background:rgba(33, 49, 150,.08);border-color:rgba(33, 49, 150,.2);color:#213196;">
                    <i data-feather="database"></i>
                    {{ $asistencias->total() }} {{ $asistencias->total() === 1 ? 'resultado' : 'resultados' }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table doc-asist-table mb-0">
                    <thead>
                        <tr>
                            <th><i data-feather="calendar" style="width:13px;height:13px;margin-right:5px;"></i>Fecha</th>
                            <th><i data-feather="tag" style="width:13px;height:13px;margin-right:5px;"></i>Tipo</th>
                            <th><i data-feather="clock" style="width:13px;height:13px;margin-right:5px;"></i>Hora</th>
                            <th><i data-feather="user" style="width:13px;height:13px;margin-right:5px;"></i>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asistencias as $a)
                            <tr>
                                <td>
                                    <span class="doc-asist-date">
                                        <i data-feather="calendar"></i>
                                        {{ $a->fecha?->format('d/m/Y') ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if(strtoupper($a->tipo) === 'ENTRADA')
                                        <span class="doc-asist-badge doc-asist-badge--entrada">
                                            <i data-feather="log-in"></i>
                                            ENTRADA
                                        </span>
                                    @else
                                        <span class="doc-asist-badge doc-asist-badge--salida">
                                            <i data-feather="log-out"></i>
                                            SALIDA
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="doc-asist-time">
                                        <i data-feather="clock"></i>
                                        {{ $a->created_at?->timezone('America/Lima')->format('H:i') ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="doc-asist-by">
                                        <i data-feather="user-check"></i>
                                        {{ $a->registradoPor?->name ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-0">
                                    <div class="doc-asist-empty">
                                        <div class="doc-asist-empty__icon">
                                            <i data-feather="check-square"></i>
                                        </div>
                                        <p class="doc-asist-empty__title">Sin registros de asistencia</p>
                                        <p class="doc-asist-empty__sub">
                                            @if($desde || $hasta || $tipo)
                                                No hay resultados para los filtros aplicados. Intenta con otros parámetros.
                                            @else
                                                Aún no se han registrado asistencias para tu cuenta.
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
                <div class="px-4 py-3 border-top" style="border-color:var(--doc-border) !important;">
                    {{ $asistencias->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
