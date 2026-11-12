@extends('layouts.mantis')

@section('title', 'Configuración | Sistema de Raul')
@section('page-title', 'Configuración')
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
                            <i data-feather="settings"></i>
                            Configuración
                        </span>
                        <h5 class="mb-1 jp-role-title">Configuración general</h5>
                        <p class="mb-0 text-muted jp-role-sub">
                            Controla parámetros globales, seguridad y notificaciones desde un solo panel.
                        </p>
                    </div>
                    <div>
                        <button class="btn jp-btn-search" disabled>Guardar configuración</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card jp-soft-card">
                <div class="card-header">
                    <h5 class="mb-0">Configuración general</h5>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del sistema</label>
                        <input type="text" class="form-control" value="Sistema de Raul" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Zona horaria</label>
                        <select class="form-select" disabled>
                            <option>America/Lima</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Idioma</label>
                        <select class="form-select" disabled>
                            <option>Español</option>
                            <option>Inglés</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Moneda</label>
                        <select class="form-select" disabled>
                            <option>S/ (PEN)</option>
                            <option>USD</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mensaje institucional</label>
                        <textarea class="form-control" rows="3" disabled>Bienvenidos al sistema escolar.</textarea>
                    </div>
                </div>
            </div>

            <div class="card jp-soft-card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Notificaciones</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" checked disabled>
                        <label class="form-check-label">Avisos por correo</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label">Notificaciones push</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label">Recordatorios automáticos</label>
                    </div>
                    <div class="text-muted small mt-3">
                        Configura canales de comunicación y recordatorios por módulo.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card jp-soft-card">
                <div class="card-header">
                    <h5 class="mb-0">Seguridad</h5>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Caducidad de contraseña</label>
                        <select class="form-select" disabled>
                            <option>90 días</option>
                            <option>180 días</option>
                            <option>Nunca</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Intentos de acceso</label>
                        <input type="number" class="form-control" value="5" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Política de bloqueo</label>
                        <textarea class="form-control" rows="3" disabled>Bloquear usuario por 15 minutos.</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked disabled>
                            <label class="form-check-label">Habilitar doble factor (2FA)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card jp-soft-card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Integraciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <strong>Correo SMTP</strong>
                            <div class="text-muted small">Envío de correos</div>
                        </div>
                        <span class="badge bg-success">Activo</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <strong>Google Drive</strong>
                            <div class="text-muted small">Backups en la nube</div>
                        </div>
                        <span class="badge bg-secondary">Inactivo</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <strong>SMS</strong>
                            <div class="text-muted small">Notificaciones urgentes</div>
                        </div>
                        <span class="badge bg-secondary">Inactivo</span>
                    </div>
                    <div class="text-muted small mt-3">
                        Activa integraciones para automatizar procesos del sistema.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



