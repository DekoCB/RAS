@extends('layouts.mantis')

@section('title', 'Morosos | Secretaria')
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
        $totalRiesgo = max(1, collect($riesgoResumen ?? [])->sum());
    @endphp

    <div class="rep-shell">
        @include('secretaria.reportes.partials.tabs', ['active' => 'morosos'])

        <div class="rep-header">
            <div>
                <h5 class="rep-header__title">Seguimiento de morosidad</h5>
                <p class="rep-header__sub">Segmenta por riesgo, saldo y vencimiento para priorizar cobranza.</p>
            </div>
            <div class="rep-header__meta">
                Corte: {{ optional($fechaCorte)->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="rep-split">
            <div class="rep-kpi-grid">
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Registros</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($kpis['registros'] ?? 0)) }}</div>
                    <div class="rep-kpi__hint">Deudas filtradas</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Saldo total</div>
                    <div class="rep-kpi__value">S/ {{ number_format((float) ($kpis['saldo_total'] ?? 0), 2) }}</div>
                    <div class="rep-kpi__hint">Exigible actual</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Vencidos</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($kpis['vencidos'] ?? 0)) }}</div>
                    <div class="rep-kpi__hint">Con atraso real</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Por vencer (7 dias)</div>
                    <div class="rep-kpi__value">{{ number_format((int) ($kpis['por_vencer_7'] ?? 0)) }}</div>
                    <div class="rep-kpi__hint">Preventivo</div>
                </div>
                <div class="rep-kpi">
                    <div class="rep-kpi__label">Saldo promedio</div>
                    <div class="rep-kpi__value">S/ {{ number_format((float) ($kpis['saldo_promedio'] ?? 0), 2) }}</div>
                    <div class="rep-kpi__hint">Por deuda</div>
                </div>
            </div>

            <div class="rep-filter-card">
                <div class="rep-kpi__label mb-2">Semaforo de riesgo</div>
                <div class="rep-risk-stack">
                    @foreach ([
                        '1_7' => 'Leve 1-7 dias',
                        '8_30' => 'Medio 8-30 dias',
                        '31_60' => 'Alto 31-60 dias',
                        '60_plus' => 'Critico +60 dias',
                        'sin_fecha' => 'Sin fecha',
                    ] as $key => $label)
                        @php
                            $valor = (int) ($riesgoResumen[$key] ?? 0);
                            $pct = min(100, round(($valor * 100) / $totalRiesgo, 2));
                        @endphp
                        <div class="rep-risk-item">
                            <div class="rep-risk-item__label">{{ $label }}</div>
                            <div class="rep-risk-item__bar">
                                <div class="rep-risk-item__fill" style="width: {{ $pct }}%;"></div>
                            </div>
                            <div class="rep-risk-item__value">{{ $valor }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('secretaria.reportes.morosos') }}" class="rep-filter-card">
            <div class="rep-filter-grid">
                <div>
                    <label class="form-label small text-muted">DNI</label>
                    <input type="text" name="dni" class="form-control" value="{{ $filters['dni'] ?? '' }}" placeholder="Buscar DNI">
                </div>
                <div>
                    <label class="form-label small text-muted">Grado</label>
                    <select name="grado_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($grados as $grado)
                            <option value="{{ $grado->id }}" @selected((int) ($filters['grado_id'] ?? 0) === (int) $grado->id)>
                                {{ $grado->nivel }} {{ $grado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Seccion</label>
                    <select name="seccion_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($secciones as $seccion)
                            <option value="{{ $seccion->id }}" @selected((int) ($filters['seccion_id'] ?? 0) === (int) $seccion->id)>
                                {{ $seccion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Concepto</label>
                    <select name="concepto_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($conceptos as $concepto)
                            <option value="{{ $concepto->id }}" @selected((int) ($filters['concepto_id'] ?? 0) === (int) $concepto->id)>
                                {{ $concepto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="todos" @selected(($filters['estado'] ?? 'todos') === 'todos')>Todos</option>
                        <option value="pendiente" @selected(($filters['estado'] ?? '') === 'pendiente')>Pendiente</option>
                        <option value="parcial" @selected(($filters['estado'] ?? '') === 'parcial')>Parcial</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted">Riesgo</label>
                    <select name="riesgo" class="form-select">
                        <option value="todos" @selected(($filters['riesgo'] ?? 'todos') === 'todos')>Todos</option>
                        <option value="1_7" @selected(($filters['riesgo'] ?? '') === '1_7')>Leve (1-7)</option>
                        <option value="8_30" @selected(($filters['riesgo'] ?? '') === '8_30')>Medio (8-30)</option>
                        <option value="31_60" @selected(($filters['riesgo'] ?? '') === '31_60')>Alto (31-60)</option>
                        <option value="60_plus" @selected(($filters['riesgo'] ?? '') === '60_plus')>Critico (+60)</option>
                        <option value="sin_fecha" @selected(($filters['riesgo'] ?? '') === 'sin_fecha')>Sin fecha</option>
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
                        <option value="vencimiento_asc" @selected(($filters['orden'] ?? 'vencimiento_asc') === 'vencimiento_asc')>Vence primero</option>
                        <option value="vencimiento_desc" @selected(($filters['orden'] ?? '') === 'vencimiento_desc')>Vence ultimo</option>
                        <option value="saldo_desc" @selected(($filters['orden'] ?? '') === 'saldo_desc')>Saldo mayor</option>
                        <option value="saldo_asc" @selected(($filters['orden'] ?? '') === 'saldo_asc')>Saldo menor</option>
                        <option value="reciente" @selected(($filters['orden'] ?? '') === 'reciente')>Mas recientes</option>
                    </select>
                </div>
                <div class="d-flex align-items-end">
                    <div class="form-check pb-2">
                        <input class="form-check-input" type="checkbox" name="solo_vencidos" value="1" id="soloVencidos"
                            @checked(!empty($filters['solo_vencidos']))>
                        <label class="form-check-label" for="soloVencidos">
                            Solo vencidos
                        </label>
                    </div>
                </div>
            </div>

            <div class="rep-filter-actions">
                <div class="rep-filter-inline">
                    <button class="btn jp-btn-search" type="submit">
                        <i data-feather="search"></i> Aplicar filtros
                    </button>
                    <a href="{{ route('secretaria.reportes.morosos') }}" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>

                <a href="{{ route('secretaria.reportes.morosos.excel', $exportQuery) }}" class="btn jp-btn-incidencia">
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
                            <th>Alumno</th>
                            <th>Concepto</th>
                            <th>Periodo</th>
                            <th>Monto</th>
                            <th>Saldo</th>
                            <th>Vencimiento</th>
                            <th>Atraso</th>
                            <th>Riesgo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deudas as $deuda)
                            @php
                                $estudiante = $deuda->matricula?->estudiante;
                                $nombreAlumno = $estudiante
                                    ? trim(($estudiante->apellidos ?? '') . ', ' . ($estudiante->nombres ?? ''), ' ,')
                                    : '-';
                                $dniAlumno = $estudiante?->dni ?? '-';
                                $dias = $deuda->dias_atraso;
                                $riesgo = $deuda->riesgo_key ?? 'sin_fecha';
                                $riesgoClass = match ($riesgo) {
                                    'al_dia' => 'is-ok',
                                    '1_7', '8_30' => 'is-warn',
                                    '31_60', '60_plus' => 'is-danger',
                                    default => 'is-neutral',
                                };
                                $estadoClass = strtolower((string) $deuda->estado) === 'parcial' ? 'is-warn' : 'is-danger';
                            @endphp
                            <tr>
                                <td>
                                    <div>{{ $nombreAlumno }}</div>
                                    <small class="text-muted">{{ $dniAlumno }}</small>
                                </td>
                                <td>{{ $deuda->concepto?->nombre ?? '-' }}</td>
                                <td>{{ $deuda->periodo ?? '-' }}</td>
                                <td>S/ {{ number_format((float) $deuda->monto, 2) }}</td>
                                <td>S/ {{ number_format((float) $deuda->saldo, 2) }}</td>
                                <td>{{ $deuda->fecha_vencimiento ? \Illuminate\Support\Carbon::parse($deuda->fecha_vencimiento)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $dias === null ? '-' : $dias . ' dias' }}</td>
                                <td><span class="rep-badge {{ $riesgoClass }}">{{ $deuda->riesgo_label ?? 'Sin fecha' }}</span></td>
                                <td><span class="rep-badge {{ $estadoClass }}">{{ ucfirst($deuda->estado ?? '-') }}</span></td>
                                <td>
                                    <div class="rep-actions">
                                        @if ($estudiante?->dni)
                                            <a class="rep-link-btn" href="{{ route('secretaria.reportes.alumno', ['dni' => $estudiante->dni]) }}">
                                                <i data-feather="user"></i> Perfil
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="rep-empty">No hay registros con los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Totales de la pagina ({{ number_format((int) ($totalesPagina['registros'] ?? 0)) }} registros)</th>
                            <th>S/ {{ number_format((float) ($totalesPagina['monto'] ?? 0), 2) }}</th>
                            <th>S/ {{ number_format((float) ($totalesPagina['saldo'] ?? 0), 2) }}</th>
                            <th colspan="5"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer">
                {{ $deudas->links() }}
            </div>
        </div>
    </div>
@endsection
