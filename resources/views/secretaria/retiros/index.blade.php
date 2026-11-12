@extends('layouts.mantis')

@section('title', 'Retiros de Alumnos')
@section('page-title', 'Retiros')
@section('body-class', auth()->user()?->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')
@php $rpR = auth()->user()?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp

@push('styles')
<link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@if(auth()->user()?->hasRole('SuperAdmin'))
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endif
<style>
    .jp-retiro-metric {
        border-radius: 16px;
        padding: 1.1rem 1.4rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid transparent;
    }
    .jp-retiro-metric__icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .jp-retiro-metric__icon i { width: 22px; height: 22px; }
    .jp-retiro-metric__label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .15rem; }
    .jp-retiro-metric__value { font-size: 1.6rem; font-weight: 800; line-height: 1; }

    .jp-retiro-metric--blue   { background: #f5f9ff; border-color: #cae0fe; }
    .jp-retiro-metric--blue   .jp-retiro-metric__icon { background: #e2eefe; color: #1c297e; }
    .jp-retiro-metric--blue   .jp-retiro-metric__label { color: #1c297e; }
    .jp-retiro-metric--blue   .jp-retiro-metric__value { color: #162061; }

    .jp-retiro-metric--sky { background: #edf8ff; border-color: #ccd2fe; }
    .jp-retiro-metric--sky .jp-retiro-metric__icon { background: #d5f0ff; color: #213196; }
    .jp-retiro-metric--sky .jp-retiro-metric__label { color: #213196; }
    .jp-retiro-metric--sky .jp-retiro-metric__value { color: #151f5f; }

    .jp-retiro-metric--slate { background: #f8fafc; border-color: #e2e8f0; }
    .jp-retiro-metric--slate .jp-retiro-metric__icon { background: #e2e8f0; color: #475569; }
    .jp-retiro-metric--slate .jp-retiro-metric__label { color: #475569; }
    .jp-retiro-metric--slate .jp-retiro-metric__value { color: #0f132a; }

    .jp-retiro-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .75rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        background: #e2eefe;
        color: #1c297e;
        border: 1px solid rgba(28, 41, 126,.25);
    }
    .jp-retiro-badge i { width: 13px; height: 13px; }

    .jp-retiro-anuladas {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .28rem .7rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: #d5f0ff;
        color: #1d2a82;
        border: 1px solid rgba(33, 49, 150,.25);
    }
    .jp-retiro-anuladas i { width: 12px; height: 12px; }

    .jp-btn-nuevo-retiro {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem 1.25rem;
        background: linear-gradient(135deg, #1c297e, #18246e);
        color: #fff;
        border: 0;
        border-radius: 12px;
        font-weight: 700;
        font-size: .88rem;
        text-decoration: none;
        transition: all .2s ease;
        box-shadow: 0 8px 16px rgba(28, 41, 126,.25);
    }
    .jp-btn-nuevo-retiro:hover {
        background: linear-gradient(135deg, #18246e, #162061);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(28, 41, 126,.3);
    }
    .jp-btn-nuevo-retiro i { width: 16px; height: 16px; }

    .jp-table-action--view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        text-decoration: none;
        transition: all .18s ease;
    }
    .jp-table-action--view:hover {
        background: #e2eefe;
        border-color: #cae0fe;
        color: #1c297e;
        transform: translateY(-1px);
    }
    .jp-table-action--view i { width: 15px; height: 15px; }

    .jp-motivo-text {
        max-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
    }
</style>
@endpush

@section('sidebar')
    @if(auth()->user()?->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="card jp-docente-filter border-0">
            <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h5 class="jp-docente-filter-title">Retiros de Alumnos</h5>
                    <p class="jp-docente-filter-sub">
                        Gestión de retiros y anulación automática de cobranza pendiente.
                    </p>
                    <div class="jp-docente-filter-meta">
                        <span class="jp-docente-filter-pill">
                            <i data-feather="calendar"></i>
                            Año {{ $anioActivo?->nombre ?? now()->year }}
                        </span>
                        <span class="jp-docente-filter-pill is-muted">
                            <i data-feather="shield"></i>
                            Pagos realizados intactos
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 pt-1">
                    <a href="{{ route($rpR . 'retiros.create') }}" class="jp-btn-nuevo-retiro">
                        <i data-feather="user-minus"></i>
                        Registrar Retiro
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Métricas --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-sm-4">
                <div class="jp-retiro-metric jp-retiro-metric--blue">
                    <div class="jp-retiro-metric__icon"><i data-feather="user-minus"></i></div>
                    <div>
                        <div class="jp-retiro-metric__label">Total Retirados</div>
                        <div class="jp-retiro-metric__value">{{ $totalRetirados }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="jp-retiro-metric jp-retiro-metric--sky">
                    <div class="jp-retiro-metric__icon"><i data-feather="x-circle"></i></div>
                    <div>
                        <div class="jp-retiro-metric__label">Deudas Anuladas</div>
                        <div class="jp-retiro-metric__value">{{ $totalDeudasAnuladas }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="jp-retiro-metric jp-retiro-metric--slate">
                    <div class="jp-retiro-metric__icon"><i data-feather="trending-down"></i></div>
                    <div>
                        <div class="jp-retiro-metric__label">Monto Anulado</div>
                        <div class="jp-retiro-metric__value">S/ {{ number_format($montoAnulado, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="col-12">
            <div class="alert alert-success d-flex align-items-center gap-2 border-0 rounded-3" style="background:#ecfdf5;color:#065f46;border-left:4px solid #10b981!important;">
                <i data-feather="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('info'))
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center gap-2 border-0 rounded-3" style="background:#eff6ff;color:#1b2779;border-left:4px solid #25359c!important;">
                <i data-feather="info" style="width:18px;height:18px;flex-shrink:0;"></i>
                {{ session('info') }}
            </div>
        </div>
    @endif

    {{-- Filtro --}}
    <div class="col-12">
        <div class="jp-soft-card card">
            <div class="card-body">
                <form method="GET" action="{{ route($rpR . 'retiros.index') }}" class="d-flex gap-3 align-items-end flex-wrap">
                    <div class="flex-grow-1" style="max-width:320px;">
                        <label class="form-label fw-700 small text-muted mb-1">Buscar alumno</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;">
                                <i data-feather="search" style="width:15px;height:15px;color:#94a3b8;"></i>
                            </span>
                            <input type="text" name="q" value="{{ request('q') }}"
                                class="form-control"
                                placeholder="Nombre o DNI..."
                                style="border-left:0;border-color:#e2e8f0;">
                        </div>
                    </div>
                    <button type="submit" class="jp-btn-search">
                        <i data-feather="search"></i> Buscar
                    </button>
                    @if(request('q'))
                        <a href="{{ route($rpR . 'retiros.index') }}" class="jp-docente-link">
                            <i data-feather="x"></i> Limpiar
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="col-12">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="jp-card-icon" style="background:linear-gradient(135deg,#18246e,#1c297e);">
                    <i data-feather="user-minus"></i>
                </div>
                <div>
                    <h5 class="jp-card-title mb-0">Listado de Retirados</h5>
                    <p class="jp-card-sub mb-0">Año {{ $anioActivo?->nombre ?? now()->year }}</p>
                </div>
            </div>
            <div class="card-body p-0">
                @if($retirados->isEmpty())
                    <div class="jp-admin-empty" style="padding:3.5rem;">
                        <i data-feather="inbox"></i>
                        <p>No hay alumnos retirados{{ request('q') ? ' con ese criterio' : ' este año' }}.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table jp-table mb-0">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Grado / Sección</th>
                                    <th>Fecha Retiro</th>
                                    <th>Motivo</th>
                                    <th>Deudas Anuladas</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($retirados as $m)
                                    @php
                                        $anuladas = $m->deudas->where('estado', 'anulado');
                                        $montoAn  = $anuladas->sum('saldo');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-700">{{ $m->estudiante?->apellidos }}, {{ $m->estudiante?->nombres }}</div>
                                            <small class="text-muted">DNI: {{ $m->estudiante?->dni }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-600">{{ $m->grado?->nombre }}</span>
                                            <span class="text-muted"> — {{ $m->seccion?->nombre }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-700">{{ $m->fecha_retiro?->format('d/m/Y') ?? '—' }}</span>
                                        </td>
                                        <td>
                                            @if($m->motivo_retiro)
                                                <span class="jp-motivo-text small text-muted" title="{{ $m->motivo_retiro }}">
                                                    {{ $m->motivo_retiro }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($anuladas->count() > 0)
                                                <div class="jp-retiro-anuladas">
                                                    <i data-feather="x-circle"></i>
                                                    {{ $anuladas->count() }} deuda(s)
                                                </div>
                                                <div class="small text-muted mt-1">S/ {{ number_format($montoAn, 2) }}</div>
                                            @else
                                                <span class="jp-pill jp-pill--neutral">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route($rpR . 'retiros.show', $m) }}"
                                               class="jp-table-action--view"
                                               title="Ver detalle">
                                                <i data-feather="eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($retirados->hasPages())
                        <div class="p-3">{{ $retirados->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush
