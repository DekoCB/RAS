@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp
@section('title', 'Productos | Secretaria')
@section('page-title', 'Productos')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @endif
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <style>
        /* ── Modal Registrar Venta ─────────────────────────────────────── */
        #productoVentaModal .modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(16, 24, 73, 0.28);
        }
        #productoVentaModal .modal-header {
            background: linear-gradient(120deg, #131c58 0%, #192573 100%);
            padding: 18px 24px;
            border: none;
        }
        #productoVentaModal .modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: .3px;
        }
        #productoVentaModal .modal-body {
            padding: 0;
            max-height: calc(100vh - 180px);
            overflow-y: auto;
        }

        /* Sección interna */
        .vv-section {
            padding: 18px 24px;
        }
        .vv-section + .vv-section {
            border-top: 1px solid #eaedf0;
        }
        .vv-section-title {
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #192573;
            margin-bottom: 12px;
        }

        /* Tarjeta producto */
        .vv-producto-card {
            background: linear-gradient(135deg, #f7faff 0%, #fff 100%);
            border: 1px solid #dae5f5;
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .vv-producto-icon {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, #192573, #1d2a81);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: #fff;
        }
        .vv-producto-info { flex: 1; min-width: 0; }
        .vv-producto-nombre {
            font-weight: 700; font-size: .93rem;
            color: #1a1a1a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .vv-producto-precio {
            font-size: .82rem; color: #555;
            margin-top: 2px;
        }
        .vv-producto-stock {
            background: #f3f8fe;
            border: 1px solid #c6daf5;
            border-radius: 8px;
            padding: 6px 12px;
            text-align: center;
            flex-shrink: 0;
        }
        .vv-producto-stock .stock-num {
            font-size: 1.2rem; font-weight: 800; color: #192573; line-height:1;
        }
        .vv-producto-stock .stock-lbl {
            font-size: .65rem; color: #999; text-transform: uppercase; letter-spacing:.5px;
        }

        /* Cantidad + total */
        .vv-cant-total {
            display: flex; gap: 12px; margin-top: 14px;
        }
        .vv-cant-total .vv-field { flex: 1; }
        .vv-cant-total label { font-size: .75rem; font-weight: 700; color: #555; margin-bottom: 4px; display: block; }
        .vv-cant-total input {
            border-radius: 10px; border: 1.5px solid #d5dde8;
            padding: 8px 12px; font-size: .95rem; font-weight: 600;
            transition: border .2s;
        }
        .vv-cant-total input:focus { border-color: #192573; box-shadow: 0 0 0 3px rgba(25, 37, 115,.1); outline: none; }
        .vv-total-display {
            background: linear-gradient(135deg, #192573, #1d2a81);
            color: #fff; border-radius: 10px;
            padding: 8px 14px; font-size: 1.1rem; font-weight: 800;
            display: flex; align-items: center; gap: 6px;
        }

        /* Comprador */
        .vv-comprador-tipo { display: flex; gap: 8px; margin-bottom: 12px; }
        .vv-tipo-btn {
            flex: 1; padding: 8px; border-radius: 10px; border: 1.5px solid #d0d7e0;
            background: #fff; cursor: pointer; font-size: .82rem; font-weight: 600;
            color: #666; text-align: center; transition: all .2s;
        }
        .vv-tipo-btn.is-active {
            background: #192573; color: #fff; border-color: #192573;
        }
        .vv-dni-search { display: flex; gap: 8px; }
        .vv-dni-search input {
            border-radius: 10px; border: 1.5px solid #d5dde8;
            padding: 9px 14px; font-size: .93rem; flex: 1;
            transition: border .2s;
        }
        .vv-dni-search input:focus { border-color: #192573; box-shadow: 0 0 0 3px rgba(25, 37, 115,.1); outline: none; }
        .vv-dni-search .btn-buscar {
            background: #192573; color: #fff; border: none;
            border-radius: 10px; padding: 9px 16px; font-weight: 600; font-size: .82rem;
            display: flex; align-items: center; gap: 5px;
            transition: background .2s;
        }
        .vv-dni-search .btn-buscar:hover { background: #162064; }
        .vv-alumno-result {
            background: #f0fdf4; border: 1.5px solid #86efac;
            border-radius: 10px; padding: 10px 14px;
            display: flex; align-items: center; gap: 10px;
            margin-top: 8px;
        }
        .vv-alumno-result .alumno-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0;
        }
        .vv-alumno-result .alumno-nombre { font-weight: 700; font-size: .88rem; color: #14532d; flex: 1; }
        .vv-alumno-result .alumno-dni { font-size: .75rem; color: #166534; }
        .vv-alumno-result .btn-clear {
            background: none; border: none; color: #1f2e8f; cursor: pointer; padding: 2px; line-height: 1;
        }
        .vv-error-msg { color: #dc2626; font-size: .78rem; margin-top: 6px; display: flex; align-items: center; gap: 4px; }

        /* Pagos */
        .vv-pago-row {
            background: #fafafa; border: 1.5px solid #e5e9ef;
            border-radius: 12px; padding: 12px 14px; margin-bottom: 10px;
            transition: border .2s;
        }
        .vv-pago-row:hover { border-color: #a0b6d4; }
        .vv-pago-row .row { align-items: flex-end; }
        .vv-pago-label { font-size: .72rem; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 5px; }
        .vv-pago-row select,
        .vv-pago-row input[type="number"],
        .vv-pago-row input[type="text"] {
            border-radius: 9px; border: 1.5px solid #d0d7e0;
            padding: 8px 11px; font-size: .88rem; width: 100%;
            transition: border .2s;
        }
        .vv-pago-row select:focus,
        .vv-pago-row input:focus { border-color: #192573; box-shadow: 0 0 0 3px rgba(25, 37, 115,.08); outline: none; }
        .vv-pago-metodo-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: .72rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; margin-top: 4px;
        }
        .badge-efectivo { background: #dcfce7; color: #166534; }
        .badge-yape     { background: #ede9fe; color: #5b21b6; }
        .badge-plin     { background: #e0f2fe; color: #192572; }
        .badge-transf   { background: #c3f0fe; color: #141d5a; }
        .vv-btn-remove {
            background: #e2eefe; border: none; color: #1f2e8f; border-radius: 8px;
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background .2s; flex-shrink: 0;
        }
        .vv-btn-remove:hover { background: #c8cffc; }
        .vv-operacion-wrap { margin-top: 10px; }

        /* Saldo */
        .vv-saldo-bar {
            background: #f0f4f9; border-radius: 10px; padding: 10px 14px;
            display: flex; align-items: center; gap: 10px; margin-top: 4px;
        }
        .vv-saldo-bar .saldo-label { font-size: .75rem; color: #888; font-weight: 600; }
        .vv-saldo-bar .saldo-valor { font-size: .88rem; font-weight: 800; color: #1a1a1a; }
        .vv-saldo-bar .saldo-falta { font-size: .75rem; font-weight: 700; color: #1f2e8f; margin-left: auto; }
        .vv-saldo-bar.is-completo { background: #f0fdf4; }
        .vv-saldo-bar.is-completo .saldo-valor { color: #16a34a; }

        /* Footer confirm */
        .vv-footer {
            padding: 16px 24px;
            border-top: 1px solid #eaedf0;
            background: #fff;
        }
        .vv-btn-confirm {
            background: linear-gradient(120deg, #151f5e, #192573);
            color: #fff; border: none; border-radius: 12px;
            width: 100%; padding: 13px; font-size: 1rem; font-weight: 700;
            letter-spacing: .3px; display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 6px 20px rgba(20, 30, 93, 0.3);
            transition: all .2s;
        }
        .vv-btn-confirm:hover { background: linear-gradient(120deg, #131c56, #172269); transform: translateY(-1px); }
        .vv-btn-confirm:active { transform: translateY(0); }

        /* Observación */
        .vv-obs-input {
            border-radius: 10px; border: 1.5px solid #d5dde8;
            padding: 9px 14px; width: 100%; font-size: .88rem;
            transition: border .2s;
        }
        .vv-obs-input:focus { border-color: #192573; box-shadow: 0 0 0 3px rgba(25, 37, 115,.1); outline: none; }

        .vv-btn-add-metodo {
            background: #f0f6ff; border: 1.5px dashed #babfe0; color: #192573;
            border-radius: 10px; padding: 8px 14px; font-size: .8rem; font-weight: 700;
            display: flex; align-items: center; gap: 5px; cursor: pointer; transition: all .2s;
        }
        .vv-btn-add-metodo:hover { background: #e0edff; border-color: #1d2a81; }
    </style>
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
        <a class="jp-product-tab is-active" href="{{ route($rp . 'productos.index') }}">
            <i data-feather="briefcase"></i>
            Productos
        </a>
        <a class="jp-product-tab" href="{{ route($rp . 'productos.categorias') }}">
            <i data-feather="grid"></i>
            Categor&iacute;as
        </a>
        <a class="jp-product-tab" href="{{ route($rp . 'productos.movimientos') }}">
            <i data-feather="repeat"></i>
            Movimientos
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success jp-incidencia-alert mt-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card jp-product-filter mt-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div class="jp-product-filter-label">Filtrar por:</div>
            <div class="jp-product-chip-group">
                <a class="jp-product-chip {{ request('categoria') ? '' : 'is-active' }}"
                    href="{{ route($rp . 'productos.index') }}">
                    Todas
                </a>
                @foreach ($categorias as $categoria)
                    <a class="jp-product-chip {{ (string) request('categoria') === (string) $categoria->id ? 'is-active' : '' }}"
                        href="{{ route($rp . 'productos.index', ['categoria' => $categoria->id]) }}">
                        {{ $categoria->nombre === 'Utiles escolares' ? 'Útiles Escolares' : $categoria->nombre }}
                    </a>
                @endforeach
            </div>
            <div class="ms-auto d-flex align-items-center gap-2 jp-product-actions">
                <form method="GET" action="{{ route($rp . 'productos.index') }}" class="jp-product-search">
                    <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                    <input type="text" name="q" class="form-control" placeholder="Buscar producto..."
                        value="{{ request('q') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i data-feather="search"></i>
                    </button>
                </form>
                <button type="button" class="btn jp-btn-incidencia" data-bs-toggle="modal" data-bs-target="#productoModal">
                    <i data-feather="plus"></i>
                    Agregar Producto
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        @forelse ($productos as $producto)
            @php
                $precio = number_format((float) $producto->precio, 2);
                $stock = (int) $producto->stock;
                $descripcion = $producto->descripcion ?: 'Producto del colegio disponible en almac&eacute;n.';
                $tag = $producto->categoria->nombre ?? $producto->tipo ?? 'Producto';
                $rawImagen = $producto->imagen_url;
                $imagenUrl = null;
                if ($rawImagen) {
                    if (\Illuminate\Support\Str::startsWith($rawImagen, ['http://', 'https://'])) {
                        $imagenUrl = $rawImagen;
                    } else {
                        $imagenUrl = url(ltrim($rawImagen, '/'));
                    }
                }
            @endphp
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="jp-product-card">
                    <div class="jp-product-thumb {{ $imagenUrl ? 'has-image' : '' }}"
                        @if ($imagenUrl) style="background-image: url('{{ $imagenUrl }}')" @endif>
                        @if (!$imagenUrl)
                            <div class="jp-product-placeholder">
                                {{ strtoupper(substr($producto->nombre, 0, 1)) }}
                            </div>
                        @endif
                        @if ($producto->destacado)
                            <span class="jp-product-badge">
                                <i data-feather="star"></i>
                                Destacado
                            </span>
                        @endif
                    </div>
                    <div class="jp-product-body">
                        <span class="jp-product-tag">{{ $tag }}</span>
                        <h6>{{ $producto->nombre }}</h6>
                        <p>{{ $descripcion }}</p>
                        <div class="jp-product-price">S/ {{ $precio }}</div>
                        <div class="jp-product-stock">
                            <i data-feather="box"></i>
                            Stock: {{ $stock }} unidades
                        </div>
                        <div class="jp-product-actions-row">
                            <button class="jp-product-action is-sell jp-product-sell" type="button"
                                data-bs-toggle="modal" data-bs-target="#productoVentaModal"
                                data-id="{{ $producto->id }}"
                                data-nombre="{{ e($producto->nombre) }}"
                                data-precio="{{ $producto->precio }}"
                                data-stock="{{ $producto->stock }}">
                                <i data-feather="shopping-cart"></i>
                                Vender
                            </button>
                            @role('SuperAdmin')
                            <button class="jp-product-action is-edit jp-product-edit" type="button"
                                data-bs-toggle="modal" data-bs-target="#productoEditModal"
                                data-id="{{ $producto->id }}"
                                data-nombre="{{ e($producto->nombre) }}"
                                data-descripcion="{{ e($producto->descripcion) }}"
                                data-precio="{{ $producto->precio }}"
                                data-stock="{{ $producto->stock }}"
                                data-categoria="{{ $producto->categoria_id }}"
                                data-destacado="{{ $producto->destacado ? 1 : 0 }}"
                                data-activo="{{ $producto->activo ? 1 : 0 }}">
                                <i data-feather="edit-2"></i>
                                Editar
                            </button>
                            @endrole
                            <button class="jp-product-action is-delete jp-product-delete" type="button"
                                data-bs-toggle="modal" data-bs-target="#productoDeleteModal"
                                data-id="{{ $producto->id }}"
                                data-nombre="{{ e($producto->nombre) }}">
                                <i data-feather="trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card jp-soft-card">
                    <div class="card-body text-center text-muted">
                        A&uacute;n no hay productos registrados.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $productos->links() }}
    </div>

    <div class="modal fade jp-product-modal" id="productoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="shopping-bag"></i>
                        Agregar Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route($rp . 'productos.store') }}" enctype="multipart/form-data">
                        @csrf

                        @if ($categorias->isEmpty())
                            <div class="alert alert-warning">
                                Primero crea una categor&iacute;a para poder registrar productos.
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Nombre del Producto *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripci&oacute;n</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categor&iacute;a *</label>
                            <select name="categoria_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                @forelse ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @empty
                                    <option value="" disabled>No hay categor&iacute;as registradas</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Precio (S/) *</label>
                                <input type="number" name="precio" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock (unidades) *</label>
                                <input type="number" name="stock" class="form-control" min="0" required>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="destacado" id="productoDestacado">
                            <label class="form-check-label" for="productoDestacado">
                                Marcar como destacado
                            </label>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Imagen del Producto</label>
                            <div class="jp-upload-box">
                                <input type="file" name="imagen" id="productoImagen" accept="image/*">
                                <div class="jp-upload-content">
                                    <i data-feather="upload-cloud"></i>
                                    <span id="productoImagenTexto">Haz clic para seleccionar una imagen</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn jp-btn-incidencia w-100" @disabled($categorias->isEmpty())>
                                <i data-feather="save"></i>
                                Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade jp-product-modal" id="productoEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="edit-3"></i>
                        Editar Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="#" data-action-template="{{ route($rp . 'productos.update', ['producto' => '__ID__']) }}"
                        enctype="multipart/form-data" id="productoEditForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nombre del Producto *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripci&oacute;n</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categor&iacute;a *</label>
                            <select name="categoria_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Precio (S/) *</label>
                                <input type="number" name="precio" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock (unidades) *</label>
                                <input type="number" name="stock" class="form-control" min="0" required>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="destacado" id="productoEditDestacado">
                            <label class="form-check-label" for="productoEditDestacado">
                                Marcar como destacado
                            </label>
                        </div>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="activo" id="productoEditActivo">
                            <label class="form-check-label" for="productoEditActivo">
                                Producto activo
                            </label>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Actualizar Imagen</label>
                            <div class="jp-upload-box">
                                <input type="file" name="imagen" id="productoEditImagen" accept="image/*">
                                <div class="jp-upload-content">
                                    <i data-feather="upload-cloud"></i>
                                    <span id="productoEditImagenTexto">Haz clic para seleccionar una imagen</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn jp-btn-incidencia w-100">
                                <i data-feather="save"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade jp-product-modal" id="productoDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="trash-2"></i>
                        Eliminar Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        &iquest;Seguro que deseas eliminar <strong id="productoDeleteNombre"></strong>?
                    </p>
                    <form method="POST" action="#" data-action-template="{{ route($rp . 'productos.destroy', ['producto' => '__ID__']) }}"
                        id="productoDeleteForm">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger w-50">
                                Eliminar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade jp-product-modal" id="productoVentaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i data-feather="shopping-cart"></i>
                        Registrar Venta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form method="POST" action="{{ route($rp . 'productos.ventas.store') }}" id="productoVentaForm">
                    @csrf
                    <input type="hidden" name="producto_id" id="ventaProductoId">

                    <div class="modal-body">

                        {{-- ① Producto --}}
                        <div class="vv-section">
                            <div class="vv-section-title">Producto</div>
                            <div class="vv-producto-card">
                                <div class="vv-producto-icon">
                                    <i data-feather="tag" style="width:22px;height:22px"></i>
                                </div>
                                <div class="vv-producto-info">
                                    <div class="vv-producto-nombre" id="ventaProductoNombre">—</div>
                                    <div class="vv-producto-precio">
                                        Precio unit.: <strong id="ventaProductoPrecio">—</strong>
                                    </div>
                                </div>
                                <div class="vv-producto-stock">
                                    <div class="stock-num" id="ventaProductoStock">—</div>
                                    <div class="stock-lbl">en stock</div>
                                </div>
                            </div>

                            <div class="vv-cant-total mt-3">
                                <div class="vv-field">
                                    <label>Cantidad *</label>
                                    <input type="number" name="cantidad" id="ventaCantidad"
                                        class="form-control" min="1" value="1" required>
                                </div>
                                <div class="vv-field">
                                    <label>Total a pagar</label>
                                    <div class="vv-total-display" id="ventaProductoTotal">S/ 0.00</div>
                                </div>
                            </div>
                        </div>

                        {{-- ② Comprador --}}
                        <div class="vv-section">
                            <div class="vv-section-title">Comprador</div>

                            <div class="vv-comprador-tipo">
                                <button type="button" class="vv-tipo-btn is-active" data-tipo="alumno" id="ventaTipoBtnAlumno">
                                    <i data-feather="user" style="width:13px;height:13px"></i>
                                    Alumno
                                </button>
                                <button type="button" class="vv-tipo-btn" data-tipo="externo" id="ventaTipoBtnExterno">
                                    <i data-feather="users" style="width:13px;height:13px"></i>
                                    Externo
                                </button>
                            </div>
                            <input type="hidden" name="tipo_comprador" id="ventaTipoComprador" value="alumno">

                            {{-- Alumno --}}
                            <div id="ventaAlumnoGroup">
                                <label class="vv-pago-label mb-2">Buscar por DNI</label>
                                <div class="vv-dni-search">
                                    <input type="text" id="ventaDniBuscar"
                                        placeholder="Ingresa el DNI del alumno..."
                                        maxlength="8" inputmode="numeric">
                                    <button type="button" class="btn-buscar" id="ventaBuscarDniBtn">
                                        <i data-feather="search" style="width:14px;height:14px"></i>
                                        Buscar
                                    </button>
                                </div>
                                <div id="ventaAlumnoResult" class="vv-alumno-result d-none">
                                    <div class="alumno-avatar">
                                        <i data-feather="check" style="width:14px;height:14px"></i>
                                    </div>
                                    <div>
                                        <div class="alumno-nombre" id="ventaAlumnoNombreTexto"></div>
                                        <div class="alumno-dni" id="ventaAlumnoDniTexto"></div>
                                    </div>
                                    <button type="button" class="btn-clear" id="ventaAlumnoClear" title="Limpiar">
                                        <i data-feather="x" style="width:16px;height:16px"></i>
                                    </button>
                                </div>
                                <div id="ventaAlumnoError" class="vv-error-msg d-none">
                                    <i data-feather="alert-circle" style="width:13px;height:13px"></i>
                                    <span id="ventaAlumnoErrorTexto"></span>
                                </div>
                                <input type="hidden" name="estudiante_id" id="ventaEstudianteId">
                            </div>

                            {{-- Externo --}}
                            <div id="ventaExternoGroup" class="d-none">
                                <div class="mb-2">
                                    <label class="vv-pago-label mb-1">Nombre *</label>
                                    <input type="text" name="comprador_nombre" id="ventaExternoNombre"
                                        class="vv-obs-input" placeholder="Nombre completo del comprador">
                                </div>
                                <div>
                                    <label class="vv-pago-label mb-1">Documento (opcional)</label>
                                    <input type="text" name="comprador_documento" id="ventaExternoDocumento"
                                        class="vv-obs-input" placeholder="DNI u otro documento">
                                </div>
                            </div>
                        </div>

                        {{-- ③ Pagos --}}
                        <div class="vv-section">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="vv-section-title mb-0">M&eacute;todos de pago</div>
                                <button type="button" class="vv-btn-add-metodo" id="ventaAgregarPago">
                                    <i data-feather="plus" style="width:13px;height:13px"></i>
                                    Agregar m&eacute;todo
                                </button>
                            </div>

                            <div id="ventaPagosContainer"></div>

                            <div id="ventaPagosSaldo" class="vv-saldo-bar d-none">
                                <span class="saldo-label">Asignado:</span>
                                <span class="saldo-valor" id="ventaPagosSaldoTexto">S/ 0.00</span>
                                <span class="saldo-falta d-none" id="ventaPagosSaldoAlerta"></span>
                            </div>
                        </div>

                        {{-- ④ Observación --}}
                        <div class="vv-section">
                            <label class="vv-pago-label mb-2">Observaci&oacute;n (opcional)</label>
                            <input type="text" name="observacion" class="vv-obs-input"
                                placeholder="Alguna nota sobre esta venta...">
                        </div>

                    </div>{{-- /modal-body --}}

                    <div class="vv-footer">
                        <button type="submit" class="vv-btn-confirm" id="ventaSubmitBtn">
                            <i data-feather="check-circle" style="width:18px;height:18px"></i>
                            Confirmar Venta
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('productoImagen');
            const label = document.getElementById('productoImagenTexto');
            if (!input || !label) return;

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                label.textContent = file ? file.name : 'Haz clic para seleccionar una imagen';
            });

            const editModal = document.getElementById('productoEditModal');
            const editForm = document.getElementById('productoEditForm');
            const editImageInput = document.getElementById('productoEditImagen');
            const editImageLabel = document.getElementById('productoEditImagenTexto');

            if (editModal && editForm) {
                editModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const id = button.getAttribute('data-id');
                    const nombre = button.getAttribute('data-nombre') || '';
                    const descripcion = button.getAttribute('data-descripcion') || '';
                    const precio = button.getAttribute('data-precio') || '';
                    const stock = button.getAttribute('data-stock') || '';
                    const categoria = button.getAttribute('data-categoria') || '';
                    const destacado = button.getAttribute('data-destacado') === '1';
                    const activo = button.getAttribute('data-activo') === '1';

                    editForm.action = editForm.dataset.actionTemplate.replace('__ID__', id);
                    editForm.querySelector('input[name="nombre"]').value = nombre;
                    editForm.querySelector('textarea[name="descripcion"]').value = descripcion;
                    editForm.querySelector('input[name="precio"]').value = precio;
                    editForm.querySelector('input[name="stock"]').value = stock;
                    editForm.querySelector('select[name="categoria_id"]').value = categoria;
                    editForm.querySelector('input[name="destacado"]').checked = destacado;
                    editForm.querySelector('input[name="activo"]').checked = activo;

                    if (editImageInput && editImageLabel) {
                        editImageInput.value = '';
                        editImageLabel.textContent = 'Haz clic para seleccionar una imagen';
                    }
                });
            }

            if (editImageInput && editImageLabel) {
                editImageInput.addEventListener('change', () => {
                    const file = editImageInput.files && editImageInput.files[0];
                    editImageLabel.textContent = file ? file.name : 'Haz clic para seleccionar una imagen';
                });
            }

            const deleteModal = document.getElementById('productoDeleteModal');
            const deleteForm = document.getElementById('productoDeleteForm');
            const deleteNombre = document.getElementById('productoDeleteNombre');

            if (deleteModal && deleteForm && deleteNombre) {
                deleteModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const id = button.getAttribute('data-id');
                    const nombre = button.getAttribute('data-nombre') || 'este producto';

                    deleteForm.action = deleteForm.dataset.actionTemplate.replace('__ID__', id);
                    deleteNombre.textContent = nombre;
                });
            }

            // ── MODAL VENTA ──────────────────────────────────────────────────
            const ventaModal      = document.getElementById('productoVentaModal');
            const ventaForm       = document.getElementById('productoVentaForm');
            const ventaProductoId = document.getElementById('ventaProductoId');
            const ventaProductoNombre = document.getElementById('ventaProductoNombre');
            const ventaProductoPrecio = document.getElementById('ventaProductoPrecio');
            const ventaProductoStock  = document.getElementById('ventaProductoStock');
            const ventaProductoTotal  = document.getElementById('ventaProductoTotal');
            const ventaCantidad       = document.getElementById('ventaCantidad');
            const ventaTipoComprador  = document.getElementById('ventaTipoComprador');
            const ventaAlumnoGroup    = document.getElementById('ventaAlumnoGroup');
            const ventaExternoGroup   = document.getElementById('ventaExternoGroup');
            const ventaDniBuscar      = document.getElementById('ventaDniBuscar');
            const ventaBuscarDniBtn   = document.getElementById('ventaBuscarDniBtn');
            const ventaAlumnoResult   = document.getElementById('ventaAlumnoResult');
            const ventaAlumnoNombreTexto = document.getElementById('ventaAlumnoNombreTexto');
            const ventaAlumnoDniTexto    = document.getElementById('ventaAlumnoDniTexto');
            const ventaAlumnoClear    = document.getElementById('ventaAlumnoClear');
            const ventaAlumnoError    = document.getElementById('ventaAlumnoError');
            const ventaAlumnoErrorTexto = document.getElementById('ventaAlumnoErrorTexto');
            const ventaEstudianteId   = document.getElementById('ventaEstudianteId');
            const ventaExternoNombre  = document.getElementById('ventaExternoNombre');
            const ventaPagosContainer = document.getElementById('ventaPagosContainer');
            const ventaPagosSaldo     = document.getElementById('ventaPagosSaldo');
            const ventaPagosSaldoTexto = document.getElementById('ventaPagosSaldoTexto');
            const ventaPagosSaldoAlerta = document.getElementById('ventaPagosSaldoAlerta');

            const METODOS_CON_OPERACION = ['Yape', 'Plin', 'Transferencia'];
            const METODO_COLORES = { Efectivo: 'badge-efectivo', Yape: 'badge-yape', Plin: 'badge-plin', Transferencia: 'badge-transf' };
            let precioUnitario = 0;
            let pagoIdx = 0;

            const getTotal = () => {
                const cant = parseInt(ventaCantidad?.value || '1', 10);
                return isNaN(cant) || cant < 1 ? 0 : cant * precioUnitario;
            };

            const actualizarTotal = () => {
                const total = getTotal();
                if (ventaProductoTotal) ventaProductoTotal.textContent = `S/ ${total.toFixed(2)}`;
                actualizarSaldo();
            };

            const actualizarSaldo = () => {
                if (!ventaPagosContainer) return;
                const total    = getTotal();
                const montos   = [...ventaPagosContainer.querySelectorAll('.venta-pago-monto')];
                const asignado = montos.reduce((s, el) => s + (parseFloat(el.value) || 0), 0);

                if (ventaPagosSaldo && montos.length > 0) {
                    ventaPagosSaldo.classList.remove('d-none');
                    ventaPagosSaldoTexto.textContent = `S/ ${asignado.toFixed(2)}`;
                    const completo = asignado >= total - 0.009;
                    ventaPagosSaldo.classList.toggle('is-completo', completo);
                    if (!completo) {
                        ventaPagosSaldoAlerta.classList.remove('d-none');
                        ventaPagosSaldoAlerta.textContent = `Falta S/ ${(total - asignado).toFixed(2)}`;
                    } else {
                        ventaPagosSaldoAlerta.classList.add('d-none');
                    }
                }
            };

            const crearFilaPago = (defaultMonto = '') => {
                const idx = pagoIdx++;
                const div = document.createElement('div');
                div.className = 'vv-pago-row';
                div.dataset.idx = idx;
                div.innerHTML = `
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                        <div style="flex:1.4;min-width:130px">
                            <div class="vv-pago-label">Método de pago</div>
                            <select class="venta-pago-metodo" name="pagos[${idx}][metodo]" required>
                                <option value="">Seleccionar...</option>
                                <option value="Efectivo">💵 Efectivo</option>
                                <option value="Yape">💜 Yape</option>
                                <option value="Transferencia">🏦 Transferencia</option>
                                <option value="Plin">🔵 Plin</option>
                            </select>
                        </div>
                        <div style="flex:1;min-width:100px">
                            <div class="vv-pago-label">Monto (S/)</div>
                            <input type="number" step="0.01" min="0.01"
                                class="venta-pago-monto"
                                name="pagos[${idx}][monto]"
                                placeholder="0.00"
                                value="${defaultMonto}"
                                required>
                        </div>
                        <button type="button" class="vv-btn-remove" title="Eliminar" style="display:none;margin-bottom:2px">
                            <i data-feather="trash-2" style="width:14px;height:14px"></i>
                        </button>
                    </div>
                    <div class="vv-operacion-wrap d-none" style="margin-top:10px">
                        <div class="vv-pago-label">N° de operaci&oacute;n *</div>
                        <input type="text" class="venta-pago-operacion"
                            name="pagos[${idx}][numero_operacion]"
                            placeholder="Ej: 0012345678">
                    </div>`;

                const metodoSel  = div.querySelector('.venta-pago-metodo');
                const operWrap   = div.querySelector('.vv-operacion-wrap');
                const operInput  = div.querySelector('.venta-pago-operacion');
                const montoInput = div.querySelector('.venta-pago-monto');
                const removeBtn  = div.querySelector('.vv-btn-remove');

                metodoSel.addEventListener('change', () => {
                    const m = metodoSel.value;
                    const necesitaOp = METODOS_CON_OPERACION.includes(m);
                    operWrap.classList.toggle('d-none', !necesitaOp);
                    operInput.required = necesitaOp;
                    if (!necesitaOp) operInput.value = '';
                });

                montoInput.addEventListener('input', actualizarSaldo);

                removeBtn.addEventListener('click', () => {
                    div.remove();
                    actualizarBotonesEliminar();
                    actualizarSaldo();
                    if (typeof feather !== 'undefined') feather.replace();
                });

                ventaPagosContainer.appendChild(div);
                actualizarBotonesEliminar();
                if (typeof feather !== 'undefined') feather.replace();
            };

            const actualizarBotonesEliminar = () => {
                const filas = ventaPagosContainer?.querySelectorAll('.vv-pago-row') ?? [];
                filas.forEach(fila => {
                    const btn = fila.querySelector('.vv-btn-remove');
                    if (btn) btn.style.display = filas.length > 1 ? 'flex' : 'none';
                });
            };

            const resetVentaModal = () => {
                pagoIdx = 0;
                if (ventaPagosContainer) ventaPagosContainer.innerHTML = '';
                crearFilaPago('');
                if (ventaPagosSaldo) ventaPagosSaldo.classList.add('d-none');
            };

            // Buscar alumno por DNI
            const buscarAlumnoPorDni = async () => {
                const dni = ventaDniBuscar?.value.trim();
                if (!dni) return;

                ventaBuscarDniBtn.disabled = true;
                ventaAlumnoError?.classList.add('d-none');
                ventaAlumnoResult?.classList.add('d-none');
                if (ventaEstudianteId) ventaEstudianteId.value = '';

                try {
                    const url = `{{ route($rp . 'productos.buscar-alumno') }}?dni=${encodeURIComponent(dni)}`;
                    const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const json = await res.json();

                    if (json.found) {
                        ventaEstudianteId.value = json.id;
                        ventaAlumnoNombreTexto.textContent = json.nombre;
                        ventaAlumnoDniTexto.textContent    = `DNI: ${json.dni}`;
                        ventaAlumnoResult.classList.remove('d-none');
                        if (typeof feather !== 'undefined') feather.replace();
                    } else {
                        ventaAlumnoErrorTexto.textContent = json.message || 'Alumno no encontrado.';
                        ventaAlumnoError.classList.remove('d-none');
                    }
                } catch {
                    ventaAlumnoErrorTexto.textContent = 'Error al buscar. Intente nuevamente.';
                    ventaAlumnoError.classList.remove('d-none');
                } finally {
                    ventaBuscarDniBtn.disabled = false;
                }
            };

            ventaBuscarDniBtn?.addEventListener('click', buscarAlumnoPorDni);
            ventaDniBuscar?.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); buscarAlumnoPorDni(); }
            });

            ventaAlumnoClear?.addEventListener('click', () => {
                if (ventaEstudianteId) ventaEstudianteId.value = '';
                if (ventaDniBuscar) ventaDniBuscar.value = '';
                ventaAlumnoResult?.classList.add('d-none');
                ventaAlumnoError?.classList.add('d-none');
            });

            document.getElementById('ventaAgregarPago')?.addEventListener('click', () => {
                crearFilaPago('');
                actualizarSaldo();
            });

            // Tipo comprador (botones toggle)
            const setTipoComprador = (tipo) => {
                if (ventaTipoComprador) ventaTipoComprador.value = tipo;
                document.getElementById('ventaTipoBtnAlumno')?.classList.toggle('is-active', tipo === 'alumno');
                document.getElementById('ventaTipoBtnExterno')?.classList.toggle('is-active', tipo === 'externo');
                if (tipo === 'externo') {
                    ventaAlumnoGroup?.classList.add('d-none');
                    ventaExternoGroup?.classList.remove('d-none');
                    if (ventaExternoNombre) ventaExternoNombre.required = true;
                } else {
                    ventaAlumnoGroup?.classList.remove('d-none');
                    ventaExternoGroup?.classList.add('d-none');
                    if (ventaExternoNombre) ventaExternoNombre.required = false;
                }
            };

            document.getElementById('ventaTipoBtnAlumno')?.addEventListener('click', () => setTipoComprador('alumno'));
            document.getElementById('ventaTipoBtnExterno')?.addEventListener('click', () => setTipoComprador('externo'));

            if (ventaModal && ventaForm) {
                ventaModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const id     = button.getAttribute('data-id');
                    const nombre = button.getAttribute('data-nombre') || '';
                    const precio = parseFloat(button.getAttribute('data-precio') || '0');
                    const stock  = parseInt(button.getAttribute('data-stock') || '0', 10);

                    precioUnitario = precio;
                    ventaProductoId.value = id;
                    if (ventaProductoNombre) ventaProductoNombre.textContent = nombre;
                    if (ventaProductoPrecio) ventaProductoPrecio.textContent = `S/ ${precio.toFixed(2)}`;
                    if (ventaProductoStock)  ventaProductoStock.textContent  = stock;

                    if (ventaCantidad) { ventaCantidad.value = 1; ventaCantidad.max = stock; }

                    // Reset comprador
                    if (ventaDniBuscar) ventaDniBuscar.value = '';
                    if (ventaEstudianteId) ventaEstudianteId.value = '';
                    ventaAlumnoResult?.classList.add('d-none');
                    ventaAlumnoError?.classList.add('d-none');
                    if (ventaExternoNombre) ventaExternoNombre.value = '';
                    const extDoc = document.getElementById('ventaExternoDocumento');
                    if (extDoc) extDoc.value = '';

                    setTipoComprador('alumno');
                    resetVentaModal();
                    actualizarTotal();

                    // Pre-rellenar monto del primer pago
                    const primerMonto = ventaPagosContainer?.querySelector('.venta-pago-monto');
                    if (primerMonto) { primerMonto.value = precio.toFixed(2); actualizarSaldo(); }

                    if (typeof feather !== 'undefined') feather.replace();
                });
            }

            ventaCantidad?.addEventListener('input', () => {
                actualizarTotal();
                const filas = ventaPagosContainer?.querySelectorAll('.vv-pago-row') ?? [];
                if (filas.length === 1) {
                    const m = filas[0].querySelector('.venta-pago-monto');
                    if (m) { m.value = getTotal().toFixed(2); actualizarSaldo(); }
                }
            });
        });
    </script>
@endpush
