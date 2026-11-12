@extends('layouts.mantis')

@section('title', 'Backups | Sistema de Raul')
@section('page-title', 'Backups')
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
                            <i data-feather="database"></i>
                            Backups
                        </span>
                        <h5 class="mb-1 jp-role-title">Gestión de respaldos</h5>
                        <p class="mb-0 text-muted jp-role-sub">
                            Configura respaldos automáticos y controla el historial de copias de seguridad.
                        </p>
                    </div>
                    <div>
                        <button class="btn jp-btn-search" disabled>Nuevo backup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card jp-soft-card">
                <div class="card-header">
                    <h5 class="mb-0">Acciones rápidas</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn jp-btn-search" disabled>Crear backup ahora</button>
                    <button type="button" class="btn btn-outline-danger" disabled>Restaurar backup</button>
                    <button type="button" class="btn btn-outline-primary" disabled>Descargar último backup</button>
                    <div class="text-muted small">
                        Las acciones estarán habilitadas cuando se active la lógica de backups.
                    </div>
                </div>
            </div>
            <div class="card jp-soft-card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Tipo de backup</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" checked disabled>
                        <label class="form-check-label">Base de datos</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label">Archivos (storage, imágenes)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label">Backup completo</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card jp-soft-card">
                <div class="card-header">
                    <h5 class="mb-0">Programación automática</h5>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Frecuencia</label>
                        <select class="form-select" disabled>
                            <option>Diario</option>
                            <option>Semanal</option>
                            <option>Mensual</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hora</label>
                        <input type="time" class="form-control" value="02:00" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Retención</label>
                        <select class="form-select" disabled>
                            <option>7 backups</option>
                            <option>15 backups</option>
                            <option>30 backups</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Destino</label>
                        <select class="form-select" disabled>
                            <option>Local (servidor)</option>
                            <option>Google Drive</option>
                            <option>Amazon S3</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card jp-soft-card mt-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Historial de backups</h5>
                    <span class="text-muted">Últimas ejecuciones</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table jp-usuarios-table mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Destino</th>
                                    <th>Tamaño</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Aún no hay backups registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="jp-table-pagination mt-3">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item disabled"><span class="page-link">Â«</span></li>
                                <li class="page-item active"><span class="page-link">1</span></li>
                                <li class="page-item disabled"><span class="page-link">Â»</span></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



