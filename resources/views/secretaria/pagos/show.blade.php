@extends('layouts.mantis')

@section('title', 'Detalle de Pago | Secretaria')
@section('page-title', 'Detalle de Pago')
@section('body-class', auth()->user()->hasRole('SuperAdmin') ? 'role-superadmin' : 'role-secretaria')

@push('styles')
    @if(auth()->user()->hasRole('SuperAdmin'))
        <link rel="stylesheet" href="{{ url('css/superadmin-dashboard.css') }}">
    @else
        <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    @endif
@endpush

@section('sidebar')
    @if(auth()->user()->hasRole('SuperAdmin'))
        @include('admin.partials.sidebar')
    @else
        @include('secretaria.partials.sidebar')
    @endif
@endsection

@section('content')
    <div class="card jp-soft-card mt-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="mb-1">Pago #{{ $pago->id }}</h5>
                    <p class="text-muted mb-0">Registrado el {{ $pago->fecha_pago }}</p>
                </div>
                <a href="{{ route('secretaria.pagos.index') }}" class="btn btn-outline-secondary">
                    Volver
                </a>
            </div>

            <div class="mt-3">
                @php
                    $montoAplicadoTotal = (float) $pago->deudas->sum(function ($deuda) {
                        return (float) ($deuda->pivot->monto_aplicado ?? 0);
                    });
                    $montoMostrado = $montoAplicadoTotal > 0 ? $montoAplicadoTotal : (float) $pago->monto;
                @endphp
                <div class="summary-grid">
                    <div><strong>Monto:</strong> S/ {{ number_format($montoMostrado, 2) }}</div>
                    <div><strong>M&eacute;todo:</strong>
                        {{ $pago->metodo_display }}
                        @if(count($pago->metodos_decoded) > 1)
                            <br>
                            @foreach($pago->metodos_decoded as $met)
                                <small class="text-muted">
                                    {{ $met['metodo'] }}: S/ {{ number_format($met['monto'], 2) }}
                                    {{ !empty($met['op']) ? '· Op. '.$met['op'] : '' }}
                                </small><br>
                            @endforeach
                        @endif
                    </div>
                    <div><strong>Estado:</strong> {{ ucfirst($pago->estado) }}</div>
                    <div><strong>Registrado por:</strong> {{ $pago->registradoPor->name ?? '-' }}</div>
                    <div><strong>Comentario:</strong> {{ $pago->comentario ?? '-' }}</div>
                </div>
            </div>

            <div class="section-head section-head--spaced">
                <div class="section-title"><i data-feather="file-text"></i> Deudas asociadas</div>
            </div>
            <div class="section-line"></div>

            <div class="table-responsive">
                <table class="table jp-table mb-0">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Concepto</th>
                            <th>Saldo actual</th>
                            <th>Monto aplicado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pago->deudas as $deuda)
                            @php
                                $estudiante = $deuda->matricula?->estudiante;
                                $label = $estudiante ? $estudiante->apellidos . ', ' . $estudiante->nombres : '-';
                                $aplicado = $pago->estado === 'confirmado' ? (float) ($deuda->pivot->monto_aplicado ?? 0) : 0;
                                $conceptoNombre = $deuda->concepto?->nombre ?? '-';
                                $conceptoDetalle = trim((string) ($deuda->periodo ?? ''));
                                if ($conceptoDetalle === '') {
                                    $conceptoDetalle = $conceptoNombre;
                                }
                            @endphp
                            <tr>
                                <td>{{ $label }}</td>
                                <td>
                                    <div>{{ $conceptoDetalle }}</div>
                                    @if(strtolower($conceptoDetalle) !== strtolower($conceptoNombre))
                                        <small class="text-muted">{{ $conceptoNombre }}</small>
                                    @endif
                                </td>
                                <td>S/ {{ number_format((float) $deuda->saldo, 2) }}</td>
                                <td>S/ {{ number_format((float) $aplicado, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Sin deudas asociadas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
