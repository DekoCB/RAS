@extends('layouts.mantis')

@section('title', 'Editar Estudiante | Secretaria')
@section('page-title', 'Editar Estudiante')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
@if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
<style>
    /* ── Hero del alumno ──────────────────────────────── */
    .jp-edit-hero {
        background: linear-gradient(135deg, #162061 0%, #18246e 55%, #1c297e 100%);
        border-radius: 20px;
        padding: 1.75rem 2rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: .25rem;
    }
    .jp-edit-hero::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
        pointer-events: none;
    }
    .jp-edit-hero::after {
        content: '';
        position: absolute;
        bottom: -70px; left: -30px;
        width: 250px; height: 250px;
        background: rgba(255,255,255,.04);
        border-radius: 50%;
        pointer-events: none;
    }
    .jp-edit-avatar {
        width: 60px; height: 60px;
        border-radius: 18px;
        background: rgba(255,255,255,.18);
        border: 2px solid rgba(255,255,255,.28);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: 800; color: #fff;
        flex-shrink: 0;
        letter-spacing: -.02em;
    }
    .jp-edit-hero__nombre { font-size: 1.3rem; font-weight: 800; line-height: 1.2; }
    .jp-edit-hero__sub    { font-size: .83rem; opacity: .78; margin-top: .2rem; }
    .jp-edit-hero__crumb  {
        display: flex; align-items: center; gap: .4rem;
        font-size: .75rem; opacity: .65; margin-bottom: .5rem;
    }
    .jp-edit-hero__crumb a { color: rgba(255,255,255,.7); text-decoration: none; }
    .jp-edit-hero__crumb a:hover { color: #fff; }
    .jp-edit-hero__crumb i { width: 13px; height: 13px; }

    /* ── Sección card ─────────────────────────────────── */
    .jp-edit-section {
        border: 1px solid #d7e0ea;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(18, 27, 82,.08);
        background: #fff;
    }
    .jp-edit-section__header {
        background: linear-gradient(120deg, #162061 0%, #18246e 55%, #1c297e 100%);
        padding: 1rem 1.4rem;
        display: flex;
        align-items: center;
        gap: .85rem;
        color: #fff;
    }
    .jp-edit-section__icon {
        width: 38px; height: 38px;
        border-radius: 12px;
        background: rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .jp-edit-section__icon i { width: 18px; height: 18px; }
    .jp-edit-section__title { font-weight: 700; font-size: .95rem; line-height: 1.2; }
    .jp-edit-section__sub   { font-size: .75rem; opacity: .75; margin-top: .05rem; }
    .jp-edit-section__body  { padding: 1.5rem; }

    /* ── Inputs mejorados ─────────────────────────────── */
    .jp-edit-field label {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        margin-bottom: .35rem;
        display: flex;
        align-items: center;
        gap: .35rem;
    }
    .jp-edit-field label i { width: 13px; height: 13px; color: #94a3b8; }
    .jp-edit-field .form-control,
    .jp-edit-field .form-select {
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        min-height: 44px;
        font-size: .88rem;
        color: #1e293b;
        background: #fafafa;
        transition: border-color .18s ease, box-shadow .18s ease;
        padding: .55rem .85rem;
    }
    .jp-edit-field .form-control:focus,
    .jp-edit-field .form-select:focus {
        border-color: #1c297e;
        box-shadow: 0 0 0 3px rgba(28, 41, 126,.1);
        background: #fff;
    }
    .jp-edit-field .form-control[readonly] {
        background: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
    }
    .jp-edit-field .form-control.is-invalid,
    .jp-edit-field .form-select.is-invalid {
        border-color: #ef4444;
    }

    /* ── Alerta apoderado ─────────────────────────────── */
    .jp-edit-alert {
        background: #ebfaff;
        border: 1.5px solid #b8c1fd;
        border-radius: 12px;
        padding: .75rem 1rem;
        display: flex;
        align-items: center;
        gap: .6rem;
        font-size: .85rem;
        color: #17226a;
    }
    .jp-edit-alert i { width: 16px; height: 16px; flex-shrink: 0; }

    /* ── Divisor interno ──────────────────────────────── */
    .jp-edit-divider {
        height: 1px;
        background: linear-gradient(to right, transparent, #d9e3f1, transparent);
        margin: .25rem 0 1.25rem;
    }

    /* ── Botones ──────────────────────────────────────── */
    .jp-btn-guardar {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .6rem 1.5rem;
        background: linear-gradient(135deg, #1c297e, #18246e);
        color: #fff;
        border: 0;
        border-radius: 12px;
        font-weight: 700;
        font-size: .9rem;
        box-shadow: 0 8px 16px rgba(28, 41, 126,.25);
        transition: all .18s ease;
        cursor: pointer;
    }
    .jp-btn-guardar:hover {
        background: linear-gradient(135deg, #18246e, #162061);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(28, 41, 126,.3);
    }
    .jp-btn-guardar i { width: 17px; height: 17px; }
    .jp-btn-cancelar {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .6rem 1.25rem;
        background: #f1f5f9;
        color: #475569;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 700;
        font-size: .9rem;
        text-decoration: none;
        transition: all .18s ease;
    }
    .jp-btn-cancelar:hover {
        background: #e2e8f0;
        color: #334155;
    }
    .jp-btn-cancelar i { width: 16px; height: 16px; }

    /* ── Estado badge ─────────────────────────────────── */
    .jp-estado-select option { font-weight: 600; }
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
@php
    $iniciales = strtoupper(
        substr($estudiante->nombres ?? 'A', 0, 1) .
        substr($estudiante->apellidos ?? '', 0, 1)
    );
@endphp

<form method="POST" action="{{ route('secretaria.estudiantes.update', $estudiante) }}">
@csrf
@method('PUT')

<div class="row g-4 justify-content-center">

    {{-- ── HERO ── --}}
    <div class="col-xl-9">
        <div class="jp-edit-hero">
            <div class="jp-edit-hero__crumb position-relative" style="z-index:1;">
                <a href="{{ route('secretaria.estudiantes.index') }}">Alumnos / Padres</a>
                <i data-feather="chevron-right"></i>
                <span>Editar estudiante</span>
            </div>
            <div class="d-flex align-items-center gap-3 position-relative" style="z-index:1;">
                <div class="jp-edit-avatar">{{ $iniciales }}</div>
                <div>
                    <div class="jp-edit-hero__nombre">
                        {{ $estudiante->apellidos }}, {{ $estudiante->nombres }}
                    </div>
                    <div class="jp-edit-hero__sub">
                        DNI: {{ $estudiante->dni ?? '—' }}
                        &nbsp;·&nbsp; Código: {{ $estudiante->codigo ?? 'Sin código' }}
                    </div>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('secretaria.estudiantes.index') }}" class="jp-btn-cancelar">
                        <i data-feather="arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="col-xl-9">
        <div class="alert border-0 rounded-3 d-flex align-items-start gap-2"
             style="background:#f5f9ff;color:#162061;border-left:4px solid #1c297e!important;">
            <i data-feather="alert-circle" style="width:18px;height:18px;flex-shrink:0;margin-top:.1rem;"></i>
            <ul class="mb-0 ps-2">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ── SECCIÓN 1: DATOS DEL ALUMNO ── --}}
    <div class="col-xl-9">
        <div class="jp-edit-section">
            <div class="jp-edit-section__header">
                <div class="jp-edit-section__icon">
                    <i data-feather="user"></i>
                </div>
                <div>
                    <div class="jp-edit-section__title">Datos del Alumno</div>
                    <div class="jp-edit-section__sub">Información personal del estudiante</div>
                </div>
            </div>
            <div class="jp-edit-section__body">
                <div class="row g-3">

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="hash"></i> Código
                        </label>
                        <input type="text" name="codigo" class="form-control"
                               value="{{ old('codigo', $estudiante->codigo) }}"
                               placeholder="ALU-00000">
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="credit-card"></i> DNI <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" name="dni" class="form-control @error('dni') is-invalid @enderror"
                               value="{{ old('dni', $estudiante->dni) }}"
                               placeholder="Número de DNI" maxlength="8">
                        @error('dni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="calendar"></i> Fecha de Nacimiento
                        </label>
                        <input type="date" name="fecha_nacimiento" class="form-control"
                               value="{{ old('fecha_nacimiento', $estudiante->fecha_nacimiento) }}">
                    </div>

                    <div class="col-md-6 jp-edit-field">
                        <label>
                            <i data-feather="type"></i> Nombres <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" name="nombres"
                               class="form-control @error('nombres') is-invalid @enderror"
                               value="{{ old('nombres', $estudiante->nombres) }}"
                               placeholder="Nombres del alumno" required>
                        @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 jp-edit-field">
                        <label>
                            <i data-feather="type"></i> Apellidos <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" name="apellidos"
                               class="form-control @error('apellidos') is-invalid @enderror"
                               value="{{ old('apellidos', $estudiante->apellidos) }}"
                               placeholder="Apellidos del alumno" required>
                        @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="users"></i> Sexo
                        </label>
                        <select name="sexo" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="Masculino" @selected(old('sexo', $estudiante->sexo) === 'Masculino')>Masculino</option>
                            <option value="Femenino"  @selected(old('sexo', $estudiante->sexo) === 'Femenino')>Femenino</option>
                        </select>
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="map-pin"></i> Ciudad
                        </label>
                        <input type="text" name="ciudad" class="form-control"
                               value="{{ old('ciudad', $estudiante->ciudad) }}"
                               placeholder="Ciudad de residencia">
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="toggle-right"></i> Estado
                        </label>
                        <select name="estado" class="form-select jp-estado-select">
                            <option value="activo"   @selected(old('estado', $estudiante->estado ?? 'activo') === 'activo')>Activo</option>
                            <option value="inactivo" @selected(old('estado', $estudiante->estado) === 'inactivo')>Inactivo</option>
                        </select>
                    </div>

                    <div class="col-12 jp-edit-field">
                        <label>
                            <i data-feather="home"></i> Dirección
                        </label>
                        <input type="text" name="direccion" class="form-control"
                               value="{{ old('direccion', $estudiante->direccion) }}"
                               placeholder="Dirección del alumno">
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── SECCIÓN 2: DATOS DEL APODERADO ── --}}
    <div class="col-xl-9">
        <div class="jp-edit-section">
            <div class="jp-edit-section__header">
                <div class="jp-edit-section__icon">
                    <i data-feather="heart"></i>
                </div>
                <div>
                    <div class="jp-edit-section__title">Padre / Apoderado Principal</div>
                    <div class="jp-edit-section__sub">Información del responsable del alumno</div>
                </div>
            </div>
            <div class="jp-edit-section__body">

                @if(!$apoderadoPrincipal)
                <div class="jp-edit-alert mb-4">
                    <i data-feather="alert-triangle"></i>
                    Este alumno no tiene apoderado principal asociado. Completa los datos para registrarlo.
                </div>
                @endif

                <div class="row g-3">

                    <div class="col-md-6 jp-edit-field">
                        <label>
                            <i data-feather="type"></i> Nombres del apoderado
                        </label>
                        <input type="text" name="apoderado_nombres"
                               class="form-control @error('apoderado_nombres') is-invalid @enderror"
                               value="{{ old('apoderado_nombres', $apoderadoPrincipal?->nombres) }}"
                               placeholder="Nombres">
                        @error('apoderado_nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 jp-edit-field">
                        <label>
                            <i data-feather="type"></i> Apellidos del apoderado
                        </label>
                        <input type="text" name="apoderado_apellidos"
                               class="form-control @error('apoderado_apellidos') is-invalid @enderror"
                               value="{{ old('apoderado_apellidos', $apoderadoPrincipal?->apellidos) }}"
                               placeholder="Apellidos">
                        @error('apoderado_apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="credit-card"></i> DNI del apoderado
                        </label>
                        <input type="text" name="apoderado_dni" class="form-control"
                               value="{{ old('apoderado_dni', $apoderadoPrincipal?->dni) }}"
                               placeholder="DNI" maxlength="8">
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="phone"></i> Teléfono
                        </label>
                        <input type="text" name="apoderado_telefono" class="form-control"
                               value="{{ old('apoderado_telefono', $apoderadoPrincipal?->telefono) }}"
                               placeholder="N° de teléfono">
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="mail"></i> Email
                        </label>
                        <input type="email" name="apoderado_email" class="form-control"
                               value="{{ old('apoderado_email', $apoderadoPrincipal?->email) }}"
                               placeholder="correo@ejemplo.com">
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="calendar"></i> Fecha de Nacimiento
                        </label>
                        <input type="date" name="apoderado_fecha_nacimiento" class="form-control"
                               value="{{ old('apoderado_fecha_nacimiento', $apoderadoPrincipal?->fecha_nacimiento) }}">
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="users"></i> Sexo
                        </label>
                        <select name="apoderado_sexo" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="Masculino" @selected(old('apoderado_sexo', $apoderadoPrincipal?->sexo) === 'Masculino')>Masculino</option>
                            <option value="Femenino"  @selected(old('apoderado_sexo', $apoderadoPrincipal?->sexo) === 'Femenino')>Femenino</option>
                        </select>
                    </div>

                    <div class="col-md-4 jp-edit-field">
                        <label>
                            <i data-feather="briefcase"></i> Estado Civil
                        </label>
                        <input type="text" name="apoderado_estado_civil" class="form-control"
                               value="{{ old('apoderado_estado_civil', $apoderadoPrincipal?->estado_civil) }}"
                               placeholder="Ej. Casado, Soltero">
                    </div>

                    <div class="col-md-6 jp-edit-field">
                        <label>
                            <i data-feather="map-pin"></i> Ciudad
                        </label>
                        <input type="text" name="apoderado_ciudad" class="form-control"
                               value="{{ old('apoderado_ciudad', $apoderadoPrincipal?->ciudad) }}"
                               placeholder="Ciudad">
                    </div>

                    <div class="col-md-6 jp-edit-field">
                        <label>
                            <i data-feather="link"></i> Relación con el alumno <span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" name="apoderado_relacion"
                               class="form-control @error('apoderado_relacion') is-invalid @enderror"
                               value="{{ old('apoderado_relacion', $apoderadoPrincipal?->pivot?->relacion) }}"
                               placeholder="Ej. Padre, Madre, Tutor">
                        @error('apoderado_relacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 jp-edit-field">
                        <label>
                            <i data-feather="home"></i> Dirección del apoderado
                        </label>
                        <input type="text" name="apoderado_direccion" class="form-control"
                               value="{{ old('apoderado_direccion', $apoderadoPrincipal?->direccion) }}"
                               placeholder="Dirección completa">
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── BOTONES ── --}}
    <div class="col-xl-9">
        <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted">
                <span class="text-danger">*</span> Campos requeridos
            </small>
            <div class="d-flex gap-2">
                <a href="{{ route('secretaria.estudiantes.index') }}" class="jp-btn-cancelar">
                    <i data-feather="x"></i> Cancelar
                </a>
                <button type="submit" class="jp-btn-guardar">
                    <i data-feather="save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush
