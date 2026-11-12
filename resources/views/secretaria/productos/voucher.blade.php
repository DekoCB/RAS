@extends('layouts.mantis')

@section('title', 'Voucher de Venta')
@section('page-title', 'Voucher de Venta')
@section('body-class', 'role-secretaria')

@push('styles')
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <style>
        @media print {
            .jp-voucher-actions {
                display: none !important;
            }
            .pc-container,
            .pc-header,
            .pc-sidebar {
                display: none !important;
            }
            body {
                background: #fff !important;
            }
        }
    </style>
@endpush

@section('sidebar')
    @include('secretaria.partials.sidebar')
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 jp-voucher-actions">
        <a href="{{ route('secretaria.productos.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-left"></i>
            Volver a productos
        </a>
        <button class="btn jp-btn-incidencia" type="button" onclick="window.print()">
            <i data-feather="printer"></i>
            Imprimir
        </button>
    </div>

    <div class="card jp-voucher-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <h4 class="mb-1">Voucher de Venta</h4>
                    <div class="text-muted">C&oacute;digo: <strong>{{ $venta->voucher_codigo }}</strong></div>
                    <div class="text-muted">Fecha: {{ $venta->fecha_venta->format('d/m/Y H:i') }}</div>
                </div>
                <div class="jp-voucher-status {{ $venta->estado === 'pagado' ? 'is-paid' : 'is-pending' }}">
                    {{ strtoupper($venta->estado) }}
                </div>
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="mb-2">Comprador</h6>
                    <div>{{ $venta->comprador_nombre ?? 'No registrado' }}</div>
                    @if ($venta->comprador_documento)
                        <div class="text-muted">Documento: {{ $venta->comprador_documento }}</div>
                    @endif
                    @if ($venta->tipo_comprador === 'alumno' && $venta->estudiante)
                        <div class="text-muted">Alumno: {{ $venta->estudiante->apellidos }}, {{ $venta->estudiante->nombres }}</div>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="mb-2">Pago</h6>
                    <div>M&eacute;todo: {{ $venta->metodo_pago }}</div>
                    <div class="text-muted">Registrado por: {{ $venta->usuario->name ?? '-' }}</div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table jp-voucher-table mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venta->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->nombre ?? '-' }}</td>
                                <td>{{ $detalle->cantidad }}</td>
                                <td>S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                <td>S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th>S/ {{ number_format((float) $venta->total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($venta->observacion)
                <div class="mt-3 text-muted">
                    Observaci&oacute;n: {{ $venta->observacion }}
                </div>
            @endif
        </div>
    </div>
@endsection
