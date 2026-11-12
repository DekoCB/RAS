@extends('layouts.mantis')

@section('title', 'Asistencias Docentes | ' . $panelLabel)
@section('page-title', 'Asistencias Docentes')
@section('body-class', $panel === 'director' ? 'role-director' : 'role-superadmin')

@push('styles')
    @if ($panel === 'director')
        <link rel="stylesheet" href="{{ url('css/director-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
    @endif

    <style>
        .jp-att-card {
            border-radius: 20px;
            border: 1px solid #d6e5ef;
            background: #fff;
            box-shadow: 0 16px 28px rgba(15, 19, 42, 0.08);
        }
        .jp-att-filter-card {
            background: linear-gradient(180deg, #f3faff 0%, #ffffff 100%);
        }
        .jp-att-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .jp-att-title {
            margin-bottom: 4px;
            font-weight: 800;
            color: #11194a;
        }
        .jp-att-sub {
            margin: 0;
            color: #6b7280;
        }
        .jp-btn-export {
            border: 0;
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(90deg, #15803d, #16a34a);
            box-shadow: 0 12px 18px rgba(22, 163, 74, 0.24);
        }
        .jp-btn-export:hover {
            color: #fff;
            background: linear-gradient(90deg, #166534, #15803d);
        }
        .jp-att-filter {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.8fr) auto;
            gap: 14px;
            align-items: end;
        }
        .jp-att-filter .form-label {
            font-weight: 700;
            color: #2a3f5b;
        }
        .jp-att-filter .form-control,
        .jp-att-filter .form-select {
            border-radius: 12px;
            border: 1px solid #cee0ea;
        }
        .jp-att-filter .form-control:focus,
        .jp-att-filter .form-select:focus {
            border-color: #22329c;
            box-shadow: 0 0 0 0.2rem rgba(34, 50, 156, 0.18);
        }
        .jp-att-actions {
            display: grid;
            gap: 8px;
            min-width: 190px;
        }
        .jp-btn-filter {
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(90deg, #1c297e, #213196);
            box-shadow: 0 10px 16px rgba(28, 41, 126, 0.22);
        }
        .jp-btn-filter:hover {
            color: #fff;
            background: linear-gradient(90deg, #18246e, #1d2a82);
        }
        .jp-btn-clean {
            border-radius: 999px;
            font-weight: 700;
            border-color: #ccdeec;
            color: #151f5f;
            background: #fff;
        }
        .jp-btn-clean:hover {
            color: #151f5f;
            background: #edf8ff;
            border-color: #bfddf3;
        }
        .jp-att-summary-header {
            border-bottom: 1px solid #dce7f1;
            background: #f0f9ff;
        }
        .jp-att-summary-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .jp-att-summary-title {
            margin: 0;
            font-weight: 800;
            color: #11194a;
        }
        .jp-att-summary-sub {
            margin: 4px 0 0;
            color: #6b7280;
        }
        .jp-att-note {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #d3e2eb;
            background: #fff;
            color: #1d265e;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .jp-att-chip-wrap {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .jp-att-chip {
            border-radius: 14px;
            border: 1px solid #dce7f1;
            background: #f5faff;
            padding: 10px 12px;
        }
        .jp-att-chip span {
            display: block;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .jp-att-chip strong {
            font-size: 1.35rem;
            line-height: 1;
            font-weight: 800;
            color: #111427;
        }
        .jp-att-chip.is-ok strong {
            color: #166534;
        }
        .jp-att-chip.is-warn strong {
            color: #18246e;
        }
        .jp-att-chip.is-danger strong {
            color: #991b1b;
        }
        .jp-att-table {
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 14px;
        }
        .jp-att-table thead th {
            background: linear-gradient(90deg, #1c297e, #213196);
            color: #fff;
            border: none;
            font-weight: 700;
            white-space: nowrap;
        }
        .jp-att-table thead th:first-child {
            border-top-left-radius: 12px;
        }
        .jp-att-table thead th:last-child {
            border-top-right-radius: 12px;
        }
        .jp-att-table tbody td {
            border-top: 1px solid #dce7f1;
            vertical-align: middle;
        }
        .jp-att-table tbody tr:hover {
            background: rgba(28, 41, 126, 0.04);
        }
        .jp-att-state {
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 0.74rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .jp-att-state.is-ok {
            background: #dcfce7;
            color: #166534;
        }
        .jp-att-state.is-warn {
            background: #c7effe;
            color: #17226a;
        }
        .jp-att-state.is-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .jp-att-state.is-muted {
            background: #e5e7eb;
            color: #374151;
        }
        @media (max-width: 1200px) {
            .jp-att-filter {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .jp-att-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                min-width: 0;
            }
            .jp-att-chip-wrap {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .jp-att-filter {
                grid-template-columns: 1fr;
            }
            .jp-att-actions {
                grid-template-columns: 1fr;
            }
            .jp-att-chip-wrap {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('sidebar')
    @include($sidebarPartial)
@endsection

@section('content')
    @php
        $anioLabel = 'Todos';
        if (!empty($filters['anio_escolar_id'])) {
            $anio = $anios->firstWhere('id', (int) $filters['anio_escolar_id']);
            $anioLabel = $anio?->nombre ?? 'Todos';
        }
    @endphp

    <div class="card jp-att-card jp-att-filter-card mt-2">
        <div class="card-body">
            <div class="jp-att-toolbar">
                <div>
                    <h5 class="jp-att-title">Asistencias de docentes</h5>
                    <p class="jp-att-sub">Panel {{ $panelLabel }}. Visualiza entradas y salidas registradas por QR.</p>
                </div>
                <a href="{{ route($exportRoute, request()->except('page')) }}" class="btn jp-btn-export">
                    <i data-feather="download"></i> Exportar Excel
                </a>
            </div>

            <form method="GET" action="{{ route($indexRoute) }}" class="jp-att-filter mt-3">
                <div>
                    <label class="form-label">Anio escolar</label>
                    <select name="anio_escolar_id" class="form-select">
                        <option value="" @selected($filters['anio_escolar_selected'] === '')>Todos</option>
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id }}" @selected($filters['anio_escolar_selected'] === (string) $anio->id)>
                                {{ $anio->nombre }}{{ $anio->activo ? ' (Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Desde</label>
                    <input type="date" name="desde" value="{{ $filters['desde'] }}" class="form-control">
                </div>

                <div>
                    <label class="form-label">Hasta</label>
                    <input type="date" name="hasta" value="{{ $filters['hasta'] }}" class="form-control">
                </div>

                <div>
                    <label class="form-label">Buscar docente</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Nombre, apellido, DNI o codigo">
                </div>

                <div class="jp-att-actions">
                    <button type="submit" class="btn jp-btn-filter">
                        <i data-feather="search"></i> Filtrar
                    </button>
                    <a href="{{ route($indexRoute) }}" class="btn jp-btn-clean">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card jp-att-card mt-3">
        <div class="card-header jp-att-summary-header">
            <div class="jp-att-summary-head">
                <div>
                    <h5 class="jp-att-summary-title">Resumen general</h5>
                    <p class="jp-att-summary-sub">Anio {{ $anioLabel }}</p>
                </div>
                <span class="jp-att-note">
                    <i data-feather="clock"></i>
                    Hora local Lima (America/Lima)
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="jp-att-chip-wrap">
                <div class="jp-att-chip">
                    <span>Total registros</span>
                    <strong>{{ number_format($resumen['total']) }}</strong>
                </div>
                <div class="jp-att-chip is-ok">
                    <span>Completos</span>
                    <strong>{{ number_format($resumen['completos']) }}</strong>
                </div>
                <div class="jp-att-chip is-warn">
                    <span>Sin salida</span>
                    <strong>{{ number_format($resumen['sin_salida']) }}</strong>
                </div>
                <div class="jp-att-chip is-danger">
                    <span>Sin entrada</span>
                    <strong>{{ number_format($resumen['sin_entrada']) }}</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover jp-att-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Docente</th>
                            <th>DNI</th>
                            <th>Codigo</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($asistencias as $a)
                            @php
                                $stateClass = match ($a->estado) {
                                    'completo' => 'jp-att-state is-ok',
                                    'sin_salida' => 'jp-att-state is-warn',
                                    'sin_entrada' => 'jp-att-state is-danger',
                                    default => 'jp-att-state is-muted',
                                };
                            @endphp
                            <tr>
                                <td>{{ $a->fecha_label }}</td>
                                <td>{{ trim(($a->apellidos ?? '') . ' ' . ($a->nombres ?? '')) }}</td>
                                <td>{{ $a->dni ?: '-' }}</td>
                                <td>{{ $a->codigo_label }}</td>
                                <td>
                                    @if ($a->entrada_label)
                                        <span class="badge bg-success">{{ $a->entrada_label }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($a->salida_label)
                                        <span class="badge bg-primary">{{ $a->salida_label }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $stateClass }}">
                                        <i data-feather="activity"></i>
                                        {{ $a->estado_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No hay asistencias docentes con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $asistencias->links() }}
        </div>
    </div>
@endsection
