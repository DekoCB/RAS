@extends('layouts.mantis')

@section('title', 'Permisos | SuperAdmin')
@section('page-title', 'Permisos del sistema')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-count-card mt-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="jp-pill">Permisos</div>
                    <h5 class="mb-1">Gestion de permisos</h5>
                    <p class="mb-0 opacity-75">
                        Crea permisos y asigna accesos por rol en el sistema.
                    </p>
                </div>
                <button class="btn jp-btn-search" data-bs-toggle="modal" data-bs-target="#modalPermiso">
                    Nuevo permiso
                </button>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="row mt-3 g-3">
        <div class="col-xl-4">
            <div class="card jp-soft-card">
                <div class="card-header">
                    <h5 class="mb-0">Permisos disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="permListSearch" placeholder="Buscar permiso...">
                    </div>
                    <div class="list-group" id="permList">
                        @foreach ($permissions as $permiso)
                            @php
                                $activo = $permisoActual && $permisoActual->id === $permiso->id;
                            @endphp
                            <a href="{{ route('admin-sistema.permisos.index', ['permission_id' => $permiso->id]) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $activo ? 'active' : '' }}"
                                data-name="{{ $permiso->name }}">
                                <span>{{ $permiso->name }}</span>
                                <span class="badge bg-light text-dark">{{ $permiso->roles->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card jp-soft-card">
                <div class="card-body">
                    @if (!$permisoActual)
                        <div class="alert alert-info">
                            Selecciona un permiso para administrar sus roles.
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin-sistema.permisos.roles', $permisoActual) }}">
                            @csrf
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div>
                                    <h6 class="mb-1">Roles con acceso a: {{ $permisoActual->name }}</h6>
                                    <p class="text-muted mb-0">Marca los roles que deben tener este permiso.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllRoles">
                                        Marcar todo
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllRoles">
                                        Limpiar
                                    </button>
                                    <button type="submit" class="btn jp-btn-search btn-sm">
                                        Guardar roles
                                    </button>
                                </div>
                            </div>

                            <div class="row g-2">
                                @foreach ($roles as $rol)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-check">
                                            <input class="form-check-input role-check" type="checkbox"
                                                name="roles[]" value="{{ $rol->name }}" id="role-{{ $rol->id }}"
                                                @checked($rol->hasPermissionTo($permisoActual->name))>
                                            <label class="form-check-label" for="role-{{ $rol->id }}">
                                                {{ $rol->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPermiso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Nuevo permiso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin-sistema.permisos.store') }}">
                    @csrf
                    <div class="modal-body">
                        <label class="form-label">Nombre del permiso</label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: nota.registrar" required />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn jp-btn-search">Guardar permiso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const listSearch = document.getElementById('permListSearch');
                const listItems = document.querySelectorAll('#permList .list-group-item');
                const selectAll = document.getElementById('selectAllRoles');
                const clearAll = document.getElementById('clearAllRoles');

                if (listSearch) {
                    listSearch.addEventListener('input', function () {
                        const value = this.value.toLowerCase();
                        listItems.forEach(item => {
                            const name = item.dataset.name.toLowerCase();
                            item.style.display = name.includes(value) ? '' : 'none';
                        });
                    });
                }

                if (selectAll) {
                    selectAll.addEventListener('click', function () {
                        document.querySelectorAll('.role-check').forEach(cb => cb.checked = true);
                    });
                }

                if (clearAll) {
                    clearAll.addEventListener('click', function () {
                        document.querySelectorAll('.role-check').forEach(cb => cb.checked = false);
                    });
                }
            });
        </script>
    @endpush
@endsection

