@extends('layouts.mantis')

@section('title', 'Apoderados | Secretaria')
@section('page-title', 'Apoderados')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-2">
        <div>
            <h5 class="mb-1">Apoderados registrados</h5>
            <p class="text-muted mb-0">Listado de padres y tutores.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('secretaria.estudiantes.index') }}" class="btn btn-outline-secondary">
                <i data-feather="user-check"></i>
                Ver estudiantes
            </a>
            <a href="{{ route('secretaria.apoderados.create') }}" class="btn jp-btn-incidencia">
                <i data-feather="plus-circle"></i>
                Nuevo apoderado
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success jp-incidencia-alert mt-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="card jp-soft-card mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table jp-table jp-students-table mb-0">
                    <thead>
                        <tr>
                            <th>DNI</th>
                            <th>Apoderado</th>
                            <th>Tel&eacute;fono</th>
                            <th>Email</th>
                            <th>Estudiantes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($apoderados as $apoderado)
                            <tr>
                                <td>{{ $apoderado->dni ?? '-' }}</td>
                                <td>{{ $apoderado->apellidos }} {{ $apoderado->nombres }}</td>
                                <td>{{ $apoderado->telefono ?? '-' }}</td>
                                <td>{{ $apoderado->email ?? '-' }}</td>
                                <td>{{ $apoderado->estudiantes?->count() ?? 0 }}</td>
                                <td>
                                    <div class="jp-action-group">
                                        <a href="{{ route('secretaria.apoderados.edit', $apoderado) }}"
                                            class="jp-action-icon is-edit" title="Editar" aria-label="Editar">
                                            <i data-feather="edit-2"></i>
                                        </a>
                                        <form class="jp-action-form" method="POST"
                                            action="{{ route('secretaria.apoderados.destroy', $apoderado) }}"
                                            onsubmit="return confirm('Eliminar apoderado?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="jp-action-icon is-delete" title="Eliminar"
                                                aria-label="Eliminar">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay apoderados registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $apoderados->links() }}
        </div>
    </div>
@endsection
