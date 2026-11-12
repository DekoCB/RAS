@extends('layouts.mantis')

@section('title', 'Historial de Caja – Secretarias')
@section('page-title', 'Historial de Caja por Secretaria')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    <style>
        .hcs-hero {
            background: linear-gradient(135deg, #162061, #1c297e, #1f2e8f);
            border-radius: 20px; padding: 24px 30px; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 20px rgba(22, 32, 97,.10); margin-bottom: 24px;
        }
        .hcs-hero-title { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .hcs-hero-sub   { opacity: .85; font-size: .9rem; margin-top: 4px; }

        .hcs-filters {
            background: #fff; border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9;
            padding: 18px 22px; margin-bottom: 20px;
        }
        .hcs-filters form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .hcs-filter-group { display: flex; flex-direction: column; gap: 4px; }
        .hcs-filter-group label { font-size: .75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        .hcs-filter-group select,
        .hcs-filter-group input { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 7px 12px; font-size: .875rem; color: #374151; outline: none; }
        .hcs-filter-group select:focus,
        .hcs-filter-group input:focus { border-color: #1c297e; }

        .btn-filtrar {
            background: linear-gradient(135deg, #162061, #1c297e);
            color: #fff; border: none; border-radius: 10px;
            padding: 8px 20px; font-size: .875rem; font-weight: 700;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
            transition: opacity .2s;
        }
        .btn-filtrar:hover { opacity: .9; }
        .btn-limpiar {
            background: #f1f5f9; color: #64748b; border: none;
            border-radius: 10px; padding: 8px 16px; font-size: .875rem;
            font-weight: 600; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background .2s;
        }
        .btn-limpiar:hover { background: #e2e8f0; color: #374151; }

        .hcs-stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px; margin-bottom: 20px;
        }
        .hcs-stat-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9;
            padding: 16px 20px;
        }
        .hcs-stat-label { font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
        .hcs-stat-value { font-size: 1.5rem; font-weight: 800; color: #1e293b; }
        .hcs-stat-value.verde  { color: #15803d; }
        .hcs-stat-value.rojo   { color: #1c297e; }
        .hcs-stat-value.azul   { color: #1f2e8d; }

        .hcs-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            border: 1.5px solid #f1f5f9; overflow: hidden;
        }
        .hcs-table { width: 100%; border-collapse: collapse; }
        .hcs-table th {
            background: #f8fafc; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em; color: #94a3b8;
            padding: 11px 16px; text-align: left; white-space: nowrap;
        }
        .hcs-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; font-size: .875rem; color: #374151; }
        .hcs-table tr:last-child td { border-bottom: none; }
        .hcs-table tr:hover td { background: #fafafa; }

        .badge-abierta { background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 700; white-space: nowrap; }
        .badge-cerrada { background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 700; white-space: nowrap; }

        .secretaria-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f2f7fe; color: #1c297e;
            border-radius: 20px; padding: 3px 10px;
            font-size: .78rem; font-weight: 700;
        }
        .monto-efec { color: #15803d; font-weight: 600; }
        .monto-dig  { color: #1f2e8d; font-weight: 600; }
        .monto-tot  { color: #1e293b; font-weight: 800; }

        .empty-state { padding: 50px; text-align: center; color: #94a3b8; }
        .empty-state svg { margin-bottom: 12px; }
    </style>
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

    {{-- Hero --}}
    <div class="hcs-hero">
        <div>
            <p class="hcs-hero-title">
                <i data-feather="archive" style="width:24px;height:24px;vertical-align:middle;margin-right:8px;"></i>
                Historial de Caja – Secretarias
            </p>
            <p class="hcs-hero-sub">Registro de todas las sesiones de caja abiertas y cerradas por secretaria</p>
        </div>
        <a href="{{ route('admin-sistema.caja.index') }}" class="btn-limpiar" style="color:#fff;background:rgba(255,255,255,.2);">
            <i data-feather="archive" style="width:15px;height:15px;"></i>
            Mi Caja
        </a>
    </div>

    {{-- Filtros --}}
    <div class="hcs-filters">
        <form method="GET" action="{{ route('admin-sistema.caja.historial-secretarias') }}">
            <div class="hcs-filter-group">
                <label>Secretaria</label>
                <select name="usuario_id" style="min-width:200px;">
                    <option value="">Todas</option>
                    @foreach($secretarias as $sec)
                        <option value="{{ $sec->id }}" {{ request('usuario_id') == $sec->id ? 'selected' : '' }}>
                            {{ $sec->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="hcs-filter-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="abierta" {{ request('estado') === 'abierta' ? 'selected' : '' }}>Abierta</option>
                    <option value="cerrada" {{ request('estado') === 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                </select>
            </div>
            <div class="hcs-filter-group">
                <label>Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>
            <div class="hcs-filter-group">
                <label>Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
            </div>
            <button type="submit" class="btn-filtrar">
                <i data-feather="search" style="width:14px;height:14px;"></i>
                Filtrar
            </button>
            <a href="{{ route('admin-sistema.caja.historial-secretarias') }}" class="btn-limpiar">
                <i data-feather="x" style="width:14px;height:14px;"></i>
                Limpiar
            </a>
        </form>
    </div>

    {{-- Estadísticas del listado actual --}}
    @php
        $totalAbiertas  = $cajas->getCollection()->where('estado', 'abierta')->count();
        $totalCerradas  = $cajas->getCollection()->where('estado', 'cerrada')->count();
        $sumaEfectivo   = 0;
        $sumaDigital    = 0;
        foreach ($cajas->getCollection() as $c) {
            $pg = $c->getPagos();
            $tm = collect();
            foreach ($pg as $_p) {
                foreach ($_p->metodos_decoded as $_m) {
                    $_n = $_m['metodo'] ?? 'Otro';
                    $tm[$_n] = ($tm[$_n] ?? 0) + (float)($_m['monto'] ?? 0);
                }
            }
            $sumaEfectivo += (float)($tm->get('Efectivo', 0));
            $sumaDigital  += (float)($tm->except('Efectivo')->sum());
        }
        $sumaTotal = $sumaEfectivo + $sumaDigital;
    @endphp

    <div class="hcs-stats">
        <div class="hcs-stat-card">
            <div class="hcs-stat-label">Total Cajas (página)</div>
            <div class="hcs-stat-value">{{ $cajas->count() }}</div>
        </div>
        <div class="hcs-stat-card">
            <div class="hcs-stat-label">Abiertas</div>
            <div class="hcs-stat-value verde">{{ $totalAbiertas }}</div>
        </div>
        <div class="hcs-stat-card">
            <div class="hcs-stat-label">Cerradas</div>
            <div class="hcs-stat-value" style="color:#64748b;">{{ $totalCerradas }}</div>
        </div>
        <div class="hcs-stat-card">
            <div class="hcs-stat-label">Efectivo (página)</div>
            <div class="hcs-stat-value verde">S/ {{ number_format($sumaEfectivo, 2) }}</div>
        </div>
        <div class="hcs-stat-card">
            <div class="hcs-stat-label">Digital (página)</div>
            <div class="hcs-stat-value azul">S/ {{ number_format($sumaDigital, 2) }}</div>
        </div>
        <div class="hcs-stat-card">
            <div class="hcs-stat-label">Total Cobrado (página)</div>
            <div class="hcs-stat-value rojo">S/ {{ number_format($sumaTotal, 2) }}</div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="hcs-card">
        @if($cajas->isEmpty())
            <div class="empty-state">
                <i data-feather="inbox" style="width:42px;height:42px;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;"></i>
                <p style="margin:0;">No se encontraron cajas con los filtros seleccionados.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="hcs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Secretaria</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Saldo Inicial</th>
                            <th>Efectivo</th>
                            <th>Digital</th>
                            <th>Total Cobrado</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cajas as $caja)
                            @php
                                $pagos  = $caja->getPagos();
                                $totMet = collect();
                                foreach ($pagos as $_p) {
                                    foreach ($_p->metodos_decoded as $_m) {
                                        $_n = $_m['metodo'] ?? 'Otro';
                                        $totMet[$_n] = ($totMet[$_n] ?? 0) + (float)($_m['monto'] ?? 0);
                                    }
                                }
                                $efec   = (float)($totMet->get('Efectivo', 0));
                                $dig    = (float)($totMet->except('Efectivo')->sum());
                                $total  = $efec + $dig;
                            @endphp
                            <tr>
                                <td style="color:#94a3b8;font-size:.8rem;">{{ $caja->id }}</td>
                                <td>
                                    <span class="secretaria-chip">
                                        <i data-feather="user" style="width:12px;height:12px;"></i>
                                        {{ $caja->usuario->name ?? '—' }}
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <div style="font-weight:600;">{{ $caja->fecha_apertura->format('d/m/Y') }}</div>
                                    <div style="font-size:.78rem;color:#94a3b8;">{{ $caja->fecha_apertura->format('H:i') }}</div>
                                </td>
                                <td style="white-space:nowrap;">
                                    @if($caja->fecha_cierre)
                                        <div style="font-weight:600;">{{ $caja->fecha_cierre->format('d/m/Y') }}</div>
                                        <div style="font-size:.78rem;color:#94a3b8;">{{ $caja->fecha_cierre->format('H:i') }}</div>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>S/ {{ number_format($caja->saldo_inicial, 2) }}</td>
                                <td class="monto-efec">S/ {{ number_format($efec, 2) }}</td>
                                <td class="monto-dig">S/ {{ number_format($dig, 2) }}</td>
                                <td class="monto-tot">S/ {{ number_format($total, 2) }}</td>
                                <td>
                                    @if($caja->estado === 'abierta')
                                        <span class="badge-abierta">Abierta</span>
                                    @else
                                        <span class="badge-cerrada">Cerrada</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin-sistema.caja.show', $caja->id) }}"
                                       style="font-size:.78rem;color:#1c297e;font-weight:600;text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;gap:4px;">
                                        <i data-feather="eye" style="width:13px;height:13px;"></i>
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:14px 16px;">
                {{ $cajas->links() }}
            </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
