@extends('layouts.mantis')

@section('title', 'Registrar Retiro')
@section('page-title', 'Registrar Retiro')
@section('body-class', auth()->user()?->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')
@php $rpR = auth()->user()?->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp

@push('styles')
<link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@if(auth()->user()?->hasRole('SuperAdmin'))
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endif
<style>
    .jp-retiro-step-label {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: #e2eefe;
        color: #1c297e;
        border-radius: 999px;
        padding: .25rem .8rem;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: .6rem;
    }

    /* Tabla de búsqueda */
    .jp-buscar-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s ease;
    }
    .jp-buscar-row:last-child { border-bottom: 0; }
    .jp-buscar-row:hover { background: #f7faff; }
    .jp-buscar-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #e2eefe;
        color: #1c297e;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .jp-buscar-avatar i { width: 18px; height: 18px; }
    .jp-buscar-info { flex: 1; min-width: 0; }
    .jp-buscar-nombre { font-weight: 700; font-size: .92rem; color: #1e293b; }
    .jp-buscar-meta { font-size: .78rem; color: #64748b; }
    .jp-btn-seleccionar {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .9rem;
        background: linear-gradient(135deg,#1c297e,#18246e);
        color: #fff;
        border: 0;
        border-radius: 10px;
        font-weight: 700;
        font-size: .8rem;
        text-decoration: none;
        transition: all .18s ease;
        flex-shrink: 0;
    }
    .jp-btn-seleccionar:hover { background: linear-gradient(135deg,#18246e,#162061); color:#fff; transform:translateY(-1px); }
    .jp-btn-seleccionar i { width: 14px; height: 14px; }

    /* Deudas preview tabla */
    .jp-deudas-preview {
        background: #f5faff;
        border: 1.5px solid #d4ecfd;
        border-radius: 16px;
        overflow: hidden;
    }
    .jp-deudas-preview-header {
        background: linear-gradient(135deg,#eaf6ff,#daefff);
        padding: .85rem 1.2rem;
        display: flex;
        align-items: center;
        gap: .6rem;
        border-bottom: 1px solid #d4ecfd;
    }
    .jp-deudas-preview-header i { width: 17px; height: 17px; color: #213196; }
    .jp-deudas-preview-table { width: 100%; }
    .jp-deudas-preview-table th {
        padding: .5rem 1rem;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #17226a;
        background: #e8f6ff;
        border-bottom: 1px solid #d4ecfd;
    }
    .jp-deudas-preview-table td {
        padding: .55rem 1rem;
        font-size: .85rem;
        border-bottom: 1px solid #eaf6fe;
        vertical-align: middle;
    }
    .jp-deudas-preview-table tr:last-child td { border-bottom: 0; }
    .jp-deudas-preview-total {
        background: #e6f5ff;
        padding: .65rem 1rem;
        display: flex;
        justify-content: flex-end;
        gap: .6rem;
        align-items: center;
        border-top: 1.5px solid #d4ecfd;
        font-weight: 700;
        color: #151f5f;
        font-size: .88rem;
    }

    /* Modal confirm */
    .jp-confirm-modal .modal-content {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 1.5rem 3rem rgba(21, 31, 94,.35);
    }
    .jp-confirm-modal .modal-header {
        background: linear-gradient(135deg,#162061,#1c297e);
        color: #fff;
        border-bottom: 0;
        padding: 1.2rem 1.5rem;
    }
    .jp-confirm-modal .modal-header .btn-close { filter: invert(1) grayscale(100%); opacity:.9; }
    .jp-confirm-modal .modal-body { padding: 1.5rem; }
    .jp-confirm-modal .modal-footer { border-top: 1px solid #f1f5f9; padding: 1rem 1.5rem; }
    .jp-btn-confirmar-retiro {
        background: linear-gradient(135deg,#1c297e,#18246e);
        color: #fff;
        border: 0;
        border-radius: 12px;
        padding: .6rem 1.4rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        box-shadow: 0 8px 16px rgba(28, 41, 126,.25);
        transition: all .18s ease;
    }
    .jp-btn-confirmar-retiro:hover { background: linear-gradient(135deg,#18246e,#162061); color:#fff; }
    .jp-btn-confirmar-retiro i { width: 16px; height: 16px; }
    .jp-confirm-list { background: #f5f9ff; border-radius: 12px; padding: .9rem 1rem; }
    .jp-confirm-list li { font-size: .88rem; color: #374151; padding: .2rem 0; }
    .jp-confirm-list li i { width: 14px; height: 14px; color: #1c297e; }
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

    @if(!isset($matricula))
    {{-- ============================================================ --}}
    {{-- PASO 1: BÚSQUEDA DE ALUMNO                                    --}}
    {{-- ============================================================ --}}

    <div class="col-lg-8">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="jp-card-icon" style="background:linear-gradient(135deg,#18246e,#1c297e);">
                    <i data-feather="search"></i>
                </div>
                <div>
                    <h5 class="jp-card-title mb-0">Buscar Alumno</h5>
                    <p class="jp-card-sub mb-0">Busca al alumno que deseas retirar</p>
                </div>
                <div class="ms-auto">
                    <a href="{{ route($rpR . 'retiros.index') }}" class="jp-docente-link">
                        <i data-feather="arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="jp-retiro-step-label">
                    <i data-feather="1"></i>
                    Paso 1 — Seleccionar alumno
                </div>
                <form method="GET" action="{{ route($rpR . 'retiros.create') }}" class="d-flex gap-3 align-items-end">
                    <div class="flex-grow-1">
                        <label class="form-label fw-700">Nombre o DNI del alumno</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;">
                                <i data-feather="user" style="width:15px;height:15px;color:#94a3b8;"></i>
                            </span>
                            <input type="text" name="q" value="{{ $q ?? '' }}"
                                class="form-control"
                                placeholder="Escribe nombre, apellido o DNI..."
                                style="border-left:0;border-color:#e2e8f0;"
                                autofocus>
                        </div>
                    </div>
                    <button type="submit" class="jp-btn-search">
                        <i data-feather="search"></i> Buscar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Resultados de búsqueda --}}
    @if(isset($resultados))
        <div class="col-lg-8">
            <div class="jp-soft-card card">
                <div class="card-header d-flex align-items-center gap-3">
                    <div class="jp-card-icon">
                        <i data-feather="list"></i>
                    </div>
                    <div>
                        <h5 class="jp-card-title mb-0">Resultados</h5>
                        <p class="jp-card-sub mb-0">
                            {{ $resultados->count() }} alumno(s) activo(s) encontrado(s)
                        </p>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($resultados->isEmpty())
                        <div class="jp-admin-empty" style="padding:2.5rem;">
                            <i data-feather="user-x"></i>
                            <p>No se encontraron alumnos activos con "{{ $q }}".</p>
                        </div>
                    @else
                        @foreach($resultados as $res)
                            <div class="jp-buscar-row">
                                <div class="jp-buscar-avatar">
                                    <i data-feather="user"></i>
                                </div>
                                <div class="jp-buscar-info">
                                    <div class="jp-buscar-nombre">
                                        {{ $res->estudiante?->apellidos }}, {{ $res->estudiante?->nombres }}
                                    </div>
                                    <div class="jp-buscar-meta">
                                        DNI: {{ $res->estudiante?->dni }} &nbsp;·&nbsp;
                                        {{ $res->grado?->nombre }} — {{ $res->seccion?->nombre }} &nbsp;·&nbsp;
                                        {{ $res->periodoAcademico?->nombre ?? 'Sin periodo' }}
                                    </div>
                                </div>
                                <a href="{{ route($rpR . 'retiros.create', ['matricula_id' => $res->id]) }}"
                                   class="jp-btn-seleccionar">
                                    <i data-feather="arrow-right"></i> Seleccionar
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @elseif(!isset($q) || $q === '')
        {{-- Estado inicial sin búsqueda --}}
        <div class="col-lg-8">
            <div class="text-center py-4" style="color:#94a3b8;">
                <i data-feather="user-search" style="width:48px;height:48px;opacity:.4;"></i>
                <p class="mt-3 mb-0">Ingresa el nombre o DNI del alumno para buscar.</p>
            </div>
        </div>
    @endif

    @else
    {{-- ============================================================ --}}
    {{-- PASO 2: CONFIRMACIÓN DEL RETIRO                               --}}
    {{-- ============================================================ --}}

    {{-- Info del alumno --}}
    <div class="col-lg-8">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="jp-card-icon">
                    <i data-feather="user"></i>
                </div>
                <div>
                    <h5 class="jp-card-title mb-0">Datos del Alumno</h5>
                    <p class="jp-card-sub mb-0">Matrícula #{{ $matricula->id }}</p>
                </div>
                <div class="ms-auto">
                    <a href="{{ route($rpR . 'retiros.create') }}" class="jp-docente-link">
                        <i data-feather="arrow-left"></i> Cambiar alumno
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="jp-retiro-step-label">
                    <i data-feather="check"></i>
                    Paso 2 — Confirmar retiro
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label small text-muted fw-600">Alumno</label>
                        <p class="fw-700 mb-0">{{ $matricula->estudiante?->apellidos }}, {{ $matricula->estudiante?->nombres }}</p>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small text-muted fw-600">DNI</label>
                        <p class="fw-700 mb-0">{{ $matricula->estudiante?->dni }}</p>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small text-muted fw-600">Grado / Sección</label>
                        <p class="fw-700 mb-0">{{ $matricula->grado?->nombre }} — {{ $matricula->seccion?->nombre }}</p>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small text-muted fw-600">Año Escolar</label>
                        <p class="fw-700 mb-0">{{ $matricula->anioEscolar?->nombre }}</p>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small text-muted fw-600">Periodo</label>
                        <p class="fw-700 mb-0">{{ $matricula->periodoAcademico?->nombre }}</p>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small text-muted fw-600">Estado actual</label>
                        <p class="fw-700 mb-0">
                            <span class="jp-pill jp-pill--ok">{{ ucfirst($matricula->estado) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deudas que se anularán --}}
    @if(isset($pensionesAnular) && $pensionesAnular->isNotEmpty())
    <div class="col-lg-8">
        <div class="jp-deudas-preview">
            <div class="jp-deudas-preview-header">
                <i data-feather="alert-triangle"></i>
                <span class="fw-700" style="color:#1d2a82;">
                    {{ $pensionesAnular->count() }} deuda(s) pendiente(s) se anularán
                </span>
                <span class="ms-auto small" style="color:#17226a;">
                    Se anulan todas las deudas pendientes/parciales del alumno.
                    Los pagos ya realizados quedan intactos y solo quedarán los cobros de retiro.
                </span>
            </div>
            <table class="jp-deudas-preview-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pensionesAnular as $d)
                        <tr>
                            <td>
                                <span class="fw-600">{{ $d->concepto?->nombre ?? $d->periodo ?? '—' }}</span>
                            </td>
                            <td class="text-muted">
                                {{ $d->fecha_vencimiento ? \Carbon\Carbon::parse($d->fecha_vencimiento)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-end fw-700" style="color:#151f5f;">
                                S/ {{ number_format($d->saldo, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="jp-deudas-preview-total">
                <span class="text-muted fw-600">Total a anular:</span>
                <span>S/ {{ number_format($pensionesAnular->sum('saldo'), 2) }}</span>
            </div>
        </div>
    </div>
    @endif

    @if(isset($cobrosRetiro) && count($cobrosRetiro) > 0)
    <div class="col-lg-8">
        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-0" style="background:#eff6ff;">
            <div class="d-flex align-items-start gap-3">
                <i data-feather="credit-card" style="width:20px;height:20px;color:#1f2e8d;"></i>
                <div class="w-100">
                    <h6 class="fw-700 mb-2" style="color:#162166;">Cobros que se generarán al retirar</h6>
                    <div class="small text-muted mb-2">
                        Estos conceptos quedarán como deudas pendientes del retiro.
                    </div>
                    <ul class="mb-2 ps-3">
                        @foreach($cobrosRetiro as $cobro)
                            <li>
                                <span class="fw-600">{{ $cobro['nombre'] }}</span>:
                                <span class="fw-700">S/ {{ number_format((float) ($cobro['monto'] ?? 0), 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="fw-700" style="color:#162166;">
                        Total cobros retiro: S/ {{ number_format(collect($cobrosRetiro)->sum('monto'), 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Formulario --}}
    <div class="col-lg-8">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="jp-card-icon" style="background:linear-gradient(135deg,#18246e,#1c297e);">
                    <i data-feather="user-minus"></i>
                </div>
                <div>
                    <h5 class="jp-card-title mb-0">Datos del Retiro</h5>
                    <p class="jp-card-sub mb-0">Completa la información para registrar el retiro</p>
                </div>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger mb-3 rounded-3">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form id="form-retiro" method="POST" action="{{ route($rpR . 'retiros.store') }}">
                    @csrf
                    <input type="hidden" name="matricula_id" value="{{ $matricula->id }}">

                    <div class="row g-3">
                        <div class="col-sm-5">
                            <label class="form-label fw-700">Fecha de Retiro <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_retiro"
                                id="fecha_retiro"
                                value="{{ old('fecha_retiro', now()->format('Y-m-d')) }}"
                                class="form-control @error('fecha_retiro') is-invalid @enderror"
                                max="{{ now()->format('Y-m-d') }}" required>
                            @error('fecha_retiro')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-700">Motivo del Retiro <span class="text-danger">*</span></label>
                            <textarea name="motivo_retiro" rows="3"
                                id="motivo_retiro"
                                class="form-control @error('motivo_retiro') is-invalid @enderror"
                                placeholder="Describe el motivo del retiro..." required
                                maxlength="500">{{ old('motivo_retiro') }}</textarea>
                            @error('motivo_retiro')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Máximo 500 caracteres.</small>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="button" class="jp-btn-search" data-bs-toggle="modal" data-bs-target="#modalConfirmarRetiro">
                            <i data-feather="user-minus"></i> Registrar Retiro
                        </button>
                        <a href="{{ route($rpR . 'retiros.index') }}" class="jp-docente-link">
                            <i data-feather="x"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación --}}
    <div class="modal fade jp-confirm-modal" id="modalConfirmarRetiro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="alert-triangle" style="width:20px;height:20px;"></i>
                        Confirmar Retiro
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-600 mb-3" style="color:#374151;">
                        ¿Estás seguro de registrar el retiro de
                        <strong>{{ $matricula->estudiante?->nombres }} {{ $matricula->estudiante?->apellidos }}</strong>?
                    </p>
                    <div class="jp-confirm-list">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center gap-2 mb-1">
                                <i data-feather="check" style="width:14px;height:14px;color:#1c297e;"></i>
                                La matrícula quedará marcada como <strong>RETIRADA</strong>.
                            </li>
                            <li class="d-flex align-items-center gap-2 mb-1">
                                <i data-feather="check" style="width:14px;height:14px;color:#1c297e;"></i>
                                Se anularán todas las deudas <strong>pendientes/parciales</strong> previas.
                            </li>
                            <li class="d-flex align-items-center gap-2 mb-1">
                                <i data-feather="check" style="width:14px;height:14px;color:#1c297e;"></i>
                                Los <strong>pagos ya realizados quedan intactos</strong>.
                            </li>
                            @if(isset($cobrosRetiro) && count($cobrosRetiro) > 0)
                            <li class="d-flex align-items-center gap-2 mb-1">
                                <i data-feather="check" style="width:14px;height:14px;color:#1c297e;"></i>
                                Se generarán cobros por retiro:
                                <strong>
                                    @foreach($cobrosRetiro as $i => $cobro)
                                        {{ $cobro['nombre'] }} (S/ {{ number_format((float) ($cobro['monto'] ?? 0), 2) }}){{ $i + 1 < count($cobrosRetiro) ? ',' : '.' }}
                                    @endforeach
                                </strong>
                            </li>
                            @endif
                            <li class="d-flex align-items-center gap-2">
                                <i data-feather="alert-circle" style="width:14px;height:14px;color:#213196;"></i>
                                Esta acción <strong>no se puede deshacer</strong>.
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-3 fw-600" data-bs-dismiss="modal">
                        <i data-feather="x" style="width:15px;height:15px;"></i> Cancelar
                    </button>
                    <button type="button" class="jp-btn-confirmar-retiro" onclick="document.getElementById('form-retiro').submit()">
                        <i data-feather="user-minus"></i> Confirmar Retiro
                    </button>
                </div>
            </div>
        </div>
    </div>

    @endif

</div>
@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush
