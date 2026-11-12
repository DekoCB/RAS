@extends('layouts.mantis')

@section('title', 'Reporte por Alumno | Secretaria')
@section('page-title', 'Reportes')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('css/secretaria-reportes.css') }}">
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    @php
        $exportQuery = request()->except('page');
    @endphp

    <div class="rep-shell">
        @include('secretaria.reportes.partials.tabs', ['active' => 'alumno'])

        <div class="rep-header">
            <div>
                <h5 class="rep-header__title">Historial integral por alumno</h5>
                <p class="rep-header__sub">Consolida pagos, deudas, riesgo y seguimiento en una sola vista.</p>
            </div>
            <div class="rep-header__meta">
                Buscar por DNI para cargar el expediente
            </div>
        </div>

        <form method="GET" action="{{ route('secretaria.reportes.alumno') }}" class="rep-filter-card">
            <div class="rep-filter-grid">
                <div>
                    <label class="form-label small text-muted">DNI</label>
                    <input type="text" name="dni" class="form-control" value="{{ $filters['dni'] ?? '' }}" required>
                </div>
                <div>
                    <label class="form-label small text-muted">Fecha desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="{{ $filters['fecha_desde'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Fecha hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ $filters['fecha_hasta'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Estado pagos</label>
                    <select name="estado_pago" class="form-select">
                        <option value="todos" @selected(($filters['estado_pago'] ?? 'todos') === 'todos')>Todos</option>
                        <option value="confirmado" @selected(($filters['estado_pago'] ?? '') === 'confirmado')>Confirmado</option>
                        <option value="pendiente" @selected(($filters['estado_pago'] ?? '') === 'pendiente')>Pendiente</option>
                        <option value="anulado" @selected(($filters['estado_pago'] ?? '') === 'anulado')>Anulado</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Estado deudas</label>
                    <select name="estado_deuda" class="form-select">
                        <option value="todos" @selected(($filters['estado_deuda'] ?? 'todos') === 'todos')>Todos</option>
                        <option value="pendiente" @selected(($filters['estado_deuda'] ?? '') === 'pendiente')>Pendiente</option>
                        <option value="parcial" @selected(($filters['estado_deuda'] ?? '') === 'parcial')>Parcial</option>
                        <option value="pagado" @selected(($filters['estado_deuda'] ?? '') === 'pagado')>Pagado</option>
                    </select>
                </div>
            </div>

            <div class="rep-filter-actions">
                <div class="rep-filter-inline">
                    <button class="btn jp-btn-search" type="submit">
                        <i data-feather="search"></i> Consultar alumno
                    </button>
                    <a href="{{ route('secretaria.reportes.alumno') }}" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>
            </div>
        </form>

        @if (session('status'))
            <div class="alert alert-info mb-0">{{ session('status') }}</div>
        @endif

        @if (($dni ?? '') !== '' && !$estudiante)
            <div class="rep-filter-card rep-empty">
                No se encontro un alumno con el DNI {{ $dni }}.
            </div>
        @endif

        @if ($estudiante)
            @php
                $riesgoClass = match ($resumen['riesgo_key'] ?? 'sin_fecha') {
                    'al_dia' => 'is-ok',
                    '1_7', '8_30' => 'is-warn',
                    '31_60', '60_plus' => 'is-danger',
                    default => 'is-neutral',
                };
            @endphp

            <div class="rep-split">
                <div class="rep-filter-card">
                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <div>
                            <div class="rep-kpi__label">Alumno</div>
                            <div class="fw-semibold">{{ $estudiante->apellidos }}, {{ $estudiante->nombres }}</div>
                            <div class="text-muted small">DNI: {{ $estudiante->dni ?? '-' }}</div>
                            <div class="text-muted small">
                                {{ $matricula?->grado?->nombre ?? '-' }} {{ $matricula?->seccion?->nombre ?? '' }}
                            </div>
                        </div>
                        <div class="rep-actions">
                            <a href="{{ route('secretaria.reportes.alumno.pagos.excel', $exportQuery) }}" class="btn jp-btn-incidencia">
                                <i data-feather="download"></i> Exportar pagos
                            </a>
                            <a href="{{ route('secretaria.reportes.alumno.deudas.excel', $exportQuery) }}" class="btn jp-btn-incidencia">
                                <i data-feather="download"></i> Exportar deudas
                            </a>
                        </div>
                    </div>
                </div>

                <div class="rep-filter-card">
                    <div class="rep-kpi__label mb-2">Seguimiento</div>
                    <div class="small text-muted mb-1">Ultima gestion: <strong>{{ $resumen['ultima_gestion'] ?? '-' }}</strong></div>
                    <div class="small text-muted mb-1">Responsable: <strong>{{ $resumen['responsable'] ?? '-' }}</strong></div>
                    <div class="small text-muted mb-1">Proxima fecha: <strong>{{ $resumen['proxima_fecha'] ?? '-' }}</strong></div>
                    <div class="small text-muted mb-2">Accion sugerida:</div>
                    <div class="small">{{ $resumen['proxima_accion'] ?? '-' }}</div>
                    <div class="mt-2">
                        <span class="rep-badge {{ $riesgoClass }}">{{ $resumen['riesgo_label'] ?? 'Sin riesgo' }}</span>
                    </div>
                </div>
            </div>

            <div class="rep-kpi-grid">
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Total facturado</div>
                    <div class="rep-kpi__value">S/ {{ number_format((float) ($resumen['total_monto'] ?? 0), 2) }}</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Total pagado</div>
                    <div class="rep-kpi__value">S/ {{ number_format((float) ($resumen['total_pagado'] ?? 0), 2) }}</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Saldo pendiente</div>
                    <div class="rep-kpi__value">S/ {{ number_format((float) ($resumen['total_saldo'] ?? 0), 2) }}</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Deudas pendientes</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($resumen['deudas_pendientes'] ?? 0)) }}</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Deudas vencidas</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($resumen['deudas_vencidas'] ?? 0)) }}</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Pagos registrados</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($resumen['pagos_registrados'] ?? 0)) }}</div>
                </div>
            </div>

            <div class="rep-table-card">
                <div class="rep-table-wrap">
                    <table class="table rep-table jp-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Metodo</th>
                                <th>Estado</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pagos as $pago)
                                @php
                                    $deudasAlumno = $pago->deudas->filter(function ($deuda) use ($estudiante) {
                                        return $deuda->matricula && $deuda->matricula->estudiante_id === $estudiante->id;
                                    });
                                    $deuda = $deudasAlumno->first();
                                    $conceptoLabel = $deudasAlumno->count() > 1 ? 'Varios' : ($deuda?->concepto?->nombre ?? '-');
                                    $estado = strtolower((string) ($pago->estado ?? 'pendiente'));
                                    $estadoClass = match ($estado) {
                                        'confirmado' => 'is-ok',
                                        'pendiente' => 'is-warn',
                                        'anulado' => 'is-danger',
                                        default => 'is-neutral',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $conceptoLabel }}</td>
                                    <td>S/ {{ number_format((float) $pago->monto, 2) }}</td>
                                    <td>{{ $pago->metodo ?? '-' }}</td>
                                    <td><span class="rep-badge {{ $estadoClass }}">{{ ucfirst($estado) }}</span></td>
                                    <td>{{ $pago->registradoPor->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="rep-empty">No hay pagos registrados para este filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rep-table-card">
                <div class="rep-table-wrap">
                    <table class="table rep-table jp-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Periodo</th>
                                <th>Monto</th>
                                <th>Saldo</th>
                                <th>Vencimiento</th>
                                <th>Atraso</th>
                                <th>Riesgo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deudas as $deuda)
                                @php
                                    $riesgo = $deuda->riesgo_key ?? 'sin_fecha';
                                    $riesgoClass = match ($riesgo) {
                                        'al_dia' => 'is-ok',
                                        '1_7', '8_30' => 'is-warn',
                                        '31_60', '60_plus' => 'is-danger',
                                        default => 'is-neutral',
                                    };
                                    $estado = strtolower((string) ($deuda->estado ?? 'pendiente'));
                                    $estadoClass = match ($estado) {
                                        'pagado' => 'is-ok',
                                        'parcial' => 'is-warn',
                                        default => 'is-danger',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $deuda->concepto?->nombre ?? '-' }}</td>
                                    <td>{{ $deuda->periodo ?? '-' }}</td>
                                    <td>S/ {{ number_format((float) $deuda->monto, 2) }}</td>
                                    <td>S/ {{ number_format((float) $deuda->saldo, 2) }}</td>
                                    <td>{{ $deuda->fecha_vencimiento ? \Illuminate\Support\Carbon::parse($deuda->fecha_vencimiento)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $deuda->dias_atraso === null ? '-' : $deuda->dias_atraso . ' dias' }}</td>
                                    <td><span class="rep-badge {{ $riesgoClass }}">{{ $deuda->riesgo_label ?? 'Sin fecha' }}</span></td>
                                    <td><span class="rep-badge {{ $estadoClass }}">{{ ucfirst($deuda->estado ?? '-') }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="rep-empty">No hay deudas registradas para este filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
