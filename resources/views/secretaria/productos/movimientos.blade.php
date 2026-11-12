@extends('layouts.mantis')

@php $rp = auth()->user()->hasRole('SuperAdmin') ? 'admin-sistema.' : 'secretaria.'; @endphp
@section('title', 'Movimientos | Secretaria')
@section('page-title', 'Movimientos de Inventario')
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
        <a class="jp-product-tab" href="{{ route($rp . 'productos.categorias') }}">
            <i data-feather="grid"></i>
            Categor&iacute;as
        </a>
        <a class="jp-product-tab is-active" href="{{ route($rp . 'productos.movimientos') }}">
            <i data-feather="repeat"></i>
            Movimientos
        </a>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card jp-soft-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table jp-table jp-students-table mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Costo unitario</th>
                                    <th>Referencia</th>
                                    <th>Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movimientos as $movimiento)
                                    <tr>
                                        <td>{{ $movimiento->fecha_movimiento }}</td>
                                        <td>{{ $movimiento->producto->nombre ?? '-' }}</td>
                                        <td>{{ ucfirst($movimiento->tipo) }}</td>
                                        <td>{{ $movimiento->cantidad }}</td>
                                        <td>{{ $movimiento->costo_unitario ? 'S/ ' . number_format((float) $movimiento->costo_unitario, 2) : '-' }}</td>
                                        <td>{{ $movimiento->referencia ?? '-' }}</td>
                                        <td>{{ $movimiento->usuario->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No hay movimientos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $movimientos->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
