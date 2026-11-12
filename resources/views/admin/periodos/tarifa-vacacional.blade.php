@extends('layouts.mantis')

@section('title', 'Tarifa Vacacional')
@section('page-title', 'Tarifa Vacacional')
@section('body-class', 'role-superadmin')

@push('styles')
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row g-4 justify-content-center">

    <div class="col-lg-6">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="sa-card-icon">
                    <i data-feather="sun"></i>
                </div>
                <div>
                    <h5 class="sa-card-title mb-0">Tarifa Vacacional</h5>
                    <p class="sa-card-sub mb-0">Año escolar {{ $anioActivo->nombre }}</p>
                </div>
            </div>
            <div class="card-body">

                <p class="text-muted small mb-4">
                    Configura el monto que se cobra al matricular a un alumno en el <strong>Periodo Vacacional</strong>.
                    Se genera una sola deuda (no pensiones mensuales).
                </p>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin-sistema.periodos.tarifa-vacacional.guardar') }}">
                    @csrf

                    <div class="d-flex flex-column gap-3">
                        @foreach($niveles as $nivel)
                            <div class="p-3" style="border:1px solid #d4ecfd;border-radius:14px;background:#f5faff;">
                                <label class="form-label fw-800 mb-2" style="color:#162061;">{{ $nivel }}</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-700">S/</span>
                                    <input type="number" name="montos[{{ $nivel }}]" step="0.01" min="0"
                                        value="{{ old("montos.$nivel", isset($tarifas[$nivel]) ? $tarifas[$nivel]->monto : '') }}"
                                        class="form-control @error("montos.$nivel") is-invalid @enderror"
                                        placeholder="0.00" required>
                                    @error("montos.$nivel")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="jp-btn-primary">
                            <i data-feather="save"></i> Guardar Tarifas
                        </button>
                        <a href="{{ route('admin-sistema.periodos.index') }}" class="jp-docente-link">
                            <i data-feather="arrow-left"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>feather.replace();</script>
@endpush
