@extends('layouts.mantis')

@section('title', 'Soporte | Sistema de Raul')
@section('page-title', 'Soporte')
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
            <div class="card jp-role-hero">
                <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <span class="jp-role-pill">
                            <i data-feather="life-buoy"></i>
                            Soporte
                        </span>
                        <h5 class="mb-1 jp-role-title">Centro de soporte</h5>
                        <p class="mb-0 text-muted jp-role-sub">
                            Gestiona incidencias internas, asignaciones y estados de atención para cada reporte.
                        </p>
                    </div>
                    <div>
                        <button class="btn jp-btn-search" disabled>Nuevo ticket</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form class="card jp-soft-card jp-audit-filter" method="GET" action="{{ route('admin-sistema.soporte.index') }}">
        <div class="card-body row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label">Buscar ticket</label>
                <div class="input-group jp-search-input">
                    <span class="input-group-text">
                        <i data-feather="search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Código, usuario o descripción" disabled>
                </div>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Estado</label>
                <select class="form-select" disabled>
                    <option>Todos</option>
                    <option>Abierto</option>
                    <option>En proceso</option>
                    <option>Resuelto</option>
                    <option>Cerrado</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Prioridad</label>
                <select class="form-select" disabled>
                    <option>Todas</option>
                    <option>Alta</option>
                    <option>Media</option>
                    <option>Baja</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Módulo</label>
                <select class="form-select" disabled>
                    <option>Todos</option>
                    <option>Matrículas</option>
                    <option>Pagos</option>
                    <option>Notas</option>
                    <option>Asistencias</option>
                </select>
            </div>
            <div class="col-lg-2">
                <button type="button" class="btn jp-btn-search w-100" disabled>Filtrar</button>
            </div>
        </div>
    </form>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card jp-soft-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Bandeja de tickets</h5>
                    <button type="button" class="btn btn-outline-primary" disabled>Nuevo ticket interno</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table jp-usuarios-table mb-0">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Usuario</th>
                                    <th>Módulo</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Asignado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Aún no hay tickets registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="jp-table-pagination mt-3">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item disabled"><span class="page-link">«</span></li>
                                <li class="page-item active"><span class="page-link">1</span></li>
                                <li class="page-item disabled"><span class="page-link">»</span></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card jp-soft-card">
                <div class="card-header">
                    <h5 class="mb-0">Detalle del ticket</h5>
                </div>
                <div class="card-body">
                    <div class="text-muted">
                        Selecciona un ticket para ver detalles, historial de respuestas y asignar responsables.
                    </div>
                    <div class="mt-3 d-grid gap-2">
                        <button type="button" class="btn jp-btn-search" disabled>Marcar en proceso</button>
                        <button type="button" class="btn btn-outline-success" disabled>Resolver</button>
                        <button type="button" class="btn btn-outline-danger" disabled>Cerrar ticket</button>
                    </div>
                </div>
            </div>

            <div class="card jp-soft-card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Base de conocimiento</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Error en Matrículas
                            <span class="badge bg-light text-dark">Guia</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Restablecer contraseña
                            <span class="badge bg-light text-dark">FAQ</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Pago no registrado
                            <span class="badge bg-light text-dark">Checklist</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection



