@extends('layouts.mantis')

@section('title', 'Detalle de Retiro')
@section('page-title', 'Detalle de Retiro')
@section('body-class', auth()->user()?->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')
@php $rpR = auth()->user()?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp

@push('styles')
<link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@if(auth()->user()?->hasRole('SuperAdmin'))
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endif
<style>
    .jp-show-hero {
        background: linear-gradient(135deg, #162061 0%, #18246e 50%, #1c297e 100%);
        border-radius: 20px;
        padding: 2rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .jp-show-hero::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
    }
    .jp-show-hero::after {
        content: '';
        position: absolute;
        bottom: -60px; left: -30px;
        width: 240px; height: 240px;
        background: rgba(255,255,255,.04);
        border-radius: 50%;
    }
    .jp-show-hero__avatar {
        width: 64px; height: 64px;
        border-radius: 18px;
        background: rgba(255,255,255,.15);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .jp-show-hero__avatar i { width: 30px; height: 30px; }
    .jp-show-hero__nombre { font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
    .jp-show-hero__sub { font-size: .88rem; opacity: .8; margin-top: .25rem; }
    .jp-show-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 999px;
        padding: .3rem .85rem;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-top: .6rem;
    }
    .jp-show-hero__badge i { width: 13px; height: 13px; }

    .jp-show-fecha-card {
        background: #f5f9ff;
        border: 1.5px solid #cae0fe;
        border-radius: 16px;
        padding: 1.4rem;
        text-align: center;
    }
    .jp-show-fecha-card .label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #1c297e; margin-bottom: .4rem; }
    .jp-show-fecha-card .fecha { font-size: 2rem; font-weight: 800; color: #162061; line-height: 1; }
    .jp-show-fecha-card .sub { font-size: .8rem; color: #94a3b8; margin-top: .35rem; }

    .jp-show-motivo-card {
        background: #fafafa;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.2rem 1.4rem;
        height: 100%;
    }
    .jp-show-motivo-card .label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: .5rem; display: flex; align-items: center; gap: .4rem; }
    .jp-show-motivo-card .label i { width: 14px; height: 14px; }
    .jp-show-motivo-card .texto { color: #374151; font-size: .92rem; line-height: 1.55; }

    .jp-show-table th {
        background: #f5f9ff;
        color: #162061;
        font-weight: 700;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 1.5px solid #cae0fe;
        padding: .7rem 1rem;
    }
    .jp-show-table td {
        padding: .65rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
        font-size: .88rem;
    }
    .jp-show-table tbody tr:last-child td { border-bottom: 0; }
    .jp-show-table tbody tr:hover td { background: #f7faff; }

    .jp-show-pagos-card {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        border-radius: 16px;
        padding: 1.2rem 1.4rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .jp-show-pagos-icon {
        width: 44px; height: 44px;
        border-radius: 14px;
        background: #dcfce7;
        color: #16a34a;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .jp-show-pagos-icon i { width: 20px; height: 20px; }

    .jp-breadcrumb {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .82rem;
        color: #94a3b8;
        margin-bottom: .25rem;
    }
    .jp-breadcrumb a { color: #94a3b8; text-decoration: none; }
    .jp-breadcrumb a:hover { color: #1c297e; }
    .jp-breadcrumb i { width: 14px; height: 14px; }
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
<div class="row g-4 justify-content-center">

    {{-- Breadcrumb + volver --}}
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between">
            <div class="jp-breadcrumb">
                <a href="{{ route($rpR . 'retiros.index') }}">Retiros</a>
                <i data-feather="chevron-right"></i>
                <span>Detalle del retiro</span>
            </div>
            <a href="{{ route($rpR . 'retiros.index') }}" class="jp-docente-link">
                <i data-feather="arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    {{-- Hero del alumno --}}
    <div class="col-lg-9">
        <div class="jp-show-hero">
            <div class="d-flex align-items-start gap-4 position-relative" style="z-index:1;">
                <div class="jp-show-hero__avatar">
                    <i data-feather="user"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="jp-show-hero__nombre">
                        {{ $matricula->estudiante?->apellidos }}, {{ $matricula->estudiante?->nombres }}
                    </div>
                    <div class="jp-show-hero__sub">
                        DNI: {{ $matricula->estudiante?->dni }}
                        &nbsp;·&nbsp; {{ $matricula->grado?->nombre }} — {{ $matricula->seccion?->nombre }}
                        &nbsp;·&nbsp; {{ $matricula->anioEscolar?->nombre }}
                    </div>
                    <div class="jp-show-hero__badge">
                        <i data-feather="user-minus"></i>
                        Retirado
                    </div>
                </div>
                <div class="text-end" style="opacity:.75;font-size:.8rem;">
                    Matrícula #{{ $matricula->id }}
                </div>
            </div>
        </div>
    </div>

    {{-- Fecha + Motivo --}}
    <div class="col-lg-9">
        <div class="row g-3">
            <div class="col-sm-4">
                <div class="jp-show-fecha-card">
                    <div class="label">Fecha de Retiro</div>
                    <div class="fecha">{{ $matricula->fecha_retiro?->format('d/m/Y') ?? '—' }}</div>
                    <div class="sub">{{ $matricula->fecha_retiro?->translatedFormat('l, d \d\e F \d\e Y') ?? '' }}</div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="jp-show-motivo-card">
                    <div class="label">
                        <i data-feather="file-text"></i>
                        Motivo del retiro
                    </div>
                    <div class="texto">{{ $matricula->motivo_retiro ?? 'Sin motivo registrado.' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deudas anuladas --}}
    @if($deudasAnuladas->isNotEmpty())
    <div class="col-lg-9">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="jp-card-icon" style="background:linear-gradient(135deg,#213196,#1d2a82);">
                    <i data-feather="x-circle"></i>
                </div>
                <div>
                    <h5 class="jp-card-title mb-0">Deudas Anuladas</h5>
                    <p class="jp-card-sub mb-0">
                        {{ $deudasAnuladas->count() }} deuda(s) — Total S/ {{ number_format($deudasAnuladas->sum('saldo'), 2) }}
                    </p>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table jp-show-table mb-0">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Vencimiento</th>
                            <th class="text-end">Monto</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deudasAnuladas as $d)
                            <tr>
                                <td class="fw-600">{{ $d->concepto?->nombre ?? $d->periodo ?? '—' }}</td>
                                <td class="text-muted">
                                    {{ $d->fecha_vencimiento ? \Carbon\Carbon::parse($d->fecha_vencimiento)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-end fw-700" style="color:#151f5f;">
                                    S/ {{ number_format($d->saldo, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="jp-pill jp-pill--danger" style="font-size:.7rem;">
                                        <i data-feather="x" style="width:11px;height:11px;"></i> Anulado
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Pagos intactos / deudas pagadas --}}
    <div class="col-lg-9">
        <div class="jp-show-pagos-card">
            <div class="jp-show-pagos-icon">
                <i data-feather="shield"></i>
            </div>
            <div>
                <div class="fw-700" style="color:#15803d;">Pagos realizados — intactos</div>
                <div class="text-muted small mt-1">
                    @if($deudasPagadas->isNotEmpty())
                        {{ $deudasPagadas->count() }} pago(s) registrado(s) por un total de
                        <strong>S/ {{ number_format($deudasPagadas->sum('saldo'), 2) }}</strong>.
                        Los pagos previos al retiro no se modifican.
                    @else
                        No se registran pagos previos en esta matrícula.
                        Los pagos anteriores al retiro nunca se modifican.
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Deudas pendientes (si quedaron sin anular, anteriores a la fecha de retiro) --}}
    @if($deudasPendientes->isNotEmpty())
    <div class="col-lg-9">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="jp-card-icon" style="background:linear-gradient(135deg,#1f2e8e,#1b287b);">
                    <i data-feather="clock"></i>
                </div>
                <div>
                    <h5 class="jp-card-title mb-0">Deudas Anteriores al Retiro</h5>
                    <p class="jp-card-sub mb-0">Vencidas antes de la fecha de retiro — no fueron anuladas</p>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table jp-show-table mb-0">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Vencimiento</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deudasPendientes as $d)
                            <tr>
                                <td class="fw-600">{{ $d->concepto?->nombre ?? $d->periodo ?? '—' }}</td>
                                <td class="text-muted">
                                    {{ $d->fecha_vencimiento ? \Carbon\Carbon::parse($d->fecha_vencimiento)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-end fw-700">S/ {{ number_format($d->saldo, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush
