@extends('layouts.mantis')

@section('title', 'Agregar Producto | Secretaria')
@section('page-title', 'Agregar Producto')
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
    <div class="row">
        <div class="col-12">
            <div class="card jp-soft-card">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1">Agregar producto</h4>
                        <p class="mb-0 text-muted">Registra un nuevo producto del colegio.</p>
                    </div>
                    <a href="{{ route('secretaria.productos.index') }}" class="btn btn-outline-secondary">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card jp-soft-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('secretaria.productos.store') }}" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                Revisa los campos marcados.
                            </div>
                        @endif

                        @if ($categorias->isEmpty())
                            <div class="alert alert-warning">
                                Primero crea una categor&iacute;a para poder registrar productos.
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categor&iacute;a</label>
                                <select name="categoria_id" class="form-select" required>
                                    <option value="">Seleccione categor&iacute;a</option>
                                    @forelse ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" @selected((string) old('categoria_id') === (string) $categoria->id)>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No hay categor&iacute;as registradas</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock</label>
                                <input type="number" name="stock" class="form-control" min="0" required value="{{ old('stock', 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Precio (S/)</label>
                                <input type="number" name="precio" class="form-control" min="0" step="0.01" required value="{{ old('precio', 0) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripci&oacute;n</label>
                                <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Imagen del producto</label>
                                <input type="file" name="imagen" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Destacado</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="destacado" id="destacadoSwitch" @checked(old('destacado'))>
                                    <label class="form-check-label" for="destacadoSwitch">Si</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Activo</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="activo" id="activoSwitch" checked>
                                    <label class="form-check-label" for="activoSwitch">Si</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('secretaria.productos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn jp-btn-incidencia" @disabled($categorias->isEmpty())>Guardar producto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
