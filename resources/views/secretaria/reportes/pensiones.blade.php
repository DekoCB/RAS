@extends('layouts.mantis')

@section('title', 'Pensiones | Secretaria')
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
        $resumenItems = collect($resumen->items());
        $totalMontoPagina = (float) $resumenItems->sum('monto');
        $totalSaldoPagina = (float) $resumenItems->sum('saldo');
        $totalCobradoPagina = (float) $resumenItems->sum('cobrado');
    @endphp

    <div class="rep-shell">
        @include('secretaria.reportes.partials.tabs', ['active' => 'pensiones'])

        <div class="rep-header">
            <div>
                <h5 class="rep-header__title">Control de pensiones por periodo</h5>
                <p class="rep-header__sub">Visualiza cobertura de cobranza, saldos y avance por cada periodo.</p>
            </div>
            <div class="rep-header__meta">
                Corte: {{ optional($fechaCorte)->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="rep-kpi-grid">
            <div class="rep-kpi">
                <div class="rep-kpi__label">Periodos</div>
                <div class="rep-kpi__value">{{ number_format((int) ($kpis['periodos'] ?? 0)) }}</div>
                <div class="rep-kpi__hint">Con registros</div>
            </div>
            <div class="rep-kpi">
                <div class="rep-kpi__label">Estudiantes</div>
                <div class="rep-kpi__value">{{ number_format((int) ($kpis['estudiantes'] ?? 0)) }}</div>
                <div class="rep-kpi__hint">Unicos considerados</div>
            </div>
            <div class="rep-kpi">
                <div class="rep-kpi__label">Monto total</div>
                <div class="rep-kpi__value">S/ {{ number_format((float) ($kpis['monto_total'] ?? 0), 2) }}</div>
                <div class="rep-kpi__hint">Facturado</div>
            </div>
            <div class="rep-kpi">
                <div class="rep-kpi__label">Cobrado</div>
                <div class="rep-kpi__value">S/ {{ number_format((float) ($kpis['cobrado_total'] ?? 0), 2) }}</div>
                <div class="rep-kpi__hint">Recuperado</div>
            </div>
            <div class="rep-kpi">
                <div class="rep-kpi__label">Cobertura global</div>
                <div class="rep-kpi__value">{{ number_format((float) ($kpis['cobertura_total'] ?? 0), 2) }}%</div>
                <div class="rep-kpi__hint">Cobrado vs facturado</div>
            </div>
        </div>

        <form method="GET" action="{{ route('secretaria.reportes.pensiones') }}" class="rep-filter-card">
            <div class="rep-filter-grid">
                <div>
                    <label class="form-label small text-muted">DNI</label>
                    <input type="text" name="dni" class="form-control" value="{{ $filters['dni'] ?? '' }}" placeholder="Buscar DNI">
                </div>
                <div>
                    <label class="form-label small text-muted">Periodo</label>
                    <input type="text" name="periodo" class="form-control" value="{{ $filters['periodo'] ?? '' }}" placeholder="Ej: MARZO 2026">
                </div>
                <div>
                    <label class="form-label small text-muted">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="todos" @selected(($filters['estado'] ?? 'todos') === 'todos')>Todos</option>
                        <option value="pendiente" @selected(($filters['estado'] ?? '') === 'pendiente')>Pendiente</option>
                        <option value="parcial" @selected(($filters['estado'] ?? '') === 'parcial')>Parcial</option>
                        <option value="pagado" @selected(($filters['estado'] ?? '') === 'pagado')>Pagado</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Saldo min</label>
                    <input type="number" step="0.01" min="0" name="saldo_min" class="form-control" value="{{ $filters['saldo_min'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Saldo max</label>
                    <input type="number" step="0.01" min="0" name="saldo_max" class="form-control" value="{{ $filters['saldo_max'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Vence desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="{{ $filters['fecha_desde'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Vence hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ $filters['fecha_hasta'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Orden</label>
                    <select name="orden" class="form-select">
                        <option value="periodo_desc" @selected(($filters['orden'] ?? 'periodo_desc') === 'periodo_desc')>Periodo reciente</option>
                        <option value="periodo_asc" @selected(($filters['orden'] ?? '') === 'periodo_asc')>Periodo antiguo</option>
                        <option value="saldo_desc" @selected(($filters['orden'] ?? '') === 'saldo_desc')>Saldo mayor</option>
                        <option value="saldo_asc" @selected(($filters['orden'] ?? '') === 'saldo_asc')>Saldo menor</option>
                        <option value="cobertura_desc" @selected(($filters['orden'] ?? '') === 'cobertura_desc')>Cobertura alta</option>
                        <option value="cobertura_asc" @selected(($filters['orden'] ?? '') === 'cobertura_asc')>Cobertura baja</option>
                    </select>
                </div>
            </div>

            <div class="rep-filter-actions">
                <div class="rep-filter-inline">
                    <button class="btn jp-btn-search" type="submit">
                        <i data-feather="search"></i> Aplicar filtros
                    </button>
                    <a href="{{ route('secretaria.reportes.pensiones') }}" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>

                <a href="{{ route('secretaria.reportes.pensiones.excel', $exportQuery) }}" class="btn jp-btn-incidencia">
                    <i data-feather="download"></i>
                    Exportar Excel
                </a>
            </div>
        </form>

        <div class="rep-table-card">
            <div class="rep-table-wrap">
                <table class="table rep-table jp-table">
                    <thead>
                        <tr>
                            <th>Periodo</th>
                            <th>Estudiantes</th>
                            <th>Monto</th>
                            <th>Cobrado</th>
                            <th>Saldo</th>
                            <th>Cobertura</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resumen as $periodo => $data)
                            @php
                                $periodoLabel = (string) $periodo;
                                if ($periodoLabel !== 'Sin periodo') {
                                    if (preg_match('/^\d{4}-\d{2}$/', $periodoLabel)) {
                                        $periodoLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $periodoLabel)
                                            ->startOfMonth()
                                            ->format('d/m/Y');
                                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodoLabel)) {
                                        $periodoLabel = \Illuminate\Support\Carbon::parse($periodoLabel)->format('d/m/Y');
                                    }
                                }
                                $cobertura = (float) ($data['cobertura'] ?? 0);
                                $estadoClass = $cobertura >= 95 ? 'is-ok' : ($cobertura >= 70 ? 'is-warn' : 'is-danger');
                                $estadoTexto = $cobertura >= 95 ? 'Al dia' : ($cobertura >= 70 ? 'Seguimiento' : 'Critico');
                            @endphp
                            <tr>
                                <td>{{ $periodoLabel }}</td>
                                <td>{{ number_format((int) ($data['estudiantes'] ?? 0)) }}</td>
                                <td>S/ {{ number_format((float) ($data['monto'] ?? 0), 2) }}</td>
                                <td>S/ {{ number_format((float) ($data['cobrado'] ?? 0), 2) }}</td>
                                <td>S/ {{ number_format((float) ($data['saldo'] ?? 0), 2) }}</td>
                                <td style="min-width: 160px;">
                                    <div class="rep-risk-item__bar">
                                        <div class="rep-risk-item__fill" style="width: {{ min(100, max(0, $cobertura)) }}%;"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($cobertura, 2) }}%</small>
                                </td>
                                <td><span class="rep-badge {{ $estadoClass }}">{{ $estadoTexto }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="rep-empty">No hay periodos para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Totales de la pagina</th>
                            <th>S/ {{ number_format($totalMontoPagina, 2) }}</th>
                            <th>S/ {{ number_format($totalCobradoPagina, 2) }}</th>
                            <th>S/ {{ number_format($totalSaldoPagina, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer">
                {{ $resumen->links() }}
            </div>
        </div>
    </div>
@endsection
