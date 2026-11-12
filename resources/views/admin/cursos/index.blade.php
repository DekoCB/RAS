@extends('layouts.mantis')

@section('title', 'Cursos | SuperAdmin')
@section('page-title', 'Cursos')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="card jp-soft-card jp-role-hero mt-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="jp-role-pill">
                        <i data-feather="book-open"></i>
                        Cursos
                    </div>
                    <h5 class="mb-1 jp-role-title">Gestion de cursos</h5>
                    <p class="mb-0 text-muted jp-role-sub">
                        Administra el catalogo de cursos para todos los niveles.
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if (session('matricula.return_to'))
                        <a class="btn btn-outline-secondary" href="{{ session('matricula.return_to') }}">
                            {{ session('matricula.return_label', 'Volver a matrícula') }}
                        </a>
                    @endif
                    <button class="btn jp-btn-search" data-bs-toggle="modal" data-bs-target="#modalCurso">
                        Nuevo curso
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="card jp-soft-card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table jp-table jp-usuarios-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Codigo</th>
                            <th>Secciones</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cursos as $curso)
                            <tr>
                                <td>{{ $curso->nombre }}</td>
                                <td>{{ $curso->codigo ?? '-' }}</td>
                                <td>{{ $curso->secciones_count }}</td>
                                <td class="text-end">
                                    <button class="btn jp-table-action jp-table-action--edit me-2" data-bs-toggle="modal"
                                        data-bs-target="#editarCurso{{ $curso->id }}">
                                        <i data-feather="edit-3"></i>
                                        Editar
                                    </button>
                                    <form method="POST" action="{{ route('admin-sistema.cursos.destroy', $curso) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn jp-table-action jp-table-action--delete"
                                            onclick="return confirm('Eliminar curso?')">
                                            <i data-feather="trash-2"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="editarCurso{{ $curso->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-md modal-dialog-centered">
                                    <div class="modal-content jp-ano-modal">
                                        <div class="modal-header jp-ano-modal__header">
                                            <h5 class="modal-title">Editar curso</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin-sistema.cursos.update', $curso) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body jp-ano-modal__body">
                                                <div class="mb-3">
                                                    <label class="form-label">Curso</label>
                                                    <input type="text" name="nombre" class="form-control"
                                                        value="{{ $curso->nombre }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Codigo</label>
                                                    <input type="text" name="codigo" class="form-control"
                                                        value="{{ $curso->codigo }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer jp-ano-modal__footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                    Cancelar
                                                </button>
                                                <button type="submit" class="btn jp-btn-search">Guardar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay cursos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCurso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content jp-ano-modal">
                <div class="modal-header jp-ano-modal__header">
                    <h5 class="modal-title">Nuevo curso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin-sistema.cursos.store') }}">
                    @csrf
                    <div class="modal-body jp-ano-modal__body">
                        <div class="mb-3">
                            <label class="form-label">Curso</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Codigo</label>
                            <input type="text" name="codigo" class="form-control"
                                value="{{ old('codigo', $codigoSugerido ?? 'CUR-000001') }}">
                        </div>
                    </div>
                    <div class="modal-footer jp-ano-modal__footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn jp-btn-search">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

