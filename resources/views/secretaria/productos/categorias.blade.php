@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp
@section('title', 'Categorías | Secretaria')
@section('page-title', 'Categorías de Productos')
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
    <div class="jp-product-tabs">
        <a class="jp-product-tab" href="{{ route($rp . 'productos.index') }}">
            <i data-feather="briefcase"></i>
            Productos
        </a>
        <a class="jp-product-tab is-active" href="{{ route($rp . 'productos.categorias') }}">
            <i data-feather="grid"></i>
            Categor&iacute;as
        </a>
        <a class="jp-product-tab" href="{{ route($rp . 'productos.movimientos') }}">
            <i data-feather="repeat"></i>
            Movimientos
        </a>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card jp-product-filter">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">Categor&iacute;as</h5>
                        <p class="mb-0 text-muted">Administra las categor&iacute;as del inventario.</p>
                    </div>
                    <button class="btn jp-btn-incidencia" data-bs-toggle="modal" data-bs-target="#categoriaModal">
                        <i data-feather="plus"></i>
                        Nueva categor&iacute;a
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success jp-incidencia-alert mt-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="row mt-3 g-3">
        @forelse ($categorias as $categoria)
            <div class="col-md-6 col-xl-4">
                <div class="card jp-category-card">
                    <div class="card-body">
                        <div class="jp-category-title">
                            <span class="jp-category-icon">
                                <i data-feather="package"></i>
                            </span>
                            <div>
                                <h6 class="mb-1">{{ $categoria->nombre }}</h6>
                                <span class="text-muted">{{ $categoria->productos_count ?? 0 }} productos</span>
                            </div>
                        </div>
                        <div class="jp-category-stats">
                            <div>
                                <span>Stock total</span>
                                <strong>{{ (int) ($categoria->stock_total ?? 0) }}</strong>
                            </div>
                        </div>
                        <div class="jp-category-actions mt-3">
                            <button class="jp-btn-pill jp-btn-peach"
                                data-bs-toggle="modal"
                                data-bs-target="#categoriaModal"
                                data-categoria-id="{{ $categoria->id }}"
                                data-categoria-nombre="{{ $categoria->nombre }}"
                                data-categoria-activo="{{ $categoria->activo ? '1' : '0' }}">
                                <i data-feather="edit-2"></i>
                                Editar
                            </button>
                            <form method="POST" action="{{ route($rp . 'productos.categorias.destroy', $categoria) }}">
                                @csrf
                                @method('DELETE')
                                <button class="jp-btn-pill jp-btn-rose" type="submit"
                                    onclick="return confirm('¿Eliminar esta categoría?')">
                                    <i data-feather="trash-2"></i>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card jp-soft-card">
                    <div class="card-body text-center text-muted">
                        No hay categor&iacute;as registradas.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="modal fade jp-modal" id="categoriaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="grid"></i>
                        <span id="categoriaModalTitle">Nueva categor&iacute;a</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route($rp . 'productos.categorias.store') }}" id="categoriaForm">
                        @csrf
                        <input type="hidden" name="_method" value="POST" id="categoriaMethod">

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="categoriaNombre" class="form-control" required>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activo" id="categoriaActivo" checked>
                            <label class="form-check-label" for="categoriaActivo">Activo</label>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn jp-btn-incidencia w-100">
                                Guardar categor&iacute;a
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('categoriaModal');
                const form = document.getElementById('categoriaForm');
                const methodInput = document.getElementById('categoriaMethod');
                const nombreInput = document.getElementById('categoriaNombre');
                const activoInput = document.getElementById('categoriaActivo');
                const title = document.getElementById('categoriaModalTitle');

                modal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    const categoriaId = button?.getAttribute('data-categoria-id');

                    if (categoriaId) {
                        form.action = `{{ route($rp . 'productos.categorias.update', ':id') }}`.replace(':id', categoriaId);
                        methodInput.value = 'PATCH';
                        nombreInput.value = button.getAttribute('data-categoria-nombre') || '';
                        activoInput.checked = button.getAttribute('data-categoria-activo') === '1';
                        title.textContent = 'Editar categor\u00eda';
                    } else {
                        form.action = `{{ route($rp . 'productos.categorias.store') }}`;
                        methodInput.value = 'POST';
                        nombreInput.value = '';
                        activoInput.checked = true;
                        title.textContent = 'Nueva categor\u00eda';
                    }
                });
            });
        </script>
    @endpush
@endsection

