@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp
@section('title', 'Pagos del Dia | Secretaria')
@section('page-title', 'Reportes')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

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
        @include('secretaria.reportes.partials.tabs', ['active' => 'pagos-dia'])

        <div class="rep-header">
            <div>
                <h5 class="rep-header__title">Reporte operativo de pagos</h5>
                <p class="rep-header__sub">Control de caja con filtros avanzados, resumen rapido y exportacion.</p>
            </div>
            <div class="rep-header__meta">
                Corte: {{ optional($fechaCorte)->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="rep-split">
            <div class="rep-kpi-grid">
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Operaciones</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($kpis['operaciones'] ?? 0)) }}</div>
                    <div class="rep-kpi__hint">Pagos filtrados</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Monto total</div>
                    <div class="rep-kpi__value">S/ {{ number_format((float) ($kpis['monto_total'] ?? 0), 2) }}</div>
                    <div class="rep-kpi__hint">Acumulado</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Confirmados</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($kpis['confirmados'] ?? 0)) }}</div>
                    <div class="rep-kpi__hint">Estado confirmado</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Pendientes</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($kpis['pendientes'] ?? 0)) }}</div>
                    <div class="rep-kpi__hint">Requieren revision</div>
                </div>

            </div>

            <div class="rep-filter-card">
                <div class="rep-kpi__label mb-2">Distribucion por metodo</div>
                @if (($metodoResumen ?? collect())->count())
                    <div class="rep-risk-stack">
                        @foreach ($metodoResumen as $metodo => $total)
                            @php
                                $base = max(1, (int) (($kpis['operaciones'] ?? 0)));
                                $pct = min(100, round(((int) $total * 100) / $base, 2));
                            @endphp
                            <div class="rep-risk-item">
                                <div class="rep-risk-item__label">{{ $metodo }}</div>
                                <div class="rep-risk-item__bar">
                                    <div class="rep-risk-item__fill" style="width: {{ $pct }}%;"></div>
                                </div>
                                <div class="rep-risk-item__value">{{ $total }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rep-empty">No hay datos para graficar metodos.</div>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route($rp . 'reportes.pagos-dia') }}" class="rep-filter-card">
            <div class="rep-filter-grid">
                <div>
                    <label class="form-label small text-muted">DNI</label>
                    <input type="text" name="dni" class="form-control" value="{{ $filters['dni'] ?? '' }}"
                        placeholder="Buscar DNI">
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
                    <label class="form-label small text-muted">Metodo</label>
                    <select name="metodo" class="form-select">
                        <option value="todos">Todos</option>
                        @foreach ($metodosDisponibles as $metodo)
                            <option value="{{ $metodo }}" @selected(($filters['metodo'] ?? 'todos') === $metodo)>{{ $metodo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="todos" @selected(($filters['estado'] ?? 'todos') === 'todos')>Todos</option>
                        <option value="confirmado" @selected(($filters['estado'] ?? '') === 'confirmado')>Confirmado</option>
                        <option value="pendiente" @selected(($filters['estado'] ?? '') === 'pendiente')>Pendiente</option>
                        <option value="anulado" @selected(($filters['estado'] ?? '') === 'anulado')>Anulado</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Monto min</label>
                    <input type="number" step="0.01" min="0" name="monto_min" class="form-control"
                        value="{{ $filters['monto_min'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Monto max</label>
                    <input type="number" step="0.01" min="0" name="monto_max" class="form-control"
                        value="{{ $filters['monto_max'] ?? '' }}">
                </div>
                <div>
                    <label class="form-label small text-muted">Orden</label>
                    <select name="orden" class="form-select">
                        <option value="reciente" @selected(($filters['orden'] ?? 'reciente') === 'reciente')>Mas recientes</option>
                        <option value="antiguo" @selected(($filters['orden'] ?? '') === 'antiguo')>Mas antiguos</option>
                        <option value="monto_desc" @selected(($filters['orden'] ?? '') === 'monto_desc')>Monto mayor</option>
                        <option value="monto_asc" @selected(($filters['orden'] ?? '') === 'monto_asc')>Monto menor</option>
                    </select>
                </div>
            </div>

            <div class="rep-filter-actions">
                <div class="rep-filter-inline">
                    <button class="btn jp-btn-search" type="submit">
                        <i data-feather="search"></i> Aplicar filtros
                    </button>
                    <a href="{{ route($rp . 'reportes.pagos-dia') }}" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>

                <a href="{{ route($rp . 'reportes.pagos-dia.excel', $exportQuery) }}" class="btn jp-btn-incidencia">
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
                            <th>Fecha</th>
                            <th>DNI / Alumno</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Metodo</th>
                            <th>Estado</th>
                            <th>Registrado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagos as $pago)
                            @php
                                $deuda = $pago->deudas->first();
                                $estudiante = $deuda?->matricula?->estudiante;
                                $concepto = $deuda?->concepto?->nombre;
                                $conceptoLabel = $pago->deudas->count() > 1 ? 'Varios' : ($concepto ?? '-');
                                $nombreAlumno = $estudiante
                                    ? trim(($estudiante->apellidos ?? '') . ', ' . ($estudiante->nombres ?? ''), ' ,')
                                    : '-';
                                $dniAlumno = $estudiante?->dni ?? '-';
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
                                <td>
                                    <div>{{ $dniAlumno }}</div>
                                    <small class="text-muted">{{ $nombreAlumno }}</small>
                                </td>
                                <td>{{ $conceptoLabel }}</td>
                                <td>S/ {{ number_format((float) $pago->monto, 2) }}</td>
                                <td>{{ $pago->metodo_display }}</td>
                                <td>
                                    <span class="rep-badge {{ $estadoClass }}">{{ ucfirst($estado) }}</span>
                                </td>
                                <td>{{ $pago->registradoPor->name ?? '-' }}</td>
                                <td>
                                    <div class="rep-actions">
                                        @if ($estudiante?->dni)
                                            <a class="rep-link-btn" href="{{ route($rp . 'reportes.alumno', ['dni' => $estudiante->dni]) }}">
                                                <i data-feather="user"></i> Alumno
                                            </a>
                                        @endif
                                        <a class="rep-link-btn" href="{{ route($rp . 'pagos.show', $pago) }}">
                                            <i data-feather="eye"></i> Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="rep-empty">No hay pagos para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Totales de la pagina ({{ number_format((int) ($totalesPagina['registros'] ?? 0)) }} registros)</th>
                            <th>S/ {{ number_format((float) ($totalesPagina['monto'] ?? 0), 2) }}</th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer">
                {{ $pagos->links() }}
            </div>
        </div>
    </div>
@endsection
