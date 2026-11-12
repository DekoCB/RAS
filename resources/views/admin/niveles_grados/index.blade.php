@extends('layouts.mantis')

@section('title', 'Niveles y grados | SuperAdmin')
@section('page-title', 'Niveles y grados')
@section('body-class', 'role-superadmin')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}?v={{ filemtime(public_path('css/superadmin-dashboard.css')) }}">
@endpush

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    @php
        $nivelesCollection = collect($niveles);
        $nivelesTabs = $nivelesCollection->keys()->mapWithKeys(function ($nivel) {
            $slug = \Illuminate\Support\Str::slug($nivel);
            return [$slug => $nivel];
        });
        $activeTab = request('tab');
        if (!$activeTab || !$nivelesTabs->has($activeTab)) {
            $activeTab = $nivelesTabs->keys()->first();
        }
    @endphp

    <div class="card jp-soft-card jp-role-hero mt-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="jp-role-pill">
                        <i data-feather="layers"></i>
                        Niveles
                    </div>
                    <h5 class="mb-1 jp-role-title">Gestion de niveles y grados</h5>
                    <p class="mb-0 text-muted jp-role-sub">
                        Organiza los niveles academicos y los grados disponibles.
                    </p>
                </div>
                <button class="btn jp-btn-search" data-bs-toggle="modal" data-bs-target="#modalGrado">
                    Nuevo grado
                </button>
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

    @if ($nivelesCollection->isEmpty())
        <div class="alert alert-info mt-3">No hay niveles ni grados registrados.</div>
    @else
        <div class="jp-product-tabs mt-3">
            @foreach ($nivelesTabs as $slug => $label)
                <a class="jp-product-tab {{ $activeTab === $slug ? 'is-active' : '' }}"
                    href="{{ route('admin-sistema.niveles-grados.index', ['tab' => $slug]) }}">
                    <i data-feather="layers"></i>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @php
            $nivelActual = $nivelesTabs[$activeTab] ?? $nivelesTabs->first();
            $grados = $nivelesCollection->get($nivelActual, collect());
        @endphp

        <div class="card jp-soft-card mt-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0 jp-section-title"><i data-feather="layers"></i> {{ $nivelActual }}</h5>
                <span class="badge bg-secondary">{{ $grados->count() }} grados</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table jp-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Grado</th>
                                <th>Secciones</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grados as $grado)
                                <tr>
                                    <td>{{ $grado->nombre }}</td>
                                    <td>{{ $grado->secciones_count }}</td>
                                    <td class="text-end">
                                        <button class="btn jp-table-action jp-table-action--edit me-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editarGrado{{ $grado->id }}">
                                            <i data-feather="edit-3"></i>
                                            Editar
                                        </button>
                                        <form method="POST"
                                            action="{{ route('admin-sistema.niveles-grados.destroy', $grado) }}"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn jp-table-action jp-table-action--delete"
                                                onclick="return confirm('Eliminar grado?')">
                                                <i data-feather="trash-2"></i>
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editarGrado{{ $grado->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-md modal-dialog-centered">
                                        <div class="modal-content jp-ano-modal">
                                            <div class="modal-header jp-ano-modal__header">
                                                <h5 class="modal-title">Editar grado</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin-sistema.niveles-grados.update', $grado) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body jp-ano-modal__body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nivel</label>
                                                        <input type="text" name="nivel" class="form-control" value="{{ $grado->nivel }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Grado</label>
                                                        <input type="text" name="nombre" class="form-control" value="{{ $grado->nombre }}" required>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="modalGrado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content jp-ano-modal">
                <div class="modal-header jp-ano-modal__header">
                    <h5 class="modal-title">Nuevo grado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin-sistema.niveles-grados.store') }}">
                    @csrf
                    <div class="modal-body jp-ano-modal__body">
                        <div class="mb-3">
                            <label class="form-label">Nivel</label>
                            <input type="text" name="nivel" class="form-control" list="nivelesList" required>
                            <datalist id="nivelesList">
                                @foreach ($nivelesDisponibles as $nivel)
                                    <option value="{{ $nivel }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Grado</label>
                            <input type="text" name="nombre" class="form-control" required>
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

