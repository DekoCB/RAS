@extends('layouts.mantis')

@section('title', 'Pre-matrículas')
@section('page-title', 'Pre-matrículas')
@section('body-class', auth()->user()?->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
<link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@if(auth()->user()?->hasRole('SuperAdmin'))
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endif
<style>
    .jp-table-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 42px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #151f5f;
        transition: all .2s ease;
    }

    .jp-table-action i { width: 20px; height: 20px; }

    .jp-table-action:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #161f59;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('sidebar')
    @if (auth()->user()?->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
@php
    $esAdmin = auth()->user()?->hasRole('SuperAdmin');
    $rpM = $esAdmin ? 'admin-sistema.' : 'secretaria.';
    $esDirectorVista = false;
    $preIndexRoute = route($rpM . 'matriculas.pre-matriculas');
@endphp

<div class="jp-attendance-wrap">

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-1 mb-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold" style="color:#162061;font-size:1.05rem;">Pre-matrículas</h5>
            <span class="badge" style="background:#e2eefe;color:#1c297e;font-size:.78rem;font-weight:700;border-radius:999px;padding:.3rem .75rem;">
                {{ $preMatriculas->total() }} resultado{{ $preMatriculas->total() !== 1 ? 's' : '' }}
            </span>
        </div>
        <a href="{{ route($rpM . 'matriculas.index') }}" class="jp-docente-history-btn">
            <i data-feather="file-text"></i>
            Ir a Matrículas
        </a>
    </div>

    {{-- ── Filtros ────────────────────────────────────────────────── --}}
    <div class="card jp-search-card">
        <div class="card-body">
            <form method="GET" action="{{ $preIndexRoute }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="search" style="width:12px;height:12px;"></i>
                            Buscar por DNI (alumno o apoderado)
                        </label>
                        <div class="input-group jp-search-input">
                            <span class="input-group-text"><i data-feather="search"></i></span>
                            <input type="text" name="dni" class="form-control" placeholder="Nº de DNI..."
                                value="{{ request('dni') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i data-feather="flag" style="width:12px;height:12px;"></i>
                            Estado
                        </label>
                        <select name="estado" class="form-select" style="border-radius:10px;min-height:44px;"
                                onchange="this.form.submit()">
                            <option value="">— Todos los estados —</option>
                            @foreach(\App\Models\Postulante::ESTADOS as $clave => $etiqueta)
                                <option value="{{ $clave }}" @selected(request('estado') === $clave)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2 justify-content-end">
                        <button class="btn jp-btn-search" type="submit">
                            <i data-feather="search" style="width:15px;height:15px;"></i>
                            Buscar
                        </button>
                        @if(request()->hasAny(['dni','estado']))
                            <a href="{{ $preIndexRoute }}" class="btn btn-light rounded-pill px-3 d-flex align-items-center gap-1 small fw-bold">
                                <i data-feather="x" style="width:14px;height:14px;"></i>
                                Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('secretaria.matriculas._pre_matriculas')
</div>
@endsection
