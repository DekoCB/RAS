@extends('layouts.mantis')

@section('title', 'Auditoria | Sistema de Raul')
@section('page-title', 'Auditoria')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card jp-soft-card jp-audit-hero">
                <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1 jp-section-title">
                            <i data-feather="activity"></i>
                            Auditoria del sistema
                        </h4>
                        <p class="text-muted mb-3">
                            Monitorea acciones, cambios y accesos registrados en los modulos clave.
                        </p>
                        <div class="jp-audit-chips">
                            <span class="jp-audit-chip jp-audit-chip--primary">
                                <i data-feather="clipboard"></i>
                                {{ number_format($totalLogs) }} registros totales
                            </span>
                            <span class="jp-audit-chip jp-audit-chip--warning">
                                <i data-feather="calendar"></i>
                                {{ number_format($logsHoy) }} hoy
                            </span>
                            <span class="jp-audit-chip jp-audit-chip--danger">
                                <i data-feather="activity"></i>
                                {{ number_format($logsSemana) }} ultimos 7 dias
                            </span>
                        </div>
                    </div>
                    <div class="jp-audit-meta">
                        <span class="jp-audit-chip jp-audit-chip--neutral">
                            <i data-feather="shield"></i>
                            Control total y trazabilidad
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form class="card jp-soft-card jp-audit-filter" method="GET" action="{{ route('admin-sistema.auditoria.index') }}">
        <div class="card-body row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label">Buscar</label>
                <div class="input-group jp-search-input">
                    <span class="input-group-text">
                        <i data-feather="search"></i>
                    </span>
                    <input type="text" name="buscar" class="form-control"
                        placeholder="Accion, tabla, usuario, IP..." value="{{ request('buscar') }}">
                </div>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Accion</label>
                <select name="accion" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($acciones as $accion)
                        <option value="{{ $accion }}" @selected(request('accion') === $accion)>{{ $accion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Tabla</label>
                <select name="tabla" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($tablas as $tabla)
                        <option value="{{ $tabla }}" @selected(request('tabla') === $tabla)>{{ $tabla }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Usuario</label>
                <select name="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected((string) request('user_id') === (string) $usuario->id)>
                            {{ $usuario->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" class="form-control" value="{{ request('desde') }}">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}">
            </div>
            <div class="col-lg-2">
                <button type="submit" class="btn jp-btn-search w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="card jp-soft-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Registros de auditoria</h5>
            <span class="text-muted">Mostrando {{ $logs->total() }} registros</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table jp-usuarios-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Accion</th>
                            <th>Tabla</th>
                            <th>Registro</th>
                            <th>IP</th>
                            <th>Cambios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $accion = strtolower((string) $log->accion);
                                $badgeClass = 'bg-secondary';
                                if (str_contains($accion, 'crear') || str_contains($accion, 'create')) {
                                    $badgeClass = 'bg-success';
                                } elseif (str_contains($accion, 'actualizar') || str_contains($accion, 'update')) {
                                    $badgeClass = 'bg-primary';
                                } elseif (str_contains($accion, 'eliminar') || str_contains($accion, 'delete')) {
                                    $badgeClass = 'bg-danger';
                                } elseif (str_contains($accion, 'login') || str_contains($accion, 'acceso')) {
                                    $badgeClass = 'bg-warning text-dark';
                                }
                            @endphp
                            <tr>
                                <td>{{ $log->fecha?->format('d-m-Y H:i') ?? '-' }}</td>
                                <td>
                                    <strong>{{ $log->user->name ?? 'Sistema' }}</strong>
                                    <div class="text-muted small">{{ $log->user->email ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $log->accion }}</span>
                                </td>
                                <td>{{ $log->tabla }}</td>
                                <td>#{{ $log->registro_id }}</td>
                                <td>{{ $log->ip ?? '-' }}</td>
                                <td>
                                    @if (!empty($log->cambios))
                                        {{ \Illuminate\Support\Str::limit(json_encode($log->cambios, JSON_UNESCAPED_UNICODE), 80) }}
                                    @else
                                        <span class="text-muted">Sin cambios</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay registros de auditoria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="jp-table-pagination mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection

