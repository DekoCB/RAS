@extends('layouts.mantis')

@section('title', 'Editar Periodo')
@section('page-title', 'Editar Periodo')
@section('body-class', 'role-superadmin')

@push('styles')
<link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="jp-soft-card card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="sa-card-icon">
                    <i data-feather="edit-2"></i>
                </div>
                <div>
                    <h5 class="sa-card-title mb-0">Editar: {{ $periodo->nombre }}</h5>
                    <p class="sa-card-sub mb-0">{{ $periodo->codigo }} - Orden {{ $periodo->orden }}</p>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin-sistema.periodos.update', $periodo) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-700 small text-muted">Tipo</label>
                        <div>
                            @if($periodo->es_vacacional)
                                <span class="jp-pill jp-pill--warn">Vacacional</span>
                                <small class="text-muted ms-2">No genera pensiones mensuales.</small>
                            @else
                                <span class="jp-pill jp-pill--ok">Academico</span>
                                <small class="text-muted ms-2">Genera pensiones por mes.</small>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-700">Fecha de Inicio <span class="text-muted fw-400">(opcional)</span></label>
                            <input
                                type="date"
                                name="fecha_inicio"
                                value="{{ old('fecha_inicio', $periodo->fecha_inicio?->format('Y-m-d')) }}"
                                class="form-control @error('fecha_inicio') is-invalid @enderror"
                            >
                            @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Si lo dejas vacio, se usa el rango por defecto del periodo.</small>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-700">Fecha de Fin <span class="text-muted fw-400">(opcional)</span></label>
                            <input
                                type="date"
                                name="fecha_fin"
                                value="{{ old('fecha_fin', $periodo->fecha_fin?->format('Y-m-d')) }}"
                                class="form-control @error('fecha_fin') is-invalid @enderror"
                            >
                            @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Si lo dejas vacio, se usa el rango por defecto del periodo.</small>
                        </div>

                        @if(!$periodo->es_vacacional)
                            @php
                                $meses = [
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                                ];
                                $mesesExcluidosSeleccionados = collect(old('meses_excluidos', $periodo->mesesExcluidosPensiones()))
                                    ->map(fn ($mes) => (int) $mes)
                                    ->filter(fn ($mes) => $mes >= 1 && $mes <= 12)
                                    ->values()
                                    ->all();
                            @endphp

                            <div class="col-12">
                                <label class="form-label fw-700">
                                    Meses excluidos de cobro <span class="text-muted fw-400">(opcional)</span>
                                </label>
                                <div class="p-3" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach($meses as $numeroMes => $nombreMes)
                                            <label class="form-check d-inline-flex align-items-center gap-2 mb-0">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input js-mes-excluido"
                                                    name="meses_excluidos[]"
                                                    value="{{ $numeroMes }}"
                                                    @checked(in_array($numeroMes, $mesesExcluidosSeleccionados, true))
                                                >
                                                <span>{{ $nombreMes }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Los meses marcados no generan deuda de pension.
                                    </small>
                                </div>
                                @error('meses_excluidos')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @error('meses_excluidos.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div id="preview-meses" class="p-3" style="background:#f5faff;border:1px solid #d4ecfd;border-radius:12px;">
                                    <span class="fw-700 small" style="color:#213196;">Pensiones que se generaran:</span>
                                    <span id="meses-label" class="ms-2 small text-muted">Configura las fechas para ver el rango</span>
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="activo" id="activoSwitch" value="1" {{ old('activo', $periodo->activo) ? 'checked' : '' }}>
                                <label class="form-check-label fw-700" for="activoSwitch">Periodo Activo</label>
                            </div>
                            <small class="text-muted">Se usa por defecto cuando no hay fechas especificas.</small>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="jp-btn-primary">
                            <i data-feather="save"></i> Guardar Cambios
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
<script>
feather.replace();

const meses = {
    1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril',
    5: 'Mayo', 6: 'Junio', 7: 'Julio', 8: 'Agosto',
    9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
};

function rangoMesesSegunFormulario() {
    const ini = document.querySelector('[name=fecha_inicio]')?.value;
    const fin = document.querySelector('[name=fecha_fin]')?.value;
    const mI = ini ? (new Date(ini).getMonth() + 1) : 3;
    const mF = fin ? (new Date(fin).getMonth() + 1) : 12;
    return [mI, mF];
}

function normalizarMesesExcluidos(mI, mF) {
    const checks = Array.from(document.querySelectorAll('.js-mes-excluido'));
    checks.forEach((check) => {
        const mes = parseInt(check.value, 10);
        const habilitado = mes >= mI && mes <= mF;
        check.disabled = !habilitado;
        if (!habilitado) {
            check.checked = false;
        }
    });
}

function actualizarPreview() {
    const label = document.getElementById('meses-label');
    if (!label) return;

    const [mI, mF] = rangoMesesSegunFormulario();
    if (mF < mI) {
        label.textContent = 'La fecha de fin debe ser mayor o igual a la de inicio.';
        label.style.color = '#1c297e';
        return;
    }

    normalizarMesesExcluidos(mI, mF);

    const mesesRango = [];
    for (let m = mI; m <= mF; m++) {
        mesesRango.push(m);
    }

    const excluidos = Array.from(document.querySelectorAll('.js-mes-excluido:checked'))
        .map((check) => parseInt(check.value, 10))
        .filter((mes) => !Number.isNaN(mes));

    const activos = mesesRango.filter((mes) => !excluidos.includes(mes));
    if (!activos.length) {
        label.textContent = 'Debes dejar al menos 1 mes activo para generar pensiones.';
        label.style.color = '#1c297e';
        return;
    }

    const primerMes = activos[0];
    const ultimoMes = activos[activos.length - 1];
    const excluidosTexto = excluidos.length
        ? ` | Excluidos: ${excluidos.map((mes) => meses[mes]).join(', ')}`
        : '';

    label.textContent = `${meses[primerMes]} -> ${meses[ultimoMes]} (${activos.length} mes${activos.length !== 1 ? 'es' : ''})${excluidosTexto}`;
    label.style.color = '#213196';
}

document.querySelector('[name=fecha_inicio]')?.addEventListener('change', actualizarPreview);
document.querySelector('[name=fecha_fin]')?.addEventListener('change', actualizarPreview);
document.querySelectorAll('.js-mes-excluido').forEach((check) => {
    check.addEventListener('change', actualizarPreview);
});

actualizarPreview();
</script>
@endpush
